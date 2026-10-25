// @mention autocomplete for comment inputs and caption edits, backed by
// /api/users/search (same endpoint as search.js).
const FIELDS = '.comment-input, .post-edit-textarea';

let field = null;
let list = null;
let users = [];
let active = -1;
let timer = null;
let controller = null;

function close() {
	clearTimeout(timer);
	controller?.abort();
	list?.remove();
	list = null;
	users = [];
	active = -1;
}

// Same pattern as the server-side mention regex, anchored at the caret.
function queryAtCaret(el) {
	const match = el.value.slice(0, el.selectionStart).match(/(?<![\w@])@(\w+)$/);
	return match ? match[1] : null;
}

function pick(username) {
	const caret = field.selectionStart;
	const before = field.value.slice(0, caret).replace(/@\w*$/, `@${username} `);
	field.value = before + field.value.slice(caret);
	field.setSelectionRange(before.length, before.length);
	field.focus();
	close();
}

function highlight(index) {
	active = index;
	list.querySelectorAll('.mention-option').forEach((li, i) => {
		li.setAttribute('aria-selected', i === active ? 'true' : 'false');
	});
}

function render() {
	list?.remove();
	list = null;
	if (!users.length) return;

	list = document.createElement('ul');
	list.className = 'mention-list';
	list.setAttribute('role', 'listbox');
	users.forEach((user, i) => {
		const li = document.createElement('li');
		li.className = 'mention-option';
		li.setAttribute('role', 'option');
		// Same avatar markup as resultItem in search.js.
		const avatar = document.createElement('div');
		avatar.className = 'post-user-avatar';
		if (user.avatar_url) {
			const img = document.createElement('img');
			img.src = user.avatar_url;
			img.alt = '';
			img.className = 'h-full w-full object-cover';
			avatar.append(img);
		} else {
			avatar.textContent = user.username.slice(0, 2).toUpperCase();
		}
		li.append(avatar, `@${user.username}`);
		// mousedown instead of click so the field doesn't lose focus first.
		li.addEventListener('mousedown', (e) => {
			e.preventDefault();
			pick(user.username);
		});
		li.addEventListener('mouseenter', () => highlight(i));
		list.append(li);
	});
	field.parentElement.append(list);
	highlight(0);
}

async function search(query) {
	controller?.abort();
	controller = new AbortController();
	try {
		const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
			headers: { 'Accept': 'application/json' },
			signal: controller.signal
		});
		const data = await response.json();
		users = (data.results || []).slice(0, 5);
		render();
	} catch (error) {
		// A failed suggestion lookup must not block typing the comment.
		if (error.name !== 'AbortError') close();
	}
}

document.addEventListener('input', (e) => {
	if (!e.target.matches?.(FIELDS)) return;
	if (field !== e.target) close();
	field = e.target;

	const query = queryAtCaret(field);
	if (!query) {
		close();
		return;
	}
	clearTimeout(timer);
	timer = setTimeout(() => search(query), 200);
});

// Capture phase so Enter picks a suggestion before the form submit handler sees it.
document.addEventListener('keydown', (e) => {
	if (!list || e.target !== field) return;

	if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
		e.preventDefault();
		const step = e.key === 'ArrowDown' ? 1 : -1;
		highlight((active + step + users.length) % users.length);
	} else if (e.key === 'Enter' || e.key === 'Tab') {
		e.preventDefault();
		pick(users[active].username);
	} else if (e.key === 'Escape') {
		e.preventDefault();
		close();
	}
}, true);

document.addEventListener('focusout', (e) => {
	if (e.target === field) close();
});

// Inputs can't color part of their text, so while a field is focused its own
// text goes transparent and a mirror on top draws it with the @mentions colored.
const MIRRORED_STYLES = [
	'fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textAlign', 'boxSizing',
	'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
	'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth'
];
let mirrored = null;
let mirror = null;
let frame = 0;
let lastValue = null;

function highlighted(text) {
	// Same pattern as the server; the capture group keeps the mentions in the split.
	return text.split(/((?<![\w@])@\w+)/).map((part, i) => {
		if (i % 2 === 0) return document.createTextNode(part);
		const span = document.createElement('span');
		span.className = 'mention-typed';
		span.textContent = part;
		return span;
	});
}

// Runs every frame while focused because home.js also sets .value directly
// (reply, clear after posting) and that fires no input event.
function syncMirror() {
	if (mirrored.value !== lastValue) {
		lastValue = mirrored.value;
		// Trailing space keeps a final newline visible in the textarea.
		mirror.replaceChildren(...highlighted(lastValue), ' ');
	}
	Object.assign(mirror.style, {
		left: `${mirrored.offsetLeft}px`,
		top: `${mirrored.offsetTop}px`,
		width: `${mirrored.offsetWidth}px`,
		height: `${mirrored.offsetHeight}px`
	});
	mirror.scrollLeft = mirrored.scrollLeft;
	mirror.scrollTop = mirrored.scrollTop;
	frame = requestAnimationFrame(syncMirror);
}

document.addEventListener('focusin', (e) => {
	if (!e.target.matches?.(FIELDS)) return;
	mirrored = e.target;
	mirror = document.createElement('div');
	mirror.className = 'mention-mirror';
	mirror.setAttribute('aria-hidden', 'true');
	const style = getComputedStyle(mirrored);
	MIRRORED_STYLES.forEach((prop) => { mirror.style[prop] = style[prop]; });
	mirror.style.whiteSpace = mirrored.tagName === 'INPUT' ? 'pre' : 'pre-wrap';
	mirrored.parentElement.append(mirror);
	mirrored.classList.add('mention-mirrored');
	lastValue = null;
	syncMirror();
});

document.addEventListener('focusout', (e) => {
	if (e.target !== mirrored) return;
	cancelAnimationFrame(frame);
	mirror.remove();
	mirrored.classList.remove('mention-mirrored');
	mirrored = mirror = null;
});

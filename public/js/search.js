import { showAlert } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('search-form');
	const input = document.getElementById('search-input');
	const results = document.getElementById('search-results');
	const empty = document.getElementById('search-empty');
	const emptyQuery = document.getElementById('search-empty-query');
	if (!form || !input || !results)
		return;

	let timer = null;
	let controller = null;

	async function search() {
		const query = input.value.trim();
		history.replaceState(null, '', query ? `/search?q=${encodeURIComponent(query)}` : '/search');

		controller?.abort();
		if (!query) {
			results.replaceChildren();
			empty.hidden = true;
			return;
		}

		controller = new AbortController();
		try {
			const response = await fetch(`/api/users/search?q=${encodeURIComponent(query)}`, {
				headers: { 'Accept': 'application/json' },
				signal: controller.signal
			});
			const data = await response.json();
			if (data.redirect) {
				window.location.href = data.redirect;
				return;
			}
			results.replaceChildren(...(data.results || []).map(resultItem));
			emptyQuery.textContent = query;
			empty.hidden = data.results.length > 0;
		} catch (error) {
			if (error.name !== 'AbortError')
				await showAlert('Could not connect to the server');
		}
	}

	input.addEventListener('input', () => {
		clearTimeout(timer);
		timer = setTimeout(search, 250);
	});

	form.addEventListener('submit', (e) => {
		e.preventDefault();
		clearTimeout(timer);
		search();
	});
});

// Same markup as the results loop in app/Views/search.php.
function resultItem(user) {
	const link = document.createElement('a');
	link.href = `/profile?username=${encodeURIComponent(user.username)}`;
	link.className = 'search-result';

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

	const name = document.createElement('span');
	name.className = 'font-bold text-white';
	name.textContent = user.username;

	link.append(avatar, name);
	return link;
}

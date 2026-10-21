// Site-styled stand-ins for the native alert()/confirm() dialogs, reusing
// the same modal-overlay/modal-card visuals as the add-photo modal. Driven
// by the #app-modal markup in app/Views/modals/app-modal.php.

let activeResolve = null;
let handlersReady = false;

function getModal() {
	return document.getElementById('app-modal');
}

function close(result) {
	const modal = getModal();
	if (!modal) return;

	modal.classList.remove('show');
	setTimeout(() => {
		if (!modal.classList.contains('show')) {
			modal.style.display = 'none';
		}
	}, 300);

	if (activeResolve) {
		const resolve = activeResolve;
		activeResolve = null;
		resolve(result);
	}
}

function ensureHandlers(modal) {
	if (handlersReady) return;
	handlersReady = true;

	modal.addEventListener('click', (e) => {
		if (e.target === modal) close(false);
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && modal.classList.contains('show')) close(false);
	});
}

function show(title, message, buttons) {
	return new Promise((resolve) => {
		const modal = getModal();

		// Fallback for pages that don't include the app-modal partial yet.
		if (!modal) {
			resolve(window.confirm(message));
			return;
		}

		ensureHandlers(modal);
		activeResolve = resolve;

		modal.querySelector('#app-modal-title').textContent = title;
		modal.querySelector('#app-modal-message').textContent = message;

		const actions = modal.querySelector('#app-modal-actions');
		actions.innerHTML = '';
		buttons.forEach(({ label, value, primary }) => {
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.textContent = label;
			btn.className = primary ? 'modal-primary-btn' : 'modal-secondary-btn';
			btn.addEventListener('click', () => close(value));
			actions.appendChild(btn);
		});

		modal.style.display = 'flex';
		setTimeout(() => modal.classList.add('show'), 10);
	});
}

export function showAlert(message, title = 'Notice') {
	return show(title, message, [
		{ label: 'OK', value: true, primary: true },
	]);
}

export function showConfirm(message, title = 'Confirm') {
	return show(title, message, [
		{ label: 'Cancel', value: false, primary: false },
		{ label: 'Confirm', value: true, primary: true },
	]);
}

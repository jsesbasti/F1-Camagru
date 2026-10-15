import { api } from './api.js';
import { showConfirm } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.settings-card[data-endpoint]')
	.forEach(initSettingsForm);
	initNotifications();
	initAvatar();
});

const AVATAR_UPLOAD_SIZE = 512;

function initAvatar() {
	const card = document.getElementById('avatar-card');
	if (!card)
		return;
	const input = card.querySelector('#avatar-input');
	const removeBtn = card.querySelector('#avatar-remove');
	const preview = card.querySelector('#avatar-preview');
	const success = card.querySelector('[data-success]');
	const error = card.querySelector('[data-error="error"]');

	function show(avatarUrl) {
		document.querySelectorAll('#avatar-preview, [data-avatar]').forEach((el) => {
			if (avatarUrl) {
				const img = document.createElement('img');
				img.src = avatarUrl;
				img.alt = '';
				img.className = 'h-full w-full object-cover';
				el.replaceChildren(img);
			} else {
				el.textContent = preview.dataset.initials;
			}
		});
		removeBtn.hidden = !avatarUrl;
	}

	async function send(url, body) {
		success.textContent = '';
		error.textContent = '';
		try {
			const response = await fetch(url, { method: 'POST', body });
			const data = await response.json();
			if (data.success) {
				success.textContent = data.message;
				show(data.avatar_url);
			} else {
				error.textContent = Object.values(data.errors || {})[0] || 'Could not save';
			}
		} catch (e) {
			error.textContent = 'Something went wrong, try again';
		}
	}

	input.addEventListener('change', () => {
		const file = input.files[0];
		input.value = '';
		if (!file)
			return;

		const img = new Image();
		img.onload = () => {
			URL.revokeObjectURL(img.src);
			const side = Math.min(img.naturalWidth, img.naturalHeight);
			const canvas = document.createElement('canvas');
			canvas.width = canvas.height = AVATAR_UPLOAD_SIZE;
			canvas.getContext('2d').drawImage(img,
				(img.naturalWidth - side) / 2, (img.naturalHeight - side) / 2, side, side,
				0, 0, AVATAR_UPLOAD_SIZE, AVATAR_UPLOAD_SIZE);
			canvas.toBlob((blob) => {
				const form = new FormData();
				form.append('avatar', blob, 'avatar.png');
				send('/api/settings/avatar', form);
			}, 'image/png');
		};
		img.onerror = () => {
			error.textContent = 'That file is not an image we can read';
		};
		img.src = URL.createObjectURL(file);
	});

	removeBtn.addEventListener('click', () => send('/api/settings/avatar/delete'));
}

function initNotifications() {
	const card = document.getElementById('notifications-card');
	if (!card)
		return;
	const toggle = card.querySelector('#notify-comments');
	const success = card.querySelector('[data-success]');
	const error = card.querySelector('[data-error="error"]');

	toggle.addEventListener('change', async () => {
		success.textContent = '';
		error.textContent = '';
		toggle.disabled = true;

		try {
			const response = await api('/api/settings/notifications', 'POST', { notify_comments: toggle.checked });
			const data = await response.json();
			if (data.success) {
				success.textContent = data.message;
			} else {
				toggle.checked = !toggle.checked;
				error.textContent = Object.values(data.errors || {})[0] || 'Could not save';
			}
		} catch (e) {
			toggle.checked = !toggle.checked;
			error.textContent = 'Something went wrong, try again';
		} finally {
			toggle.disabled = false;
		}
	});
}

function initSettingsForm(form) {
	const button = form.querySelector('button[type="submit"]');
	const success = form.querySelector('[data-success]');

	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		form.querySelectorAll('.error-msg')
		.forEach(el => el.textContent = '');

		const body = Object.fromEntries(new FormData(form));

		if ('confirm' in body && body.confirm !== body.new_password) {
			showError(form, 'confirm', 'Passwords does not match');
			return ;
		}

		if (form.dataset.confirm
			&& !await showConfirm(form.dataset.confirm, 'Delete account')) {
			return ;
		}

		button.disabled = true;

		try {

			const response = await api(form.dataset.endpoint, 'POST', body);

			const data = await response.json();

			if (data.redirect) {
				window.location.href = data.redirect;
				return ;
			}

			if (data.success) {
				success.textContent = data.message;
				onSuccess(form, data);
			} else {
				Object.entries(data.errors || {}).forEach(([field, message]) => {
					showError(form, field, message);
				});
			}
		} catch (error) {
			showError(form, 'error', 'Something went wrong, try again');
		} finally {
			button.disabled = false;
		}
	});
}

function showError(form, field, message) {
	const el = form.querySelector(`[data-error="${field}"]`)
		|| form.querySelector('[data-error="error"]');
	el.textContent = message;
}

function onSuccess(form, data) {
	form.querySelectorAll('input[type="password"]')
	.forEach(input => input.value = '');

	if (data.pending) {
		form.querySelector('input[name="email"]').value = '';
		form.querySelector('[data-pending-email]').textContent = data.pending;
		form.querySelector('[data-pending]').hidden = false;
	}

	if (data.username) {
		document.querySelectorAll('.dropdown-username')
		.forEach(el => el.textContent = data.username);
		const initials = data.username.slice(0, 2).toUpperCase();
		document.querySelectorAll('#avatar-preview, [data-avatar]').forEach((el) => {
			el.dataset.initials = initials;
			if (!el.querySelector('img')) el.textContent = initials;
		});
	}
}

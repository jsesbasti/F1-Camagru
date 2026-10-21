import { showAlert } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
	const button = document.getElementById('follow-btn');
	if (!button)
		return;

	button.addEventListener('click', async () => {
		button.disabled = true;
		try {
			const response = await fetch('/api/users/follow', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ user_id: Number(button.dataset.userId) })
			});
			const data = await response.json();

			if (data.redirect) {
				window.location.href = data.redirect;
				return;
			}
			if (!data.success) {
				await showAlert(Object.values(data.errors || {})[0] || 'Something went wrong');
				return;
			}

			button.textContent = data.following ? 'Unfollow' : 'Follow';
			button.classList.toggle('primary', !data.following);
			button.classList.toggle('secondary', data.following);
			document.getElementById('followers-count').textContent = data.followers;
		} catch (error) {
			await showAlert('Could not connect to the server');
		} finally {
			button.disabled = false;
		}
	});
});

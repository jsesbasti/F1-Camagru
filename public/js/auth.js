import { api } from './api.js'

document.addEventListener('DOMContentLoaded', () => {

	const form = document.getElementById('login-form');
	if (!form)
		return;
	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		const username = document.getElementById('username').value;
		const password = document.getElementById('password').value;

		try {

			const response = await api('/login', 'POST', { username, password });

			const data = await response.json();

			if (response.ok) {
				window.location.href = '/';
			} else {
				document.getElementById('error').textContent =
					data.error || 'Login failed';
			}
		} catch (error) {
			document.getElementById('error').textContent =
				'Server error';
			console.log(data.error);
		}
	});
});

document.addEventListener('DOMContentLoaded', () => {

	const form = document.getElementById('register-form');
	if (!form)
		return;
	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		const username = document.getElementById('username').value;
		const password = document.getElementById('password').value;
		const email = document.getElementById('email').value;

		try {

			const response = await api('/register', 'POST', { username, password, email });

			const data = await response.json();

			if (response.ok) {
				window.location.href = '/';
			} else {
				document.getElementById('error').textContent =
					data.error || 'Register failed';
			}
		} catch (error) {
			document.getElementById('error').textContent =
				'Server error';
			console.log(data.error);
		}
	});
});
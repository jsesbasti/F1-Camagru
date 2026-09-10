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

			if (data.redirect) {
				window.location.href = data.redirect;
			} else {
				Object.entries(data.errors).forEach(([field, message]) => {
					console.log(field);
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					}
				});
				form.reset();
			}
		} catch (error) {
			console.log(error.message);
		}
	});
});

document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('register-form');
	if (!form)
		return;
	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		document.querySelectorAll('.error-msg')
		.forEach(el => el.textContent = '');
		const username = document.getElementById('username').value;
		const email = document.getElementById('email').value;
		const password = document.getElementById('password').value;
		const confirm_pass = document.getElementById('confirm').value;

		if (confirm_pass !== password)
		{
			document.getElementById('confirm-error').textContent = 'Passwords does not match';
			return ;
		}
		try {

			const response = await api('/register', 'POST', { username, password, email });

			const data = await response.json();

			if (data.redirect) {
				window.location.href = data.redirect;
			} else {
				Object.entries(data.errors).forEach(([field, message]) => {
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					}
				});
				form.reset();
			}
		} catch (error) {
			return ;
		}
	});
});

document.addEventListener('DOMContentLoaded', () => {

	const form = document.getElementById('forgot-password-form');
	if (!form)
		return;
	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		document.querySelectorAll('.error-msg')
		.forEach(el => el.textContent = '');

		const email = document.getElementById('email').value;

		try {

			const response = await api('/forgot-password', 'POST', { email });

			const data = await response.json();

			if (data.success) {
				document.getElementById('success-msg').textContent = data.message;
				form.reset();
			} else {
				Object.entries(data.errors).forEach(([field, message]) => {
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					} else {
						document.getElementById('error-error').textContent = message;
					}
				});
			}
		} catch (error) {
			return ;
		}
	});
});

document.addEventListener('DOMContentLoaded', () => {

	const form = document.getElementById('reset-password-form');
	if (!form)
		return;
	form.addEventListener('submit', async (e) => {

		e.preventDefault();

		document.querySelectorAll('.error-msg')
		.forEach(el => el.textContent = '');

		const token = document.getElementById('token').value;
		const password = document.getElementById('password').value;
		const confirm = document.getElementById('confirm').value;

		if (confirm !== password) {
			document.getElementById('confirm-error').textContent = 'Passwords does not match';
			return ;
		}

		try {

			const response = await api('/reset-password', 'POST', { token, password, confirm });

			const data = await response.json();

			if (data.redirect) {
				window.location.href = data.redirect;
			} else {
				Object.entries(data.errors).forEach(([field, message]) => {
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					} else {
						document.getElementById('error-error').textContent = message;
					}
				});
			}
		} catch (error) {
			return ;
		}
	});
});
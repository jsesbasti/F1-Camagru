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
				Object.entries(data.errors).forEach(([field, message]) => {
					console.log(field);
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					}
				});
			}
		} catch (error) {
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

			if (response.ok) {
				window.location.href = '/';
			} else {
				Object.entries(data.errors).forEach(([field, message]) => {
					const errorElement = document.getElementById(`${field}-error`);
					if (errorElement) {
						errorElement.textContent = message;
					}
				});
				username = '';
				email = '';
				password = '';
				confirm_pass = '';
			}
		} catch (error) {
			return ;
		}
	});
});
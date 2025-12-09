<script>
	document.addEventListener('keydown', (event) => { if (event.key === 'Enter') { window[document.title]()}});
	
	const cookies = cookieStore.getAll();
	
	document.addEventListener('DOMContentLoaded', async () => {
		const res = fetch("http://localhost:9000/logged", {
			method: 'GET',
			credentials: true
		})
	
		const data = (await res).json();
		if (data.logged == true)
			window.location.href = "http://localhost:8000/"
	});
	
	async function login() {
		const user = document.getElementById("username").value;
		const password = document.getElementById("password").value;
		const message = document.getElementById("message");
	
		const response = await fetch("http://localhost:9000/login", {
			method: "POST",
			mode: 'cors',
			credentials: 'include',
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({ user, password })
		});
	
		const data = await response.json();
		if (response.ok) {
			message.style.color = "green";
			message.textContent = data.msg;
		} else if (response.status == 301) {
			window.location.href = `http://localhost:8000/${data.path}`;
		} else {
			message.style.color = "red";
			message.textContent = data.error || "Credenciales incorrectas";
		}
	
	}
	
	async function register() {
		const firstname = document.getElementById("fname").value;
		const lastname = document.getElementById("lname").value;
		const email = document.getElementById("email").value;
		const user = document.getElementById("username").value;
		const password = document.getElementById("password").value;
		const message = document.getElementById("message");
	
		const response = await fetch("http://localhost:9000/register", {
			method: "POST",
			mode: 'cors',
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({ firstname, lastname, email, user, password })
		});
	
		const data = await response.json();
		if (response.ok) {
			message.style.color = "green";
			message.textContent = `${data.msg}, please, login now`;
			window.location.href = "http://localhost:8000/";
		} else {
			message.style.color = "red";
			message.textContent = data.error || "Credenciales incorrectas";
		}
	}
</script>
async function login() {
	const username = document.getElementById("username").value;
	const password = document.getElementById("password").value;
	const message = document.getElementById("message");

	const response = await fetch("http://<?php echo $_SERVER['SERVER_ADDR'];?>:3000/login", {
		method: "POST",
		headers: {
			"Content-Type": "application/json"
		},
		body: JSON.stringify({ username, password })
	});

	const data = await response.json();

		if (response.ok) {
			message.style.color = "green";
			message.textContent = "Login correcto ✔️";
		} else {
			message.style.color = "red";
			message.textContent = data.error || "Credenciales incorrectas";
		}
	}
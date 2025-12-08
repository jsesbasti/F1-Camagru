document.addEventListener('keydown', (event) => { if (event.key === 'Enter') {login()}});

async function login() {
    const user = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    const message = document.getElementById("message");

    const response = await fetch("http://localhost:9000/login", {
        method: "POST",
        mode: 'cors',
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ user, password })
    });

    const data = await response.json();
    if (response.ok) {
        message.style.color = "green";
        message.textContent = data.msg;
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
        message.textContent = data.msg;
    } else {
	    message.style.color = "red";
	    message.textContent = data.error || "Credenciales incorrectas";
    }
}
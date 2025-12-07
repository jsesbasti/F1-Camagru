<?php require 'Head.php'?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    
</head>
<body>
    <div class="login-box">
        <h3>Register</h3>
        <input type="text" id="user" placeholder="Usuario" required />
        <input type="password" id="password" placeholder="Contraseña" required />
        <button onclick="login()">Entrar</button>
        <p id="message"></p>
    </div>

    <script>
        async function login() {
            const user = document.getElementById("user").value;
            const password = document.getElementById("password").value;
            const message = document.getElementById("message");

            const response = await fetch("http://localhost:3000/register", {
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
    </script>
</body>
</html>
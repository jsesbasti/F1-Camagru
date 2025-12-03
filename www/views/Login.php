<?php require 'Head.php'?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f5f5f5;
        }
        .login-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #005fcc;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h3>Login</h3>
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

            const response = await fetch("http://10.12.250.20:3000/login", {
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
                message.textContent = "Login correcto ✔️";
            } else {
                message.style.color = "red";
                message.textContent = data.error || "Credenciales incorrectas";
            }
        }
    </script>
</body>
</html>

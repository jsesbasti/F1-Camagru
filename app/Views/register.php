<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>
    <link href="assets/css/auth.css" rel="stylesheet">
    <script type="module" src="js/auth.js"></script>
</head>

<body>
    <form id="register-form" class="login-box">
        <div>
            <input type="text" name="email" id="email" autocomplete="on" placeholder="email" required>
            <p id="email-error" class="error-msg"></p>
            <input type="text" name="username" id="username" autocomplete="on" placeholder="username" required>
            <p id="username-error" class="error-msg"></p>
            <input type="password" name="password" id="password" placeholder="password" required>
            <p id="password-error" class="error-msg"></p>
            <input type="password" name="confirm" id="confirm" placeholder="confirm password" required>
            <p id="confirm-error" class="error-msg"></p>
            <button enabled="false" type="submit" name="register">Register</button>
        </div>
    </form>
</body>

</html>
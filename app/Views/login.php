<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title ?></title>
        <link rel="stylesheet" href="assets/css/auth.css">
        <script type="module" src="js/auth.js"></script>
    </head>
    <body>
       <form id="login-form" class="login-box">
            <div>
                <input type="text" name="username" id="username" autocomplete="on" placeholder="username" required>
                <input type="password" name="password" id="password" placeholder="password" required>

                <button enabled="false" type="submit" name="login">Login</button>
            </div>
       </form>
       <p id="error" class="error-msg"></p>
    </body>
</html>

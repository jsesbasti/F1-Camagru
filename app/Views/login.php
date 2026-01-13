<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title ?></title>
        <link rel="stylesheet" href="assets/css/auth.css">
        <script src="js/auth.js"></script>
    </head>
    <body>
       <form action = "/login" method="post" class="login-box">
            <div>
            <input type="text" name="username" id="username" autocomplete="on" placeholder="username" required>
            <input type="password" name="password" id="password" placeholder="password" required>
            <button enabled="false" type="submit" name="login">Login</button>
            </div>
       </form> 
    </body>
</html>

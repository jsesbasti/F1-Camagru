<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title ?></title>
        <link href="assets/css/auth.css" rel="stylesheet">
    </head>
    <body>
       <form action = "/register" method="post" class="login-box">
            <div>
            <input type="email" name="email" id="email" autocomplete="on" placeholder="email" required>
            <input type="text" name="username" id="username" autocomplete="on" placeholder="username" required>
            <input type="password" name="password" id="password" placeholder="password" required>
            <input type="password" name="confirm" id="confirm" placeholder="confirm password" required>
            <button enabled="false" type="submit" name="register">Register</button>
            </div>
       </form> 
    </body>
</html>

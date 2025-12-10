<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>$title</title>
    </head>
    <body>
       <form action = "<?php echo $template; ?>" method="post">
            <div>
               <label for="username">Username:</label>
               <input type="text" name="username" id="name">
            </div>
            <div>
               <label for="password">Password:</label>
               <input type="password" name="password" id="password">
            </div>
            <section style="margin-left:2rem;">
               <button type="submit" name="register">Login</button>
            </section>
       </form> 
    </body>
</html>

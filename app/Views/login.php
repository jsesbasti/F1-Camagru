<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/colors.css">
    <link rel="stylesheet" href="assets/css/auth.css">

    <script type="module" src="js/auth.js"></script>
</head>

<body>

    <a href="/" class="back-button">
        ← HOME
    </a>

    <form id="login-form" class="login-box">

        <div>

            <input
                type="text"
                name="username"
                id="username"
                autocomplete="on"
                placeholder="Username"
                required
            >

            <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
            >

            <button type="submit" name="login">
                LOGIN
            </button>

        </div>

        <p id="error-error" class="error-msg"></p>

    </form>

</body>
</html>
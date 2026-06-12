<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
    <header>
        <h1><?= $title ?></h1>
    </header>
    <main>
        <h2>Usuarios:</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?= $user['username'] ?></li>
            <?php endforeach; ?>
        </ul>
    </main>
    <footer>
    </footer>
</body>
</html>

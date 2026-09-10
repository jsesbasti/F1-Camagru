<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Camagru') ?></title>

    <!-- Google Fonts -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Inter:wght@400;500;700&display=swap"
          rel="stylesheet">

    <!-- Tailwind (compiled from resources/css/app.css, see package.json) -->

    <link rel="stylesheet" href="/assets/css/app.css">

</head>

<body
    class="min-h-screen bg-asphalt font-body text-white <?= htmlspecialchars($bodyClass ?? '') ?>"
<?php

if (!empty($bodyAttributes)) {

    foreach ($bodyAttributes as $key => $value) {

        echo ' ' . htmlspecialchars($key)
            . '="' . htmlspecialchars($value) . '"';
    }
}

?>
>
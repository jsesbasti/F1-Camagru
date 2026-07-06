<!DOCTYPE html>
<html lang="es">

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

    <!-- Base -->

    <link rel="stylesheet" href="/assets/css/base/reset.css">
    <link rel="stylesheet" href="/assets/css/base/colors.css">
    <link rel="stylesheet" href="/assets/css/base/typography.css">
    <link rel="stylesheet" href="/assets/css/base/utilities.css">

    <!-- Componentes -->

    <link rel="stylesheet" href="/assets/css/components/buttons.css">
    <link rel="stylesheet" href="/assets/css/components/forms.css">
    <link rel="stylesheet" href="/assets/css/components/cards.css">

    <!-- CSS específico -->

    <?php if (!empty($styles)): ?>

        <?php foreach ($styles as $style): ?>

            <link rel="stylesheet"
                  href="/assets/css/pages/<?= htmlspecialchars($style) ?>.css">

        <?php endforeach; ?>

    <?php endif; ?>

</head>

<body
    class="<?= htmlspecialchars($bodyClass ?? '') ?>"
<?php

if (!empty($bodyAttributes)) {

    foreach ($bodyAttributes as $key => $value) {

        echo ' ' . htmlspecialchars($key)
            . '="' . htmlspecialchars($value) . '"';
    }
}

?>
>
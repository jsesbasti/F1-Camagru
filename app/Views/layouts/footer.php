<?php

if (!empty($footer)) {
    require __DIR__ . "/../partials/{$footer}.php";
}

$scripts = array_unique(array_merge(['nav'], $scripts ?? []));

if (!empty($scripts)) {

    foreach ($scripts as $script) {
        ?>
        <script
            type="module"
            src="/js/<?= htmlspecialchars($script) ?>.js">
        </script>
        <?php
    }
}

?>

</body>
</html>
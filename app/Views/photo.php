<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/css/colors.css">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700;1,800;1,900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/photo.css">
</head>
<body>
    <main class="photo-shell">
        <section class="photo-hero">
            <h1 class="photo-title">Modo <span>boxes</span></h1>
            <p class="photo-intro">Aquí irá la captura real desde la cámara y el flujo de publicación.</p>
        </section>
        <section class="photo-grid">
            <div class="photo-preview">
                <div class="camera-frame">
                    <video id="camera-preview" class="camera-preview" autoplay playsinline muted></video>
                    <div class="camera-overlay">
                        <div class="camera-status" id="camera-status">Activando cámara...</div>
                    </div>
                </div>
            </div>
            <aside class="photo-panel">
                <p class="photo-note">La ruta ya existe y la cámara se ha autorizado desde el modal anterior.</p>
                <button class="photo-action primary" id="capture-photo-btn" type="button">Capturar foto</button>
                <button class="photo-action secondary" id="retry-camera-btn" type="button">Reintentar cámara</button>
                <a class="photo-action primary" href="/">Volver al feed</a>
                <a class="photo-action secondary" href="/logout">Cerrar sesión</a>
            </aside>
        </section>
    </main>
    <script src="js/photo.js"></script>
</body>
</html>
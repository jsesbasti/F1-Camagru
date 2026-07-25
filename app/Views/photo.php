<?php require __DIR__ . '/partials/header.php';?>
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
                    <div class="camera-status" id="camera-status">Starting camera...</div>
                </div>
            </div>
        </div>
        <aside class="photo-panel">
            <form action="/photo/upload" method="post" enctype="multipart/form-data">
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Subir imagen</button>
            </form>
            <button class="photo-action primary" id="capture-photo-btn" type="button">Take photo</button>
            <button class="photo-action secondary" id="retry-camera-btn" type="button">Refresh camera</button>
            <button class="photo-action primary" id="feed-btn" type="button">Back to track</a>
        </aside>
    </section>
</main>
<?php require __DIR__ . '/partials/footer-nav.php';?>
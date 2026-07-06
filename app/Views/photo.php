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
            <button class="photo-action primary" id="feed-btn" type="button">Volver al feed</a>
        </aside>
    </section>
</main>

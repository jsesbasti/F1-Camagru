<?php require __DIR__ . '/partials/header.php';?>
<main class="photo-shell">
    <section class="photo-hero">
        <h1 class="photo-title">Photo <span>Editor</span></h1>
        <p class="photo-intro">Capture or upload photos and customize them with effects before publishing.</p>
    </section>
    <section class="photo-grid">
        <div class="photo-preview">
            <!-- Modo cámara -->
            <div id="camera-mode" class="photo-mode active">
                <div class="camera-frame">
                    <video id="camera-preview" class="camera-preview" autoplay playsinline muted></video>
                    <div class="camera-overlay">
                        <div class="camera-status" id="camera-status">Starting camera...</div>
                    </div>
                </div>
                <div class="superposable-images">
                    <h3>Available Effects</h3>
                    <div class="effects-grid" id="effects-container">
                        <!-- Effects will be loaded here dynamically -->
                    </div>
                </div>
                <button class="photo-action primary" id="capture-photo-btn" type="button">Capture Photo</button>
                <label class="photo-action secondary" for="upload-image" id="upload-label">Upload Image</label>
                <input type="file" id="upload-image" name="image" accept="image/*" style="display: none;">
            </div>
            
            <!-- Modo edición -->
            <div id="edit-mode" class="photo-mode">
                <div class="camera-frame">
                    <img id="edit-preview" class="edit-preview" src="" alt="Imagen a editar">
                </div>
                <div class="edit-options">
                    <div class="superposable-images">
                        <h3>Available Effects</h3>
                        <div class="effects-grid" id="edit-effects-container">
                            <!-- Effects will be loaded here dynamically -->
                        </div>
                    </div>
                </div>
                <button class="photo-action primary" id="publish-photo-btn" type="button">Publish Photo</button>
                <button class="photo-action secondary" id="cancel-edit-btn" type="button">Cancel</button>
            </div>
        </div>
        <aside class="photo-panel">
            <h3>Previous Photos</h3>
            <div class="thumbnails-container" id="thumbnails-container">
                <!-- Thumbnails will be loaded here dynamically -->
            </div>
            <button class="photo-action primary" id="feed-btn" type="button">Back to Feed</button>
        </aside>
    </section>
</main>
<?php require __DIR__ . '/partials/footer-nav.php';?>
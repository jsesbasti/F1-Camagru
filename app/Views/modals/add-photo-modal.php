<div class="modal-overlay" id="add-post-modal" data-authenticated="<?= $currentUser ? '1' : '0' ?>">
    <div class="modal-card">
        <?php require __DIR__ . "/logout-link.php"; ?>
        <h2 class="modal-title" id="add-photo-modal-title">Cargar Telemetría</h2>
        <div class="modal-content" id="add-photo-modal-content">
            <div class="modal-state modal-state-auth" id="add-photo-auth-state" hidden>
                <p>Para continuar necesitas activar la cámara y entrar al área de captura.</p>
                <p class="mt-2.5 font-racing font-bold uppercase text-f1-red">Boxes listos para recibir tu imagen.</p>
                <button class="modal-primary-btn" id="add-photo-camera-btn" type="button">Permitir cámara y continuar</button>
                <a class="modal-primary-btn border border-line !bg-transparent !shadow-none hover:border-muted" href="/photo">Continuar sin cámara</a>
                <p class="modal-status" id="add-photo-status" aria-live="polite"></p>
            </div>
            <div class="modal-state modal-state-guest" id="add-photo-guest-state" hidden>
                <p>Primero has de hacer login para poder subir fotos o capturar nuevas imágenes.</p>
                <p class="mt-2.5 font-racing font-bold uppercase text-f1-red">Entra a boxes para continuar.</p>
                <div class="modal-auth-actions">
                    <a class="modal-secondary-btn" href="/login">Login</a>
                    <a class="modal-primary-btn" href="/register">Register</a>
                </div>
            </div>
        </div>
        <button class="modal-close-btn" id="modal-close-btn">Volver a Pista</button>
    </div>
</div>
<div class="modal-overlay" id="add-post-modal" data-authenticated="<?= $currentUser ? '1' : '0' ?>">
    <div class="modal-card">
        <h2 class="modal-title" id="add-photo-modal-title">Cargar Telemetría</h2>
        <div class="modal-content" id="add-photo-modal-content">
            <div class="modal-state modal-state-auth" id="add-photo-auth-state" hidden>
                <p>Para continuar necesitas activar la cámara y entrar al área de captura.</p>
                <p style="margin-top: 10px; color: var(--f1-red); font-family: var(--font-racing); font-weight: 700; text-transform: uppercase;">Boxes listos para recibir tu imagen.</p>
                <button class="modal-primary-btn" id="add-photo-camera-btn" type="button">Permitir cámara y continuar</button>
                <p class="modal-status" id="add-photo-status" aria-live="polite"></p>
            </div>
            <div class="modal-state modal-state-guest" id="add-photo-guest-state" hidden>
                <p>Primero te has de hacer login para poder subir fotos o capturar nuevas imágenes.</p>
                <p style="margin-top: 10px; color: var(--f1-red); font-family: var(--font-racing); font-weight: 700; text-transform: uppercase;">Entra a boxes para continuar.</p>
                <div class="modal-auth-actions">
                    <a class="modal-secondary-btn" href="/login">Login</a>
                    <a class="modal-primary-btn" href="/register">Register</a>
                </div>
            </div>
        </div>
        <button class="modal-close-btn" id="modal-close-btn">Volver a Pista</button>
    </div>
</div>
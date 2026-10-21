<div class="modal-overlay" id="add-post-modal" data-authenticated="<?= $currentUser ? '1' : '0' ?>">
    <div class="modal-card">
        <?php require __DIR__ . "/logout-link.php"; ?>
        <h2 class="modal-title" id="add-photo-modal-title">Load Telemetry</h2>
        <div class="modal-content" id="add-photo-modal-content">
            <div class="modal-state modal-state-auth" id="add-photo-auth-state" hidden>
                <p>To continue, enable your camera and head to the capture area.</p>
                <p class="mt-2.5 font-racing font-bold uppercase text-f1-red">Pit crew ready for your photo.</p>
                <button class="modal-primary-btn" id="add-photo-camera-btn" type="button">Allow camera and continue</button>
                <a class="modal-primary-btn border border-line !bg-transparent !shadow-none hover:border-muted" href="/photo">Continue without camera</a>
                <p class="modal-status" id="add-photo-status" aria-live="polite"></p>
            </div>
            <div class="modal-state modal-state-guest" id="add-photo-guest-state" hidden>
                <p>You need to log in before you can upload or capture photos.</p>
                <p class="mt-2.5 font-racing font-bold uppercase text-f1-red">Enter the pit lane to continue.</p>
                <div class="modal-auth-actions">
                    <a class="modal-secondary-btn" href="/login">Login</a>
                    <a class="modal-primary-btn" href="/register">Register</a>
                </div>
            </div>
        </div>
        <button class="modal-close-btn" id="modal-close-btn">Back to Track</button>
    </div>
</div>
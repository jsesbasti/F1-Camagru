<div class="modal-overlay" id="comments-modal">
    <div class="post-card comments-modal-card" id="comments-modal-card" data-post-id="0">
        <div class="comments-modal-media">
            <img alt="" class="comments-modal-image">
            <video class="comments-modal-image comments-modal-video" muted loop playsinline autoplay hidden></video>
        </div>
        <div class="comments-modal-side">
            <div class="flex items-center gap-2.5 border-b border-line pb-3">
                <div class="post-user-avatar"></div>
                <a href="#" class="post-username"></a>
                <div class="ml-auto flex items-center gap-2">
                    <?php require __DIR__ . '/logout-link.php'; ?>
                    <button type="button" class="comments-modal-close" aria-label="Cerrar">&times;</button>
                </div>
            </div>
            <div class="post-caption">
                <a href="#" class="post-caption-user"></a>
                <span class="post-caption-text"></span>
            </div>
            <div class="post-comments-list comments-modal-list"></div>
            <div class="comment-reply-chip" hidden>
                <span>Respondiendo a <strong class="comment-reply-target"></strong></span>
                <button type="button" class="comment-reply-cancel" aria-label="Cancelar respuesta">&times;</button>
            </div>
            <form class="comment-form">
                <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off" maxlength="500">
                <button type="submit" class="comment-submit-btn">Publicar</button>
            </form>
        </div>
    </div>
</div>

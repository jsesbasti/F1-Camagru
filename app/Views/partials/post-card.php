<?php
/**
 * Renders one feed post card. Reused by home.php's main feed loop and by
 * the hidden #post-card-template at the bottom of that file, which
 * public/js/home.js clones to render infinite-scroll posts — so this markup
 * only needs to be kept in sync in one place.
 *
 * Expects in scope:
 *   $post        array: id, user_id, username, avatar_initials, image_url,
 *                caption, likes_count, badge, comments[]
 *   $currentUser array|null — the signed-in user, to decide if the
 *                owner menu (edit/delete) should show
 */

$isOwner = $currentUser && (int) ($post['user_id'] ?? 0) === (int) $currentUser['id'];

$imageStyle = '';
if (!empty($post['image_url'])) {
    $escapedImageUrl = htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8');
    $imageStyle = "background-image: url('{$escapedImageUrl}')";
}

$caption = trim((string) ($post['caption'] ?? ''));
$comments = $post['comments'] ?? [];

$profileUrl = static function (string $username): string {
    return $username !== '' ? '/profile?username=' . urlencode($username) : '#';
};

$renderComment = static function (string $text) use ($profileUrl): string {
    return preg_replace_callback(
        '/(?<![\w@])@(\w+)/',
        static fn (array $m) => '<a href="' . htmlspecialchars($profileUrl($m[1])) . '" class="comment-mention">@' . $m[1] . '</a>',
        htmlspecialchars($text)
    );
};
?>
<div class="post-card" id="post-<?= (int) ($post['id'] ?? 0) ?>">
    <div class="post-header">
        <div class="post-user-info">
            <div class="post-user-avatar"><?= \App\Core\Helper::avatar($post['avatar_url'] ?? null, $post['username'] ?? '') ?></div>
            <a href="<?= htmlspecialchars($profileUrl($post['username'] ?? '')) ?>" class="post-username"><?= htmlspecialchars($post['username'] ?? '') ?></a>
        </div>
        <div class="flex items-center gap-2">
            <div class="post-badge"><?= htmlspecialchars($post['badge'] ?? 'Post') ?></div>
            <div class="post-menu"<?= $isOwner ? '' : ' hidden' ?>>
                <button type="button" class="post-menu-btn" aria-label="Post options">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="12" cy="19" r="2"></circle></svg>
                </button>
                <div class="post-menu-dropdown">
                    <button type="button" class="post-menu-item post-edit-btn">Editar</button>
                    <button type="button" class="post-menu-item danger post-delete-btn">Borrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="post-media-container">
        <div class="f1-visual-bg" style="<?= $imageStyle ?>">
            <div class="visual-overlay"></div>
        </div>
    </div>
    <div class="post-actions">
        <button class="action-btn like-btn<?= !empty($post['liked_by_me']) ? ' liked' : '' ?>" aria-label="Like Post" aria-pressed="<?= !empty($post['liked_by_me']) ? 'true' : 'false' ?>">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </button>
        <button class="action-btn comment-trigger-btn" aria-label="Comment">
            <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
        </button>
    </div>
    <div class="post-details">
        <div class="post-likes-count"><span class="likes-num"><?= number_format((int) ($post['likes_count'] ?? 0), 0, ',', '.') ?></span> me gusta</div>
        <div class="post-caption">
            <a href="<?= htmlspecialchars($profileUrl($post['username'] ?? '')) ?>" class="post-caption-user"><?= htmlspecialchars($post['username'] ?? '') ?></a>
            <span class="post-caption-text"><?= htmlspecialchars($caption !== '' ? $caption : 'Sin descripción') ?></span>
        </div>
        <form class="post-edit-form" hidden>
            <textarea class="post-edit-textarea" rows="2" placeholder="Escribe una descripción..."><?= htmlspecialchars($caption) ?></textarea>
            <div class="post-edit-actions">
                <button type="button" class="post-edit-cancel-btn">Cancelar</button>
                <button type="submit" class="post-edit-save-btn">Guardar</button>
            </div>
        </form>
        <div class="post-comments-list">
            <?php foreach ($comments as $thread): ?>
                <div class="comment-thread" data-comment-id="<?= (int) $thread['id'] ?>">
                    <?php foreach ([$thread, ...$thread['replies']] as $comment): ?>
                        <div class="comment-item<?= $comment['parent_id'] ? ' comment-reply' : '' ?>" data-comment-id="<?= (int) $comment['id'] ?>">
                            <a href="<?= htmlspecialchars($profileUrl($comment['username'] ?? '')) ?>" class="comment-user"><?= htmlspecialchars($comment['username']) ?></a>
                            <span class="comment-text"><?= $renderComment($comment['comment']) ?></span>
                            <button type="button" class="comment-reply-btn" data-reply-to="<?= (int) $comment['id'] ?>" data-username="<?= htmlspecialchars($comment['username']) ?>">Responder</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
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

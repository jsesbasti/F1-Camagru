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

$isVideo = \App\Core\Helper::isVideo($post['image_url'] ?? '');

$caption = trim((string) ($post['caption'] ?? ''));
$comments = $post['comments'] ?? [];
// The feed sends a total; the single-post page has every comment, so count them.
$commentsCount = $post['comments_count'] ?? array_sum(array_map(static fn (array $t) => 1 + count($t['replies']), $comments));

$profileUrl = static function (string $username): string {
    return $username !== '' ? '/profile?username=' . urlencode($username) : '#';
};

// Escapes the text and turns every @username into a profile link; used for
// both comments and the caption.
$renderComment = static function (string $text) use ($profileUrl): string {
    return preg_replace_callback(
        '/(?<![\w@])@(\w+)/',
        static fn (array $m) => '<a href="' . htmlspecialchars($profileUrl($m[1])) . '" class="comment-mention">@' . $m[1] . '</a>',
        htmlspecialchars($text)
    );
};

// Long comments show a cut-down version (at a word boundary) plus "see more"; same rule as commentPreview() in home.js.
$commentPreview = static function (string $text): ?string {
    if (mb_strlen($text) <= 150) {
        return null;
    }
    $cut = mb_substr($text, 0, 150);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space > 50 ? mb_substr($cut, 0, $space) : $cut) . '…';
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"></circle><circle cx="12" cy="12" r="2"></circle><circle cx="12" cy="19" r="2"></circle></svg>
                </button>
                <div class="post-menu-dropdown">
                    <button type="button" class="post-menu-item post-edit-btn">Edit</button>
                    <button type="button" class="post-menu-item danger post-delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <div class="post-media-container">
        <?php if ($isVideo): ?>
            <video class="post-media" src="<?= htmlspecialchars($post['image_url']) ?>" muted loop playsinline autoplay></video>
        <?php else: ?>
            <img class="post-media" src="<?= htmlspecialchars($post['image_url'] ?? '') ?>" alt="" loading="lazy">
        <?php endif; ?>
    </div>
    <div class="post-actions">
        <button class="action-btn like-btn<?= !empty($post['liked_by_me']) ? ' liked' : '' ?>" aria-label="Like Post" aria-pressed="<?= !empty($post['liked_by_me']) ? 'true' : 'false' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            <span class="likes-num action-count"><?= number_format((int) ($post['likes_count'] ?? 0), 0, ',', '.') ?></span>
        </button>
        <button class="action-btn comment-trigger-btn" aria-label="Comment">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            <span class="comments-num action-count"><?= number_format((int) $commentsCount, 0, ',', '.') ?></span>
        </button>
    </div>
    <div class="post-details">
        <?php $shortCaption = $caption !== '' ? $commentPreview($caption) : null; ?>
        <div class="post-caption" data-caption="<?= htmlspecialchars($caption) ?>">
            <a href="<?= htmlspecialchars($profileUrl($post['username'] ?? '')) ?>" class="post-caption-user"><?= htmlspecialchars($post['username'] ?? '') ?></a>
            <span class="post-caption-text"><?= $caption !== '' ? $renderComment($shortCaption ?? $caption) : 'No caption' ?></span>
            <?php if ($shortCaption): ?>
                <span class="post-caption-full" hidden><?= $renderComment($caption) ?></span>
                <button type="button" class="comment-more-btn">see more</button>
            <?php endif; ?>
        </div>
        <form class="post-edit-form" hidden>
            <textarea class="post-edit-textarea" rows="2" placeholder="Write a caption..."><?= htmlspecialchars($caption) ?></textarea>
            <div class="post-edit-actions">
                <button type="button" class="post-edit-cancel-btn">Cancel</button>
                <button type="submit" class="post-edit-save-btn">Save</button>
            </div>
        </form>
        <div class="post-comments-list"<?= empty($fullComments) ? ' data-preview' : '' ?>>
            <?php foreach ($comments as $thread): ?>
                <div class="comment-thread" data-comment-id="<?= (int) $thread['id'] ?>">
                    <?php foreach ([$thread, ...$thread['replies']] as $comment): ?>
                        <div class="comment-item<?= $comment['parent_id'] ? ' comment-reply' : '' ?>" data-comment-id="<?= (int) $comment['id'] ?>">
                            <a href="<?= htmlspecialchars($profileUrl($comment['username'] ?? '')) ?>" class="comment-user"><?= htmlspecialchars($comment['username']) ?></a>
                            <?php $short = $commentPreview($comment['comment']); ?>
                            <span class="comment-text"><?= $renderComment($short ?? $comment['comment']) ?></span>
                            <?php if ($short): ?>
                                <span class="comment-text-full" hidden><?= $renderComment($comment['comment']) ?></span>
                                <button type="button" class="comment-more-btn">see more</button>
                            <?php endif; ?>
                            <button type="button" class="comment-reply-btn" data-reply-to="<?= (int) $comment['id'] ?>" data-username="<?= htmlspecialchars($comment['username']) ?>">Reply</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="comment-reply-chip" hidden>
            <span>Replying to <strong class="comment-reply-target"></strong></span>
            <button type="button" class="comment-reply-cancel" aria-label="Cancel reply">&times;</button>
        </div>
        <form class="comment-form">
            <input type="text" placeholder="Add a comment..." class="comment-input" required autocomplete="off" maxlength="500">
            <button type="submit" class="comment-submit-btn">Post</button>
        </form>
    </div>
</div>

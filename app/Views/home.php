    <?php require __DIR__ . "/partials/header.php"; ?>
    <!-- Main Content (Feed y Scroll Infinito) -->
    <main class="main-content">
        <div class="feed-container">
            
            <!-- Drivers Stories (Instagram-style listing of database users) -->
            <?php if (!empty($users)): ?>
                <div class="drivers-stories">
                    <div class="stories-title">Drivers in grid</div>
                    <div class="stories-wrapper">
                        <?php foreach ($users as $user): ?>
                            <div class="story-item">
                                <div class="story-avatar-ring">
                                    <div class="story-avatar">
                                        <?= substr(strtoupper($user['username']), 0, 2) ?>
                                    </div>
                                </div>
                                <span class="story-username"><?= htmlspecialchars($user['username']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php
                        $imageStyle = '';
                        if (!empty($post['image_url'])) {
                            $escapedImageUrl = htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8');
                            $imageStyle = "background-image: url('{$escapedImageUrl}')";
                        }
                        $caption = trim((string) ($post['caption'] ?? ''));
                        $comments = $post['comments'] ?? [];
                    ?>
                    <div class="post-card" id="post-<?= (int) $post['id'] ?>">
                        <div class="post-header">
                            <div class="post-user-info">
                                <div class="post-user-avatar"><?= htmlspecialchars($post['avatar_initials']) ?></div>
                                <a href="#" class="post-username"><?= htmlspecialchars($post['username']) ?></a>
                            </div>
                            <div class="post-badge"><?= htmlspecialchars($post['badge']) ?></div>
                        </div>
                        <div class="post-media-container">
                            <div class="f1-visual-bg" style="<?= $imageStyle ?>">
                                <div class="visual-overlay"></div>
                            </div>
                        </div>
                        <div class="post-actions">
                            <button class="action-btn like-btn" aria-label="Like Post">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            <button class="action-btn comment-trigger-btn" aria-label="Comment">
                                <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            </button>
                        </div>
                        <div class="post-details">
                            <div class="post-likes-count"><span class="likes-num"><?= number_format((int) $post['likes_count'], 0, ',', '.') ?></span> me gusta</div>
                            <div class="post-caption">
                                <a href="#" class="post-caption-user"><?= htmlspecialchars($post['username']) ?></a>
                                <span><?= htmlspecialchars($caption !== '' ? $caption : 'Sin descripción') ?></span>
                            </div>
                            <div class="post-comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <a href="#" class="comment-user"><?= htmlspecialchars($comment['username']) ?></a>
                                        <span><?= htmlspecialchars($comment['comment']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form class="comment-form">
                                <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
                                <button type="submit" class="comment-submit-btn">Upload</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-card">
                    <div class="post-details" style="padding: 24px; text-align: center;">
                        Track clear
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($hasMorePosts)): ?>
                <div id="infinite-loader"
                     class="loader-container"
                     data-endpoint="/api/posts"
                     data-has-more="1"
                     data-next-page="<?= (int) ($nextFeedPage ?? 2) ?>"
                     data-page-size="<?= (int) ($feedPageSize ?? 4) ?>">
                    <div class="f1-loader" aria-label="Loading cars">
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php require __DIR__ . "/modals/add-photo-modal.php"; ?>
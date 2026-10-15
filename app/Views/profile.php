<?php require __DIR__ . "/partials/header.php"; ?>
<main class="mt-[60px] mb-[70px] flex flex-1 items-start justify-center px-2.5 py-5 md:px-5 md:py-[30px] max-[480px]:mb-[75px]">
    <div class="mx-auto flex w-full max-w-[600px] flex-col gap-6">

        <?php if (!empty($userNotFound)): ?>
            <div class="profile-empty-state">
                <p class="font-racing text-sm uppercase tracking-wide text-white">Driver not found</p>
                <p class="text-sm">That user doesn't exist or has left the grid.</p>
                <a href="/" class="photo-action primary max-w-[220px]">Back to feed</a>
            </div>
        <?php else: ?>

            <!-- Profile header -->
            <section class="profile-card">
                <div class="profile-avatar">
                    <?= \App\Core\Helper::avatar($profileUser['avatar'] ?? null, $profileUser['username']) ?>
                </div>
                <div class="flex flex-1 flex-col items-center sm:items-start">
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="profile-username"><?= htmlspecialchars($profileUser['username']) ?></span>
                        <span class="profile-badge">F1 driver</span>
                    </div>
                    <?php if ($isOwnProfile): ?>
                        <span class="profile-email"><?= htmlspecialchars($profileUser['email']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($profileUser['created_at'])): ?>
                        <span class="mt-1 text-xs text-muted">
                            On the grid since <?= htmlspecialchars(date('d M Y', strtotime($profileUser['created_at']))) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="profile-stats">
                    <div>
                        <span class="profile-stat-value"><?= (int) $photosCount ?></span>
                        <span class="profile-stat-label">Photos</span>
                    </div>
                    <div>
                        <span class="profile-stat-value"><?= (int) $likesReceived ?></span>
                        <span class="profile-stat-label">Likes</span>
                    </div>
                </div>
            </section>

            <!-- Photo grid -->
            <?php if (!empty($photos)): ?>
                <div class="profile-photo-grid">
                    <?php foreach ($photos as $photo): ?>
                        <?php
                            $bgStyle = '';
                            if (!empty($photo['image_url'])) {
                                $escapedUrl = htmlspecialchars($photo['image_url'], ENT_QUOTES, 'UTF-8');
                                $bgStyle = "background-image: url('{$escapedUrl}')";
                            }
                        ?>
                        <a href="/post?id=<?= (int) $photo['id'] ?>" class="profile-photo-item group no-underline" style="<?= $bgStyle ?>">
                            <div class="profile-photo-overlay">
                                <span class="profile-photo-stat">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    <?= (int) $photo['likes_count'] ?>
                                </span>
                                <span class="profile-photo-stat">
                                    <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    <?= (int) $photo['comments_count'] ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($isOwnProfile): ?>
                <div class="profile-empty-state">
                    <p class="font-racing text-sm uppercase tracking-wide text-white">No photos yet</p>
                    <p class="text-sm">Capture or upload your first photo to see it here.</p>
                    <a href="/photo" class="photo-action primary max-w-[220px]">Take a photo</a>
                </div>
            <?php else: ?>
                <div class="profile-empty-state">
                    <p class="font-racing text-sm uppercase tracking-wide text-white">No photos yet</p>
                    <p class="text-sm"><?= htmlspecialchars($profileUser['username']) ?> hasn't posted any photos.</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>
<?php require __DIR__ . "/partials/footer-nav.php"; ?>

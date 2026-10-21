<?php require __DIR__ . "/partials/header.php"; ?>
<main class="mt-[60px] mb-[70px] flex flex-1 items-start justify-center px-2.5 py-5 md:px-5 md:py-[30px] max-[480px]:mb-[75px]">
    <div class="feed-container mx-auto flex w-full max-w-[600px] flex-col gap-6" data-current-user-id="<?= (int) ($currentUser['id'] ?? 0) ?>">
        <?php if ($post): ?>
            <?php $fullComments = true; require __DIR__ . '/partials/post-card.php'; ?>
        <?php else: ?>
            <div class="profile-empty-state">
                <p class="font-racing text-sm uppercase tracking-wide text-white">Post not found</p>
                <p class="text-sm">This photo doesn't exist or was removed.</p>
                <a href="/" class="photo-action primary max-w-[220px]">Back to feed</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . "/modals/comments-modal.php"; ?>
<?php require __DIR__ . "/partials/footer-nav.php"; ?>

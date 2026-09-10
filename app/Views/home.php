    <?php require __DIR__ . "/partials/header.php"; ?>
    <!-- Main Content (Feed y Scroll Infinito) -->
    <main class="mt-[60px] mb-[70px] flex flex-1 items-start justify-center px-2.5 py-5 md:px-5 md:py-[30px] max-[480px]:mb-[75px]">
        <div class="feed-container mx-auto flex w-full max-w-[600px] flex-col gap-6" data-current-user-id="<?= (int) ($currentUser['id'] ?? 0) ?>">

            <!-- Drivers Stories (Instagram-style listing of database users) -->
            <?php if (!empty($users)): ?>
                <div class="flex flex-col gap-2.5 rounded-lg border border-line bg-carbon p-[15px]">
                    <div class="border-l-2 border-f1-red pl-1.5 font-racing text-[11px] font-extrabold uppercase tracking-wide text-f1-red">Drivers in grid</div>
                    <div class="flex gap-4 overflow-x-auto pb-1.5 [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1">
                        <?php foreach ($users as $user): ?>
                            <a href="/profile?username=<?= urlencode($user['username']) ?>" class="group flex min-w-[60px] flex-col items-center gap-1.5 no-underline">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,var(--color-f1-red)_30%,var(--color-gold)_70%)] p-0.5 transition-transform duration-200 group-hover:scale-105">
                                    <div class="flex h-full w-full items-center justify-center rounded-full border-2 border-carbon bg-carbon-medium font-racing text-sm font-bold text-white">
                                        <?= substr(strtoupper($user['username']), 0, 2) ?>
                                    </div>
                                </div>
                                <span class="max-w-[60px] truncate text-[11px] text-muted"><?= htmlspecialchars($user['username']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php require __DIR__ . '/partials/post-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-card">
                    <div class="p-6 text-center">
                        Track clear
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($hasMorePosts)): ?>
                <div id="infinite-loader"
                     class="mb-5 flex items-center justify-center py-5"
                     data-endpoint="/api/posts"
                     data-has-more="1"
                     data-next-page="<?= (int) ($nextFeedPage ?? 2) ?>"
                     data-page-size="<?= (int) ($feedPageSize ?? 4) ?>">
                    <div class="flex gap-2" aria-label="Loading cars">
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
    <?php require __DIR__ . "/modals/app-modal.php"; ?>

    <!-- Blank post card, kept only as a DOM template so public/js/home.js can
         clone the exact same markup as above for infinite-scroll posts
         instead of duplicating it in a JS string. Rendered from the same
         partials/post-card.php, with the owner menu forced visible so the
         clone always has it available; home.js hides it per-post itself. -->
    <template id="post-card-template">
        <?php
            (function () {
                $post = [
                    'id' => 0,
                    'user_id' => 1,
                    'username' => '',
                    'avatar_initials' => '??',
                    'image_url' => '',
                    'caption' => '',
                    'likes_count' => 0,
                    'badge' => 'Post',
                    'comments' => [],
                ];
                $currentUser = ['id' => 1];
                require __DIR__ . '/partials/post-card.php';
            })();
        ?>
    </template>

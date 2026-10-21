<?php require __DIR__ . "/partials/header.php"; ?>
<main class="mt-[60px] mb-[70px] flex flex-1 items-start justify-center px-2.5 py-5 md:px-5 md:py-[30px] max-[480px]:mb-[75px]">
    <div class="mx-auto flex w-full max-w-[600px] flex-col gap-4">
        <form action="/search" method="get" class="flex gap-2" id="search-form">
            <input type="search" name="q" id="search-input" autocomplete="off" value="<?= htmlspecialchars($query) ?>" placeholder="Search drivers..." maxlength="50" autofocus class="f1-input !mt-0 flex-1">
            <button type="submit" class="photo-action primary !w-auto">Search</button>
        </form>

        <div class="profile-empty-state" id="search-empty" <?= $query !== '' && !$results ? '' : 'hidden' ?>>
            <p class="text-sm">No drivers match "<span id="search-empty-query"><?= htmlspecialchars($query) ?></span>".</p>
        </div>

        <div class="flex flex-col gap-4" id="search-results">
            <?php foreach ($results as $user): ?>
                <a href="/profile?username=<?= urlencode($user['username']) ?>" class="search-result">
                    <div class="post-user-avatar"><?= \App\Core\Helper::avatar($user['avatar'], $user['username']) ?></div>
                    <span class="font-bold text-white"><?= htmlspecialchars($user['username']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . "/partials/footer-nav.php"; ?>

<div class="flex min-h-screen items-center justify-center p-[15px]">
    <div class="f1-form-card text-center">
        <h1 class="f1-form-title"><?= htmlspecialchars($heading ?? ($ok ? 'Email updated' : 'Link not valid')) ?></h1>
        <p class="<?= $ok ? 'text-white' : 'text-error' ?>"><?= htmlspecialchars($message) ?></p>
        <a href="<?= htmlspecialchars($linkHref ?? '/settings') ?>" class="f1-submit-btn block text-center no-underline"><?= htmlspecialchars($linkText ?? 'Back to settings') ?></a>
    </div>
</div>

<?php require __DIR__ . "/partials/header.php"; ?>
<main class="mt-[60px] mb-[70px] flex flex-1 items-start justify-center px-2.5 py-5 md:px-5 md:py-[30px] max-[480px]:mb-[75px]">
    <div class="mx-auto flex w-full max-w-[600px] flex-col gap-6">

        <h1 class="f1-form-title !mb-0">Settings</h1>

        <section class="settings-card" id="avatar-card">
            <h2 class="settings-title">Profile photo</h2>
            <div class="mt-4 flex items-center gap-5 max-[480px]:flex-col">
                <div class="profile-avatar" id="avatar-preview" data-initials="<?= htmlspecialchars(strtoupper(substr($currentUser['username'], 0, 2))) ?>">
                    <?= \App\Core\Helper::avatar($currentUser['avatar'] ?? null, $currentUser['username']) ?>
                </div>
                <div class="flex w-full flex-1 flex-col gap-2">
                    <label class="photo-action primary" for="avatar-input">Change photo</label>
                    <input type="file" id="avatar-input" accept="image/*" class="hidden">
                    <button type="button" class="photo-action secondary" id="avatar-remove" <?= empty($currentUser['avatar']) ? 'hidden' : '' ?>>Remove photo</button>
                </div>
            </div>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </section>

        <form class="settings-card" data-endpoint="/api/settings/username" data-sync="username">
            <h2 class="settings-title">Username</h2>
            <p class="settings-hint">4-20 characters: letters, numbers and underscores.</p>
            <input type="text" name="username" class="f1-input" placeholder="New username" value="<?= htmlspecialchars($currentUser['username']) ?>" autocomplete="username" required>
            <p class="error-msg" data-error="username"></p>
            <input type="password" name="current_password" class="f1-input" placeholder="Current password" autocomplete="current-password" required>
            <p class="error-msg" data-error="current_password"></p>
            <button type="submit" class="f1-submit-btn">Update username</button>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </form>

        <form class="settings-card" data-endpoint="/api/settings/email">
            <h2 class="settings-title">Email</h2>
            <p class="settings-hint">We'll send a confirmation link to the new address; your email only changes once you open it.</p>
            <p class="settings-hint">Current: <span class="text-white" data-current="email"><?= htmlspecialchars($currentUser['email']) ?></span></p>
            <p class="settings-hint" data-pending <?= $pendingEmail ? '' : 'hidden' ?>>Pending confirmation: <span class="text-gold" data-pending-email><?= htmlspecialchars($pendingEmail ?? '') ?></span>. Open the link we sent there to finish the change.</p>
            <input type="email" name="email" class="f1-input" placeholder="New email" autocomplete="email" required>
            <p class="error-msg" data-error="email"></p>
            <input type="password" name="current_password" class="f1-input" placeholder="Current password" autocomplete="current-password" required>
            <p class="error-msg" data-error="current_password"></p>
            <button type="submit" class="f1-submit-btn">Update email</button>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </form>

        <form class="settings-card" data-endpoint="/api/settings/password">
            <h2 class="settings-title">Password</h2>
            <p class="settings-hint">At least 12 characters with upper and lower case letters, a number and a special character.</p>
            <input type="password" name="current_password" class="f1-input" placeholder="Current password" autocomplete="current-password" required>
            <p class="error-msg" data-error="current_password"></p>
            <input type="password" name="new_password" class="f1-input" placeholder="New password" autocomplete="new-password" required>
            <p class="error-msg" data-error="new_password"></p>
            <input type="password" name="confirm" class="f1-input" placeholder="Confirm new password" autocomplete="new-password" required>
            <p class="error-msg" data-error="confirm"></p>
            <button type="submit" class="f1-submit-btn">Update password</button>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </form>

        <section class="settings-card" id="notifications-card">
            <h2 class="settings-title">Notifications</h2>
            <label class="mt-3 flex cursor-pointer items-center justify-between gap-4">
                <span>
                    <span class="block text-white">Email notifications</span>
                    <span class="settings-hint !mt-0 block">Email me when someone comments on my photos, replies to my comments, mentions me or starts following me.</span>
                </span>
                <input type="checkbox" id="notify-comments" class="h-5 w-5 flex-shrink-0 accent-f1-red" <?= $currentUser['notify_comments'] ? 'checked' : '' ?>>
            </label>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </section>

        <form class="settings-card !border-t-error" data-endpoint="/api/settings/delete" data-confirm="This will permanently delete your account, photos, likes and comments. This cannot be undone.">
            <h2 class="settings-title text-error">Delete account</h2>
            <p class="settings-hint">Permanently removes your account and everything you've posted.</p>
            <input type="password" name="current_password" class="f1-input" placeholder="Current password" autocomplete="current-password" required>
            <p class="error-msg" data-error="current_password"></p>
            <button type="submit" class="settings-danger-btn">Delete account</button>
            <p class="error-msg" data-error="error"></p>
            <p class="error-msg text-start-green" data-success></p>
        </form>

    </div>
</main>
<?php require __DIR__ . "/partials/footer-nav.php"; ?>

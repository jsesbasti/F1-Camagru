<div class="flex min-h-screen items-center justify-center p-[15px]">
    <a href="/login" class="f1-back-link">
        ← LOGIN
    </a>
    <form id="reset-password-form" class="f1-form-card">
        <h1 class="f1-form-title">New Password</h1>
        <input type="hidden" name="token" id="token" value="<?= htmlspecialchars($token) ?>">
        <div>
            <input
                type="password"
                name="password"
                id="password"
                placeholder="New password"
                required
                class="f1-input"
            >
            <p id="password-error" class="error-msg"></p>
            <input
                type="password"
                name="confirm"
                id="confirm"
                placeholder="Confirm new password"
                required
                class="f1-input"
            >
            <p id="confirm-error" class="error-msg"></p>
            <button type="submit" name="reset-password" class="f1-submit-btn">
                UPDATE PASSWORD
            </button>
        </div>
        <p id="error-error" class="error-msg"></p>
    </form>
</div>

<div class="flex min-h-screen items-center justify-center p-[15px]">
    <a href="/login" class="f1-back-link">
        ← LOGIN
    </a>
    <form id="forgot-password-form" class="f1-form-card">
        <h1 class="f1-form-title">Reset Password</h1>
        <p class="mb-4 text-center text-sm text-muted">
            Enter the email on your account and we'll send you a link to reset your password.
        </p>
        <?php if (!empty($_GET['expired'])): ?>
            <p class="error-msg mb-2">That reset link is invalid or has expired. Request a new one below.</p>
        <?php endif; ?>
        <div>
            <input
                type="email"
                name="email"
                id="email"
                autocomplete="on"
                placeholder="Email"
                required
                class="f1-input"
            >
            <p id="email-error" class="error-msg"></p>
            <button type="submit" name="forgot-password" class="f1-submit-btn">
                SEND RESET LINK
            </button>
        </div>
        <p id="success-msg" class="error-msg text-start-green"></p>
        <p id="error-error" class="error-msg"></p>
    </form>
</div>

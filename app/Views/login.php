<div class="flex min-h-screen items-center justify-center p-[15px]">
    <a href="/" class="f1-back-link">
        ← HOME
    </a>
    <form id="login-form" class="f1-form-card">
        <h1 class="f1-form-title">F1 CAMAGRU</h1>
        <div>
            <input
                type="text"
                name="username"
                id="username"
                autocomplete="on"
                placeholder="Username"
                required
                class="f1-input"
            >
            <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
                class="f1-input"
            >
            <button type="submit" name="login" class="f1-submit-btn">
                LOGIN
            </button>
            <a href="/forgot-password" class="mt-4 block text-center text-sm text-muted no-underline hover:text-f1-red">
                Forgot your password?
            </a>
            <p class="mt-2 text-center text-sm text-muted">
                No account yet?
                <a href="/register" class="font-bold text-white no-underline hover:text-f1-red">Register</a>
            </p>
        </div>
        <p id="error-error" class="error-msg"></p>
    </form>
</div>

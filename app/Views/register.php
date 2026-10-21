<div class="flex min-h-screen items-center justify-center p-[15px]">
    <a href="/" class="f1-back-link">
        ← HOME
    </a>
    <form id="register-form" class="f1-form-card">
        <h1 class="f1-form-title">Register</h1>
        <div>
            <input type="text" name="username" id="username" autocomplete="on" placeholder="username" class="f1-input">
            <p id="username-error" class="error-msg"></p>
            <input type="text" name="email" id="email" autocomplete="on" placeholder="email" class="f1-input">
            <p id="email-error" class="error-msg"></p>
            <input type="password" name="password" id="password" placeholder="password" class="f1-input">
            <p id="password-error" class="error-msg"></p>
            <input type="password" name="confirm" id="confirm" placeholder="confirm password" class="f1-input">
            <p id="confirm-error" class="error-msg"></p>
            <button enabled="false" type="submit" name="register" class="f1-submit-btn">Register</button>
        </div>
        <p id="error-error" class="error-msg"></p>
    </form>
</div>

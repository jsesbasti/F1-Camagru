<main class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,var(--color-carbon),var(--color-asphalt))] p-8 text-white">
<a href="/" class="f1-back-link">
    ← HOME
</a>
<div class="relative w-[min(92%,560px)] overflow-hidden rounded-2xl border border-line border-t-4 border-t-f1-red bg-carbon/95 p-12 text-center shadow-[0_20px_50px_rgba(0,0,0,0.7)] before:pointer-events-none before:absolute before:inset-0 before:bg-[repeating-linear-gradient(-45deg,transparent,transparent_18px,rgba(255,255,255,0.015)_18px,rgba(255,255,255,0.015)_20px)] before:content-[''] max-[600px]:p-8">
    <span class="mb-8 inline-block font-racing text-[0.95rem] uppercase tracking-[2px] text-start-green">
        ● STATUS : WAITING FOR EMAIL VERIFICATION
    </span>
    <div class="mb-6 text-[5rem] text-gold [text-shadow:0_0_20px_rgba(255,215,0,0.3)] max-[600px]:text-[4rem]">
        ✉
    </div>
    <h1 class="font-racing text-[3.2rem] uppercase tracking-[3px] text-white max-[600px]:text-[2.4rem]">CHECK YOUR INBOX</h1>
    <p class="mt-7 leading-relaxed text-white">
        We've sent a verification email to the address you registered with.
    </p>
    <p class="mt-3 leading-relaxed text-muted">
        Click the verification link inside the email to activate your Camagru account.
    </p>
    <div class="mx-auto my-10 h-1 w-20 rounded-[10px] bg-f1-red shadow-[0_0_12px_rgba(225,6,0,0.4)]"></div>
    <div class="mb-8 rounded-lg border border-line border-l-4 border-l-gold bg-asphalt/70 p-5 text-left">
        <h2 class="mb-3 font-racing text-2xl tracking-wide text-gold">NEXT STEP</h2>
        <p class="leading-relaxed text-muted">
            If you don't receive the email within a few minutes,
            check your spam folder or request a new verification email.
        </p>
    </div>
    <form id="resend-form">
        <input type="email" id="email" class="f1-input !mt-0 mb-3" placeholder="Your email" value="<?= htmlspecialchars($pendingEmail) ?>" required>
        <p id="email-error" class="error-msg mb-3"></p>
        <button type="submit" id="resend-btn" class="w-full rounded-lg border-2 border-f1-red p-4 font-racing text-xl tracking-wide text-white transition-all duration-[250ms] hover:-translate-y-0.5 hover:bg-f1-red hover:shadow-[0_0_20px_rgba(225,6,0,0.4)]">
            RESEND EMAIL
        </button>
        <p id="success-msg" class="error-msg text-start-green"></p>
        <p id="error-error" class="error-msg"></p>
    </form>
</div>
</main>

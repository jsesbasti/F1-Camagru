<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Verify Email') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="/assets/css/colors.css">

    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/verify.css">
</head>

<body>

<main class="verify-page">

    <div class="verify-card">

        <span class="status">
            ● STATUS : WAITING FOR EMAIL VERIFICATION
        </span>

        <div class="mail-icon">
            ✉
        </div>

        <h1>CHECK YOUR INBOX</h1>

        <p class="main-text">
            We've sent a verification email to the address you registered with.
        </p>

        <p class="secondary-text">
            Click the verification link inside the email to activate your Camagru account.
        </p>

        <div class="divider"></div>

        <div class="notice">

            <h2>NEXT STEP</h2>

            <p>
                If you don't receive the email within a few minutes,
                check your spam folder or request a new verification email.
            </p>

        </div>

        <button id="resend-btn">
            RESEND EMAIL
        </button>

    </div>

</main>

</body>
</html>
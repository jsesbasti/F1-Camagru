<header class="f1-header">
    <a href="/" class="flex items-center gap-2 no-underline">
        <div class="flex h-6 w-6 -skew-x-[15deg] items-center justify-center bg-f1-red font-racing text-sm font-black italic text-white">F1</div>
        <span class="inline-block bg-[linear-gradient(135deg,#fff_60%,var(--color-f1-red))] bg-clip-text pr-1 font-racing text-[26px] font-extrabold italic uppercase leading-none tracking-wide text-transparent antialiased max-[480px]:text-[22px]">Camagru</span>
    </a>
    <?php if ($currentUser): ?>
        <div class="flex items-center gap-2.5">
        <a href="/logout" class="f1-logout-btn" aria-label="Logout" title="Logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span class="max-[480px]:hidden">Logout</span>
        </a>
        <!-- Menú Desplegable de Usuario Logueado -->
        <div class="relative">
            <button class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 border-line p-0 transition-all duration-300 hover:border-f1-red hover:shadow-[0_0_10px_rgba(225,6,0,0.4)]" id="user-profile-btn" aria-label="User menu">
                <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,var(--color-carbon-light),var(--color-f1-red))] font-racing text-base font-bold text-white" data-avatar>
                    <?= \App\Core\Helper::avatar($currentUser['avatar'] ?? null, $currentUser['username']) ?>
                </div>
            </button>
            <div class="dropdown-menu" id="dropdown-menu">
                <div class="border-b border-line px-[18px] py-3.5">
                    <div class="dropdown-username mb-0.5 truncate text-[15px] font-bold text-white"><?= htmlspecialchars($currentUser['username']) ?></div>
                    <div class="font-racing text-[11px] uppercase tracking-wide text-f1-red">F1 driver</div>
                </div>
                <a href="/profile" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    My Profile
                </a>
                <a href="/settings" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Settings
                </a>
            </div>
        </div>
        </div>
    <?php else: ?>
        <!-- Botones de Opción para Usuarios no Logueados -->
        <div class="flex gap-3">
            <a href="/login" class="f1-nav-btn f1-nav-btn-login"><span>Login</span></a>
            <a href="/register" class="f1-nav-btn f1-nav-btn-register"><span>Register</span></a>
        </div>
    <?php endif; ?>
</header>

<footer class="fixed inset-x-0 bottom-0 z-[1000] flex h-[60px] items-center justify-around border-t-2 border-line bg-carbon/90 shadow-[0_-4px_20px_rgba(0,0,0,0.5)] backdrop-blur-md backdrop-saturate-[1.8]">
    <a href="/" class="footer-nav-btn active" aria-label="Home">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <button class="footer-add-btn" id="add-photo-btn" aria-label="Añadir foto">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
    </button>
    <?php if ($currentUser): ?>
        <a href="/profile" class="footer-nav-btn" aria-label="Perfil">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </a>
    <?php else: ?>
        <a href="/login" class="footer-nav-btn" aria-label="Login">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
        </a>
    <?php endif; ?>
</footer>

<?php require __DIR__ . "/../modals/add-photo-modal.php"; ?>
<?php require __DIR__ . "/../modals/app-modal.php"; ?>

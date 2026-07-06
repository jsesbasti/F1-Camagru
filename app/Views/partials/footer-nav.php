<footer class="footer">
    <a href="/" class="footer-nav-btn active" aria-label="Home">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <button class="footer-add-btn" id="add-photo-btn" aria-label="Añadir foto">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
    </button>
    <?php if ($currentUser): ?>
        <a href="/profile" class="footer-nav-btn" aria-label="Perfil">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </a>
    <?php else: ?>
        <a href="/login" class="footer-nav-btn" aria-label="Login">
            <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
        </a>
    <?php endif; ?>
</footer>
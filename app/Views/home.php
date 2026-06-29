<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <!-- Google Fonts: Barlow Condensed for racing aesthetic, Inter for body text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/css/colors.css">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700;1,800;1,900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body data-authenticated="<?= $currentUser ? '1' : '0' ?>">

    <!-- Header Estático (Siempre Visible) -->
    <header class="header">
        <a href="/" class="logo-container">
            <div class="logo-icon">F1</div>
            <span class="logo-text">Camagru</span>
        </a>

        <?php if ($currentUser): ?>
            <!-- Menú Desplegable de Usuario Logueado -->
            <div class="user-menu-container">
                <button class="user-profile-btn" id="user-profile-btn" aria-label="Menú de usuario">
                    <div class="user-avatar-placeholder">
                        <?= substr(strtoupper($currentUser['username']), 0, 2) ?>
                    </div>
                </button>
                <div class="dropdown-menu" id="dropdown-menu">
                    <div class="dropdown-header">
                        <div class="dropdown-username"><?= htmlspecialchars($currentUser['username']) ?></div>
                        <div class="dropdown-role">Piloto Oficial</div>
                    </div>
                    <a href="/profile" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Mi Perfil
                    </a>
                    <a href="/settings" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Configuración
                    </a>
                    <a href="/logout" class="dropdown-item logout">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Botones de Opción para Usuarios no Logueados -->
            <div class="auth-buttons">
                <a href="/login" class="btn btn-login"><span>Login</span></a>
                <a href="/register" class="btn btn-register"><span>Registro</span></a>
            </div>
        <?php endif; ?>
    </header>

    <!-- Main Content (Feed y Scroll Infinito) -->
    <main class="main-content">
        <div class="feed-container">
            
            <!-- Drivers Stories (Instagram-style listing of database users) -->
            <?php if (!empty($users)): ?>
                <div class="drivers-stories">
                    <div class="stories-title">Pilotos en Parrilla</div>
                    <div class="stories-wrapper">
                        <?php foreach ($users as $user): ?>
                            <div class="story-item">
                                <div class="story-avatar-ring">
                                    <div class="story-avatar">
                                        <?= substr(strtoupper($user['username']), 0, 2) ?>
                                    </div>
                                </div>
                                <span class="story-username"><?= htmlspecialchars($user['username']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php
                        $imageStyle = '';
                        if (!empty($post['image_url'])) {
                            $escapedImageUrl = htmlspecialchars($post['image_url'], ENT_QUOTES, 'UTF-8');
                            $imageStyle = "background-image: url('{$escapedImageUrl}')";
                        }
                        $caption = trim((string) ($post['caption'] ?? ''));
                        $comments = $post['comments'] ?? [];
                    ?>
                    <div class="post-card" id="post-<?= (int) $post['id'] ?>">
                        <div class="post-header">
                            <div class="post-user-info">
                                <div class="post-user-avatar"><?= htmlspecialchars($post['avatar_initials']) ?></div>
                                <a href="#" class="post-username"><?= htmlspecialchars($post['username']) ?></a>
                            </div>
                            <div class="post-badge"><?= htmlspecialchars($post['badge']) ?></div>
                        </div>
                        <div class="post-media-container">
                            <div class="f1-visual-bg" style="<?= $imageStyle ?>">
                                <div class="visual-overlay"></div>
                            </div>
                        </div>
                        <div class="post-actions">
                            <button class="action-btn like-btn" aria-label="Like Post">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            <button class="action-btn comment-trigger-btn" aria-label="Comment">
                                <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            </button>
                        </div>
                        <div class="post-details">
                            <div class="post-likes-count"><span class="likes-num"><?= number_format((int) $post['likes_count'], 0, ',', '.') ?></span> me gusta</div>
                            <div class="post-caption">
                                <a href="#" class="post-caption-user"><?= htmlspecialchars($post['username']) ?></a>
                                <span><?= htmlspecialchars($caption !== '' ? $caption : 'Sin descripción') ?></span>
                            </div>
                            <div class="post-comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <a href="#" class="comment-user"><?= htmlspecialchars($comment['username']) ?></a>
                                        <span><?= htmlspecialchars($comment['comment']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form class="comment-form">
                                <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
                                <button type="submit" class="comment-submit-btn">Publicar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-card">
                    <div class="post-details" style="padding: 24px; text-align: center;">
                        Aún no hay publicaciones para mostrar.
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($hasMorePosts)): ?>
                <div id="infinite-loader"
                     class="loader-container"
                     data-endpoint="/api/posts"
                     data-has-more="1"
                     data-next-page="<?= (int) ($nextFeedPage ?? 2) ?>"
                     data-page-size="<?= (int) ($feedPageSize ?? 4) ?>">
                    <div class="f1-loader" aria-label="Cargando más publicaciones">
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                        <div class="f1-light"></div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Footer Estático (Siempre Visible) -->
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

    <!-- Modal para añadir fotos (Marcador interactivo) -->
    <div class="modal-overlay" id="add-post-modal" data-authenticated="<?= $currentUser ? '1' : '0' ?>">
        <div class="modal-card">
            <h2 class="modal-title" id="add-photo-modal-title">Cargar Telemetría</h2>
            <div class="modal-content" id="add-photo-modal-content">
                <div class="modal-state modal-state-auth" id="add-photo-auth-state" hidden>
                    <p>Para continuar necesitas activar la cámara y entrar al área de captura.</p>
                    <p style="margin-top: 10px; color: var(--f1-red); font-family: var(--font-racing); font-weight: 700; text-transform: uppercase;">Boxes listos para recibir tu imagen.</p>
                    <button class="modal-primary-btn" id="add-photo-camera-btn" type="button">Permitir cámara y continuar</button>
                    <p class="modal-status" id="add-photo-status" aria-live="polite"></p>
                </div>
                <div class="modal-state modal-state-guest" id="add-photo-guest-state" hidden>
                    <p>Primero te has de hacer login para poder subir fotos o capturar nuevas imágenes.</p>
                    <p style="margin-top: 10px; color: var(--f1-red); font-family: var(--font-racing); font-weight: 700; text-transform: uppercase;">Entra a boxes para continuar.</p>
                    <div class="modal-auth-actions">
                        <a class="modal-secondary-btn" href="/login">Login</a>
                        <a class="modal-primary-btn" href="/register">Register</a>
                    </div>
                </div>
            </div>
            <button class="modal-close-btn" id="modal-close-btn">Volver a Pista</button>
        </div>
    </div>

    <!-- Script de interacción -->
    <script src="js/home.js"></script>
</body>
</html>

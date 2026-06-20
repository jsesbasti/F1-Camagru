<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <!-- Google Fonts: Barlow Condensed for racing aesthetic, Inter for body text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700;1,800;1,900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>

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

            <!-- Post 1: Monaco GP -->
            <div class="post-card" id="post-1">
                <div class="post-header">
                    <div class="post-user-info">
                        <div class="post-user-avatar">CL</div>
                        <a href="#" class="post-username">charles_leclerc</a>
                    </div>
                    <div class="post-badge">Pole Position</div>
                </div>
                <div class="post-media-container">
                    <div class="f1-visual-bg" style="background: linear-gradient(135deg, #FF416C, #FF4B2B)">
                        <div class="visual-overlay"></div>
                        <div class="visual-top-info">
                            <span class="visual-lap-time">1:12.909</span>
                            <span style="font-family: var(--font-racing); color: var(--f1-red); font-weight:700; z-index:2; text-shadow:0 2px 4px rgba(0,0,0,0.8);">MONACO GP</span>
                        </div>
                        
                        <!-- SVG de Trazado de Mónaco -->
                        <svg class="visual-circuit-map" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 20 C40 20, 50 10, 80 15 C110 20, 120 40, 140 45 C170 50, 190 70, 180 85 C170 100, 110 90, 90 75 C70 60, 50 85, 30 70 C10 55, 10 30, 10 20 Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="80" cy="15" r="5" fill="#E10600" />
                        </svg>

                        <div class="visual-bottom-data">
                            <div class="visual-speedometer">
                                <span class="speed-value">314</span>
                                <span class="speed-unit">KM/H</span>
                                <span style="font-family: var(--font-racing); font-size:18px; font-weight:800; color:#FFD700; margin-left:15px; font-style:italic; text-shadow:0 2px 5px rgba(0,0,0,0.5);">MARCHA 7</span>
                            </div>
                            <div class="visual-telemetry">
                                <div class="telemetry-item">DRS: <span class="telemetry-val" style="color: #00E676;">ON</span></div>
                                <div class="telemetry-item">RPM: <span class="telemetry-val">12.100</span></div>
                                <div class="telemetry-item">TEMP: <span class="telemetry-val">96°C</span></div>
                            </div>
                        </div>
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
                    <div class="post-likes-count"><span class="likes-num">18.420</span> me gusta</div>
                    <div class="post-caption">
                        <a href="#" class="post-caption-user">charles_leclerc</a>
                        <span>Sensación única volando por las calles de casa. ¡La pole es nuestra! 🇲🇨🏆 #F1 #MonacoGP</span>
                    </div>
                    <div class="post-comments-list">
                        <div class="comment-item">
                            <a href="#" class="comment-user">lewis_44</a>
                            <span>Gran vuelta, Charles! Mañana será una buena batalla. 🏁</span>
                        </div>
                    </div>
                    <form class="comment-form">
                        <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
                        <button type="submit" class="comment-submit-btn">Publicar</button>
                    </form>
                </div>
            </div>

            <!-- Post 2: Spa GP -->
            <div class="post-card" id="post-2">
                <div class="post-header">
                    <div class="post-user-info">
                        <div class="post-user-avatar">LN</div>
                        <a href="#" class="post-username">ln4</a>
                    </div>
                    <div class="post-badge">Eau Rouge Split</div>
                </div>
                <div class="post-media-container">
                    <div class="f1-visual-bg" style="background: linear-gradient(135deg, #1A2980, #26D0CE)">
                        <div class="visual-overlay"></div>
                        <div class="visual-top-info">
                            <span class="visual-lap-time">1:46.286</span>
                            <span style="font-family: var(--font-racing); color: var(--f1-red); font-weight:700; z-index:2; text-shadow:0 2px 4px rgba(0,0,0,0.8);">SPA GP</span>
                        </div>
                        
                        <!-- SVG de Trazado de Spa -->
                        <svg class="visual-circuit-map" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 15 C60 10, 120 20, 150 15 C180 10, 190 40, 180 60 C170 80, 140 70, 110 85 C80 100, 40 90, 20 70 C5 50, 10 30, 15 15 Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="150" cy="15" r="5" fill="#E10600" />
                        </svg>

                        <div class="visual-bottom-data">
                            <div class="visual-speedometer">
                                <span class="speed-value">332</span>
                                <span class="speed-unit">KM/H</span>
                                <span style="font-family: var(--font-racing); font-size:18px; font-weight:800; color:#FFD700; margin-left:15px; font-style:italic; text-shadow:0 2px 5px rgba(0,0,0,0.5);">MARCHA 8</span>
                            </div>
                            <div class="visual-telemetry">
                                <div class="telemetry-item">DRS: <span class="telemetry-val" style="color: var(--text-secondary);">OFF</span></div>
                                <div class="telemetry-item">RPM: <span class="telemetry-val">11.850</span></div>
                                <div class="telemetry-item">TEMP: <span class="telemetry-val">98°C</span></div>
                            </div>
                        </div>
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
                    <div class="post-likes-count"><span class="likes-num">12.980</span> me gusta</div>
                    <div class="post-caption">
                        <a href="#" class="post-caption-user">ln4</a>
                        <span>Curva a fondo en Eau Rouge. La compresión de la gravedad es de locos en esta parte de Spa. 🇧🇪🏎️ #McLaren #BelgianGP</span>
                    </div>
                    <div class="post-comments-list">
                        <div class="comment-item">
                            <a href="#" class="comment-user">mclaren_official</a>
                            <span>¡Telemetry looks fast, Lando! 🟧⚡</span>
                        </div>
                    </div>
                    <form class="comment-form">
                        <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
                        <button type="submit" class="comment-submit-btn">Publicar</button>
                    </form>
                </div>
            </div>

            <!-- Loader de F1 para Scroll Infinito -->
            <div id="infinite-loader" class="loader-container">
                <div class="f1-loader" aria-label="Cargando más publicaciones">
                    <div class="f1-light"></div>
                    <div class="f1-light"></div>
                    <div class="f1-light"></div>
                    <div class="f1-light"></div>
                    <div class="f1-light"></div>
                </div>
            </div>

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
    <div class="modal-overlay" id="add-post-modal">
        <div class="modal-card">
            <h2 class="modal-title">Cargar Telemetría</h2>
            <div class="modal-content">
                <p>¡Prepara tu cámara de boxes! Aquí podrás añadir tus capturas de carreras o imágenes favoritas de la Fórmula 1.</p>
                <p style="margin-top: 10px; color: var(--f1-red); font-family: var(--font-racing); font-weight: 700; text-transform: uppercase;">Servicio de boxes listo 🏁</p>
            </div>
            <button class="modal-close-btn" id="modal-close-btn">Volver a Pista</button>
        </div>
    </div>

    <!-- Script de interacción -->
    <script src="js/home.js"></script>
</body>
</html>

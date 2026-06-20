document.addEventListener('DOMContentLoaded', () => {
  // Initialize elements
  initUserDropdown();
  initLikeAndCommentSystem();
  initInfiniteScroll();
  initAddPhotoModal();
});

/**
 * 1. User Profile Dropdown Menu Lógica
 */
function initUserDropdown() {
  const profileBtn = document.getElementById('user-profile-btn');
  const dropdownMenu = document.getElementById('dropdown-menu');

  if (!profileBtn || !dropdownMenu) return;

  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isShown = dropdownMenu.classList.contains('show');
    
    if (isShown) {
      closeDropdown();
    } else {
      dropdownMenu.style.display = 'flex';
      // Allow display flex to apply before adding class for transition
      setTimeout(() => {
        dropdownMenu.classList.add('show');
      }, 10);
    }
  });

  // Close dropdown on click outside
  document.addEventListener('click', (e) => {
    if (!dropdownMenu.contains(e.target) && e.target !== profileBtn) {
      closeDropdown();
    }
  });

  // Close dropdown on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeDropdown();
    }
  });

  function closeDropdown() {
    dropdownMenu.classList.remove('show');
    // Hide display after transition completes
    setTimeout(() => {
      if (!dropdownMenu.classList.contains('show')) {
        dropdownMenu.style.display = 'none';
      }
    }, 250);
  }
}

/**
 * 2. Lógica de Interacciones de Post (Me gusta y Comentarios)
 * Usamos delegación de eventos en el contenedor del feed para optimizar el rendimiento
 */
function initLikeAndCommentSystem() {
  const feed = document.querySelector('.feed-container');
  if (!feed) return;

  // Manejar Clicks (likes)
  feed.addEventListener('click', (e) => {
    const likeBtn = e.target.closest('.like-btn');
    if (likeBtn) {
      e.preventDefault();
      const isLiked = likeBtn.classList.contains('liked');
      const card = likeBtn.closest('.post-card');
      const countEl = card.querySelector('.likes-num');
      let likes = parseInt(countEl.textContent.replace(/\./g, ''), 10) || 0;

      if (isLiked) {
        likeBtn.classList.remove('liked');
        likes--;
      } else {
        likeBtn.classList.add('liked');
        likes++;
      }

      countEl.textContent = formatNumber(likes);
    }
  });

  // Manejar Envío de Comentarios
  feed.addEventListener('submit', (e) => {
    const form = e.target.closest('.comment-form');
    if (form) {
      e.preventDefault();
      const input = form.querySelector('.comment-input');
      const commentText = input.value.trim();
      if (!commentText) return;

      const card = form.closest('.post-card');
      const commentsList = card.querySelector('.post-comments-list');
      
      // Get current user or mock name
      const currentUserNameElement = document.querySelector('.dropdown-username');
      const currentUsername = currentUserNameElement ? currentUserNameElement.textContent.trim() : 'visitante_f1';

      // Create new comment element
      const commentItem = document.createElement('div');
      commentItem.className = 'comment-item';
      commentItem.innerHTML = `
        <a href="#" class="comment-user">${currentUsername}</a>
        <span>${escapeHTML(commentText)}</span>
      `;

      commentsList.appendChild(commentItem);
      commentsList.scrollTop = commentsList.scrollHeight; // Auto-scroll to bottom of comments
      
      // Reset input
      input.value = '';
    }
  });
}

/**
 * Helper to format numbers (e.g. 15400 -> 15.400)
 */
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Helper to escape HTML and prevent XSS
 */
function escapeHTML(str) {
  return str.replace(/[&<>'"]/g, 
    tag => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#39;',
      '"': '&quot;'
    }[tag] || tag)
  );
}

/**
 * 3. Lógica de Scroll Infinito con Semáforo de F1
 */
function initInfiniteScroll() {
  const loader = document.getElementById('infinite-loader');
  const feed = document.querySelector('.feed-container');
  if (!loader || !feed) return;

  let postCounter = 3; // Starts after initial 3 posts
  let isLoading = false;

  // F1 tracks mock data for infinite generator
  const f1Tracks = [
    { name: 'Monaco GP', map: 'M10 20 C40 20, 50 10, 80 15 C110 20, 120 40, 140 45 C170 50, 190 70, 180 85 C170 100, 110 90, 90 75 C70 60, 50 85, 30 70 C10 55, 10 30, 10 20 Z', carX: 10, carY: 20, lap: '1:12.909', startColor: '#FF416C', endColor: '#FF4B2B' },
    { name: 'Spa GP', map: 'M15 15 C60 10, 120 20, 150 15 C180 10, 190 40, 180 60 C170 80, 140 70, 110 85 C80 100, 40 90, 20 70 C5 50, 10 30, 15 15 Z', carX: 15, carY: 15, lap: '1:46.286', startColor: '#1A2980', endColor: '#26D0CE' },
    { name: 'Silverstone GP', map: 'M25 25 C75 10, 125 15, 175 25 C190 45, 160 75, 130 65 C100 55, 80 85, 50 85 C20 85, 10 55, 25 25 Z', carX: 25, carY: 25, lap: '1:27.097', startColor: '#11998e', endColor: '#38ef7d' },
    { name: 'Monza GP', map: 'M10 50 C10 20, 190 10, 190 50 C190 80, 170 90, 130 90 C90 90, 10 80, 10 50 Z', carX: 10, carY: 50, lap: '1:18.887', startColor: '#8E2DE2', endColor: '#4A00E0' },
    { name: 'Suzuka GP', map: 'M10 50 C10 20, 80 20, 100 50 C120 80, 190 80, 190 50 C190 20, 120 20, 100 50 C80 80, 10 80, 10 50 Z', carX: 10, carY: 50, lap: '1:30.983', startColor: '#f857a6', endColor: '#ff5858' }
  ];

  const f1Users = ['charles_leclerc', 'ln4', 'max_verstappen', 'lewis_44', 'georgerussell63', 'carloszainz', 'fernando_alonso'];
  const f1Captions = [
    '¡Vuelta rápida completada en los Libres 2! Sintonizando la aerodinámica para la Clasificación. 🏎️📊',
    'Las curvas rápidas exigen el máximo agarre del neumático blando. Telemetría lista. 🔥🔥',
    'Simulación de carrera completada. La degradación de los neumáticos es menor de lo esperado.',
    'Sintiendo la fuerza G en la mítica curva de Eau Rouge. ¡No hay nada como esto! 🇧🇪🏁',
    'Configuración del alerón delantero ajustada para carga aerodinámica extrema.'
  ];

  // IntersectionObserver to watch the loader
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoading) {
      triggerF1LightsLoad();
    }
  }, {
    rootMargin: '100px' // Load when loader is 100px from viewport bottom
  });

  observer.observe(loader);

  /**
   * F1 Start Light Sequence for Loading Simulation
   */
  function triggerF1LightsLoad() {
    isLoading = true;
    const lights = loader.querySelectorAll('.f1-light');
    let lightIndex = 0;

    // Turn lights red sequentially (0.2s interval)
    const lightInterval = setInterval(() => {
      if (lightIndex < lights.length) {
        lights[lightIndex].classList.add('active');
        lightIndex++;
      } else {
        clearInterval(lightInterval);
        
        // Wait 0.4s with all lights active, then extinguish them (loading finishes)
        setTimeout(() => {
          // Extinguish lights (green light flash/turn off)
          lights.forEach(l => l.classList.remove('active'));
          
          // Append new post cards
          loadNewPosts();
          
          isLoading = false;
        }, 400);
      }
    }, 200);
  }

  function loadNewPosts() {
    // Generate 2 new posts
    for (let i = 0; i < 2; i++) {
      postCounter++;
      const user = f1Users[Math.floor(Math.random() * f1Users.length)];
      const track = f1Tracks[Math.floor(Math.random() * f1Tracks.length)];
      const caption = f1Captions[Math.floor(Math.random() * f1Captions.length)];
      const speed = Math.floor(Math.random() * 55) + 290; // 290 - 345 km/h
      const rpm = Math.floor(Math.random() * 1000) + 11500;
      const gear = Math.floor(Math.random() * 3) + 6; // gears 6-8
      const temp = Math.floor(Math.random() * 15) + 85; // 85-100 degrees
      const drs = Math.random() > 0.5 ? 'ON' : 'OFF';
      const drsColor = drs === 'ON' ? '#00E676' : 'var(--text-secondary)';
      const likesCount = Math.floor(Math.random() * 50000) + 1000;

      const postHTML = `
        <div class="post-card" id="post-${postCounter}">
          <div class="post-header">
            <div class="post-user-info">
              <div class="post-user-avatar">${user.substring(0,2).toUpperCase()}</div>
              <a href="#" class="post-username">${user}</a>
            </div>
            <div class="post-badge">Telemetría Live</div>
          </div>
          
          <div class="post-media-container">
            <div class="f1-visual-bg" style="background: linear-gradient(135deg, ${track.startColor}, ${track.endColor})">
              <div class="visual-overlay"></div>
              <div class="visual-top-info">
                <span class="visual-lap-time">${track.lap}</span>
                <span style="font-family: var(--font-racing); color: var(--f1-red); font-weight:700; z-index:2; text-shadow:0 2px 4px rgba(0,0,0,0.8);">${track.name}</span>
              </div>
              
              <!-- Dynamic Circuit Path SVG -->
              <svg class="visual-circuit-map" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="${track.map}" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="${track.carX}" cy="${track.carY}" r="5" fill="#E10600" />
              </svg>

              <div class="visual-bottom-data">
                <div class="visual-speedometer">
                  <span class="speed-value">${speed}</span>
                  <span class="speed-unit">KM/H</span>
                  <span style="font-family: var(--font-racing); font-size:18px; font-weight:800; color:#FFD700; margin-left:15px; font-style:italic; text-shadow:0 2px 5px rgba(0,0,0,0.5);">MARCHA ${gear}</span>
                </div>
                <div class="visual-telemetry">
                  <div class="telemetry-item">DRS: <span class="telemetry-val" style="color: ${drsColor};">${drs}</span></div>
                  <div class="telemetry-item">RPM: <span class="telemetry-val">${formatNumber(rpm)}</span></div>
                  <div class="telemetry-item">TEMP: <span class="telemetry-val">${temp}°C</span></div>
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
            <div class="post-likes-count"><span class="likes-num">${formatNumber(likesCount)}</span> me gusta</div>
            <div class="post-caption">
              <a href="#" class="post-caption-user">${user}</a>
              <span>${caption}</span>
            </div>
            <div class="post-comments-list">
              <div class="comment-item">
                <a href="#" class="comment-user">race_engineer</a>
                <span>Gran sector 2, sigue empujando. 📻</span>
              </div>
            </div>
            <form class="comment-form">
              <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
              <button type="submit" class="comment-submit-btn">Publicar</button>
            </form>
          </div>
        </div>
      `;

      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = postHTML;
      feed.appendChild(tempDiv.firstElementChild);
    }
  }
}

/**
 * 4. Botón Añadir Foto en Footer - Modal Box Lógica
 */
function initAddPhotoModal() {
  const addBtn = document.getElementById('add-photo-btn');
  const modal = document.getElementById('add-post-modal');
  const closeBtn = document.getElementById('modal-close-btn');

  if (!addBtn || !modal || !closeBtn) return;

  // Open modal
  addBtn.addEventListener('click', (e) => {
    e.preventDefault();
    modal.style.display = 'flex';
    setTimeout(() => {
      modal.classList.add('show');
    }, 10);
  });

  // Close modal functions
  const closeModal = () => {
    modal.classList.remove('show');
    setTimeout(() => {
      if (!modal.classList.contains('show')) {
        modal.style.display = 'none';
      }
    }, 300);
  };

  closeBtn.addEventListener('click', closeModal);
  
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('show')) {
      closeModal();
    }
  });
}

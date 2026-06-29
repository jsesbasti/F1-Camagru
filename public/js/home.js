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

function escapeAttribute(str) {
  return escapeHTML(String(str));
}

/**
 * 3. Lógica de Scroll Infinito con Semáforo de F1
 */
function initInfiniteScroll() {
  const loader = document.getElementById('infinite-loader');
  const feed = document.querySelector('.feed-container');
  if (!loader || !feed) return;

  const endpoint = loader.dataset.endpoint || '/api/posts';
  const pageSize = Number(loader.dataset.pageSize || '4');
  let currentPage = Number(loader.dataset.nextPage || '2');
  let hasMorePosts = true;
  let isLoading = false;
  if (loader.dataset.hasMore === '0') {
    return;
  }

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

          loadMorePosts()
            .finally(() => {
              isLoading = false;
            });
        }, 400);
      }
    }, 200);
  }

  async function loadMorePosts() {
    if (!hasMorePosts) {
      disconnectLoader();
      return;
    }

    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('page', String(currentPage));
    url.searchParams.set('limit', String(pageSize));

    try {
      const response = await fetch(url.toString(), {
        headers: {
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      const posts = Array.isArray(data.posts) ? data.posts : [];

      if (!posts.length) {
        hasMorePosts = false;
        disconnectLoader();
        return;
      }

      posts.forEach((post) => {
        feed.appendChild(createPostCard(post));
      });

      currentPage += 1;
      hasMorePosts = Boolean(data.hasMore);

      if (!hasMorePosts) {
        disconnectLoader();
      }
    } catch (error) {
      console.error('Error loading posts:', error);
    }
  }

  function disconnectLoader() {
    observer.disconnect();
    loader.remove();
  }

  function createPostCard(post) {
    const card = document.createElement('div');
    card.className = 'post-card';
    card.id = `post-${post.id}`;

    const imageStyle = post.image_url
      ? `background-image: url('${escapeAttribute(post.image_url)}')`
      : '';

    const commentsMarkup = Array.isArray(post.comments)
      ? post.comments.map((comment) => `
        <div class="comment-item">
          <a href="#" class="comment-user">${escapeHTML(comment.username || '')}</a>
          <span>${escapeHTML(comment.comment || '')}</span>
        </div>
      `).join('')
      : '';

    card.innerHTML = `
      <div class="post-header">
        <div class="post-user-info">
          <div class="post-user-avatar">${escapeHTML(post.avatar_initials || '??')}</div>
          <a href="#" class="post-username">${escapeHTML(post.username || '')}</a>
        </div>
        <div class="post-badge">${escapeHTML(post.badge || 'POST')}</div>
      </div>

      <div class="post-media-container">
        <div class="f1-visual-bg" style="${imageStyle}">
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
        <div class="post-likes-count"><span class="likes-num">${formatNumber(Number(post.likes_count || 0))}</span> me gusta</div>
        <div class="post-caption">
          <a href="#" class="post-caption-user">${escapeHTML(post.username || '')}</a>
          <span>${escapeHTML(post.caption || 'Sin descripción')}</span>
        </div>
        <div class="post-comments-list">
          ${commentsMarkup}
        </div>
        <form class="comment-form">
          <input type="text" placeholder="Añadir un comentario..." class="comment-input" required autocomplete="off">
          <button type="submit" class="comment-submit-btn">Publicar</button>
        </form>
      </div>
    `;

    return card;
  }
}

/**
 * 4. Botón Añadir Foto en Footer - Modal Box Lógica
 */
function initAddPhotoModal() {
  const addBtn = document.getElementById('add-photo-btn');
  const modal = document.getElementById('add-post-modal');
  const closeBtn = document.getElementById('modal-close-btn');
  const authState = document.getElementById('add-photo-auth-state');
  const guestState = document.getElementById('add-photo-guest-state');
  const cameraBtn = document.getElementById('add-photo-camera-btn');
  const statusEl = document.getElementById('add-photo-status');

  if (!addBtn || !modal || !closeBtn || !authState || !guestState) return;

  const isAuthenticated = modal.dataset.authenticated === '1';

  async function showModalState() {
    if (isAuthenticated) {
      authState.hidden = false;
      guestState.hidden = true;
      if (statusEl) {
        statusEl.textContent = '';
      }

      // Check camera permission state (if supported) and update UI
      try {
        if (navigator.permissions && navigator.permissions.query) {
          const perm = await navigator.permissions.query({ name: 'camera' });

          if (perm && perm.state === 'granted') {
            // If granted, prefer redirect (defensive)
            window.location.href = '/photo';
            return;
          }

          if (perm && perm.state === 'denied') {
            if (statusEl) {
              statusEl.innerHTML = 'Acceso a la cámara denegado. Habilítalo en la configuración del sitio: <a href="https://developer.mozilla.org/es/docs/Web/API/MediaDevices/getUserMedia" target="_blank" rel="noopener noreferrer">cómo permitir cámara</a>';
            }
            if (cameraBtn) cameraBtn.disabled = true;
          } else {
            // 'prompt' or unknown
            if (statusEl) {
              statusEl.textContent = '';
            }
            if (cameraBtn) cameraBtn.disabled = false;
          }

          // React to permission changes while modal is open
          perm.onchange = () => {
            // Re-evaluate UI when permission state changes
            showModalState().catch(() => {});
          };
        }
      } catch (err) {
        // Permissions API may not be available; leave UI as default
      }
    } else {
      authState.hidden = true;
      guestState.hidden = false;
    }
  }

  // Open modal (but if authenticated and camera permission already granted, go straight to /photo)
  addBtn.addEventListener('click', async (e) => {
    e.preventDefault();

    if (isAuthenticated) {
      try {
        if (navigator.permissions && navigator.permissions.query) {
          const status = await navigator.permissions.query({ name: 'camera' });
          if (status && status.state === 'granted') {
            window.location.href = '/photo';
            return;
          }
        }
      } catch (err) {
        // permissions API may not be supported or may throw; fall through to showing modal
      }
    }

    showModalState();
    modal.style.display = 'flex';
    setTimeout(() => {
      modal.classList.add('show');
    }, 10);
  });

  if (cameraBtn) {
    cameraBtn.addEventListener('click', async () => {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        if (statusEl) {
          statusEl.textContent = 'Tu navegador no soporta acceso a la cámara.';
        }
        return;
      }

      cameraBtn.disabled = true;
      if (statusEl) {
        statusEl.textContent = 'Solicitando permiso de cámara...';
      }

      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        stream.getTracks().forEach((track) => track.stop());
        window.location.href = '/photo';
      } catch (error) {
        if (statusEl) {
          statusEl.textContent = 'Necesitas conceder permiso de cámara para continuar.';
        }
        cameraBtn.disabled = false;
      }
    });
  }

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

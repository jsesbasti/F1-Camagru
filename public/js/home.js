import { showAlert, showConfirm } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
  // Initialize elements
  initUserDropdown();
  initLikeAndCommentSystem();
  initPostMenu();
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
        <a href="${profileUrl(currentUsername)}" class="comment-user">${escapeHTML(currentUsername)}</a>
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
 * Helper to build a link to a user's profile (mirrors profile-url building
 * in app/Views/partials/post-card.php).
 */
function profileUrl(username) {
  return username ? `/profile?username=${encodeURIComponent(username)}` : '#';
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
 * 3. Menú de tres puntos del propio post: editar descripción o borrar
 * Delegación de eventos igual que el sistema de likes/comentarios, para
 * que funcione tanto en los posts renderizados por PHP como en los que
 * llegan por scroll infinito (createPostCard).
 */
function initPostMenu() {
  const feed = document.querySelector('.feed-container');
  if (!feed) return;

  feed.addEventListener('click', (e) => {
    const menuBtn = e.target.closest('.post-menu-btn');
    if (menuBtn) {
      e.preventDefault();
      e.stopPropagation();
      const dropdown = menuBtn.nextElementSibling;
      const wasShown = dropdown.classList.contains('show');
      closeAllPostMenus();
      if (!wasShown) dropdown.classList.add('show');
      return;
    }

    const editBtn = e.target.closest('.post-edit-btn');
    if (editBtn) {
      e.preventDefault();
      closeAllPostMenus();
      toggleEditMode(editBtn.closest('.post-card'), true);
      return;
    }

    const cancelBtn = e.target.closest('.post-edit-cancel-btn');
    if (cancelBtn) {
      e.preventDefault();
      toggleEditMode(cancelBtn.closest('.post-card'), false);
      return;
    }

    const deleteBtn = e.target.closest('.post-delete-btn');
    if (deleteBtn) {
      e.preventDefault();
      closeAllPostMenus();
      deletePost(deleteBtn.closest('.post-card'));
    }
  });

  feed.addEventListener('submit', (e) => {
    const form = e.target.closest('.post-edit-form');
    if (form) {
      e.preventDefault();
      saveEdit(form.closest('.post-card'), form);
    }
  });

  // Cerrar el desplegable al hacer click fuera o pulsar Escape
  document.addEventListener('click', closeAllPostMenus);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllPostMenus();
  });

  function closeAllPostMenus() {
    document.querySelectorAll('.post-menu-dropdown.show')
      .forEach((el) => el.classList.remove('show'));
  }

  function toggleEditMode(card, editing) {
    if (!card) return;
    const captionEl = card.querySelector('.post-caption');
    const form = card.querySelector('.post-edit-form');
    if (!captionEl || !form) return;

    if (editing) {
      const captionText = card.querySelector('.post-caption-text')?.textContent.trim() || '';
      const textarea = form.querySelector('.post-edit-textarea');
      textarea.value = captionText === 'Sin descripción' ? '' : captionText;
    }

    captionEl.hidden = editing;
    form.hidden = !editing;
    if (editing) {
      form.querySelector('.post-edit-textarea').focus();
    }
  }

  async function saveEdit(card, form) {
    if (!card) return;
    const id = Number(card.id.replace('post-', ''));
    const textarea = form.querySelector('.post-edit-textarea');
    const saveBtn = form.querySelector('.post-edit-save-btn');
    const description = textarea.value.trim();

    saveBtn.disabled = true;
    try {
      const response = await fetch('/api/posts/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, description }),
      });
      const data = await response.json();

      if (data.success) {
        card.querySelector('.post-caption-text').textContent = data.caption || 'Sin descripción';
        toggleEditMode(card, false);
      } else {
        await showAlert(Object.values(data.errors || {})[0] || 'No se pudo actualizar el post');
      }
    } catch (error) {
      await showAlert('No se pudo actualizar el post');
    } finally {
      saveBtn.disabled = false;
    }
  }

  async function deletePost(card) {
    if (!card) return;
    const confirmed = await showConfirm('¿Seguro que quieres borrar esta foto? Esta acción no se puede deshacer.', 'Borrar foto');
    if (!confirmed) return;

    const id = Number(card.id.replace('post-', ''));
    try {
      const response = await fetch('/api/posts/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });
      const data = await response.json();

      if (data.success) {
        card.remove();
        // On the single-post page (/post?id=...) there's nothing left to
        // show once its one card is gone — head back to the feed instead
        // of leaving an empty container.
        if (window.location.pathname === '/post') {
          window.location.href = '/';
        }
      } else {
        await showAlert(Object.values(data.errors || {})[0] || 'No se pudo borrar el post');
      }
    } catch (error) {
      await showAlert('No se pudo borrar el post');
    }
  }
}

/**
 * 4. Lógica de Scroll Infinito con Semáforo de F1
 */
function initInfiniteScroll() {
  const loader = document.getElementById('infinite-loader');
  const feed = document.querySelector('.feed-container');
  if (!loader || !feed) return;

  const currentUserId = Number(feed.dataset.currentUserId || 0);
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

  /**
   * Clones #post-card-template (rendered server-side by the same
   * partials/post-card.php that draws the initial feed) and fills it in
   * with this post's data, instead of duplicating the markup as a JS
   * string. Using textContent throughout means we don't need to
   * HTML-escape anything here either.
   */
  function createPostCard(post) {
    const template = document.getElementById('post-card-template');
    const card = template.content.firstElementChild.cloneNode(true);

    card.id = `post-${post.id}`;
    card.querySelector('.post-user-avatar').textContent = post.avatar_initials || '??';
    card.querySelector('.post-badge').textContent = post.badge || 'Post';
    card.querySelectorAll('.post-username, .post-caption-user').forEach((el) => {
      el.textContent = post.username || '';
      el.href = profileUrl(post.username);
    });

    if (post.image_url) {
      card.querySelector('.f1-visual-bg').style.backgroundImage = `url('${post.image_url}')`;
    }

    const menu = card.querySelector('.post-menu');
    if (menu) {
      menu.hidden = !(currentUserId > 0 && Number(post.user_id) === currentUserId);
    }

    card.querySelector('.likes-num').textContent = formatNumber(Number(post.likes_count || 0));
    const caption = post.caption || '';
    card.querySelector('.post-caption-text').textContent = caption || 'Sin descripción';
    card.querySelector('.post-edit-textarea').value = caption;

    const commentsList = card.querySelector('.post-comments-list');
    (Array.isArray(post.comments) ? post.comments : []).forEach((comment) => {
      const item = document.createElement('div');
      item.className = 'comment-item';

      const userLink = document.createElement('a');
      userLink.href = profileUrl(comment.username);
      userLink.className = 'comment-user';
      userLink.textContent = comment.username || '';

      const text = document.createElement('span');
      text.textContent = comment.comment || '';

      item.append(userLink, text);
      commentsList.appendChild(item);
    });

    return card;
  }
}

/**
 * 5. Botón Añadir Foto en Footer - Modal Box Lógica
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

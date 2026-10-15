import { showAlert, showConfirm } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
  // Initialize elements
  initLikeAndCommentSystem();
  initPostMenu();
  initInfiniteScroll();
});

/**
 * 2. Lógica de Interacciones de Post (Me gusta y Comentarios)
 * Usamos delegación de eventos en el contenedor del feed para optimizar el rendimiento
 */
function initLikeAndCommentSystem() {
  const feed = document.querySelector('.feed-container');
  if (!feed) return;

  feed.addEventListener('click', async (e) => {
    const likeBtn = e.target.closest('.like-btn');
    if (!likeBtn || likeBtn.disabled) return;
    e.preventDefault();

    const card = likeBtn.closest('.post-card');
    likeBtn.disabled = true;
    try {
      const data = await postInteraction('/api/posts/like', { id: postId(card) });
      if (data) {
        likeBtn.classList.toggle('liked', data.liked);
        likeBtn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
        card.querySelector('.likes-num').textContent = formatNumber(data.likes_count);
      }
    } finally {
      likeBtn.disabled = false;
    }
  });

  feed.addEventListener('click', (e) => {
    const replyBtn = e.target.closest('.comment-reply-btn');
    if (replyBtn) {
      e.preventDefault();
      const card = replyBtn.closest('.post-card');
      setReplyTarget(card, replyBtn.dataset.replyTo, replyBtn.dataset.username);
      const input = card.querySelector('.comment-input');
      input.value = `@${replyBtn.dataset.username} `;
      input.focus();
      return;
    }

    const cancelBtn = e.target.closest('.comment-reply-cancel');
    if (cancelBtn) {
      e.preventDefault();
      const card = cancelBtn.closest('.post-card');
      setReplyTarget(card, null);
      card.querySelector('.comment-input').value = '';
    }
  });

  feed.addEventListener('submit', async (e) => {
    const form = e.target.closest('.comment-form');
    if (!form) return;
    e.preventDefault();

    const input = form.querySelector('.comment-input');
    const submitBtn = form.querySelector('.comment-submit-btn');
    const commentText = input.value.trim();
    if (!commentText) return;

    const card = form.closest('.post-card');
    const body = { id: postId(card), comment: commentText };
    if (form.dataset.parentId) {
      body.parent_id = Number(form.dataset.parentId);
    }

    submitBtn.disabled = true;
    try {
      const data = await postInteraction('/api/posts/comment', body);
      if (data) {
        const commentsList = card.querySelector('.post-comments-list');
        const thread = data.comment.parent_id
          && commentsList.querySelector(`.comment-thread[data-comment-id="${data.comment.parent_id}"]`);

        if (thread) {
          thread.appendChild(createCommentItem(data.comment));
          thread.lastElementChild.scrollIntoView({ block: 'nearest' });
        } else {
          commentsList.appendChild(createCommentThread(data.comment));
          commentsList.scrollTop = commentsList.scrollHeight; // Auto-scroll to bottom of comments
        }
        input.value = '';
        setReplyTarget(card, null);
      }
    } finally {
      submitBtn.disabled = false;
    }
  });
}

function setReplyTarget(card, commentId, username = '') {
  const form = card.querySelector('.comment-form');
  const chip = card.querySelector('.comment-reply-chip');

  if (commentId) {
    form.dataset.parentId = commentId;
    chip.querySelector('.comment-reply-target').textContent = `@${username}`;
  } else {
    delete form.dataset.parentId;
  }
  chip.hidden = !commentId;
}

async function postInteraction(url, body) {
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const data = await response.json();

    if (data.success) return data;

    if (data.redirect) {
      window.location.href = data.redirect;
      return null;
    }
    await showAlert(Object.values(data.errors || {})[0] || 'Algo ha ido mal');
  } catch (error) {
    await showAlert('No se pudo conectar con el servidor');
  }
  return null;
}

function postId(card) {
  return Number(card.id.replace('post-', ''));
}

function createCommentThread(thread) {
  const wrapper = document.createElement('div');
  wrapper.className = 'comment-thread';
  wrapper.dataset.commentId = thread.id;

  wrapper.appendChild(createCommentItem(thread));
  (Array.isArray(thread.replies) ? thread.replies : []).forEach((reply) => {
    wrapper.appendChild(createCommentItem(reply));
  });
  return wrapper;
}

function createCommentItem(comment) {
  const item = document.createElement('div');
  item.className = comment.parent_id ? 'comment-item comment-reply' : 'comment-item';
  item.dataset.commentId = comment.id;

  const userLink = document.createElement('a');
  userLink.href = profileUrl(comment.username);
  userLink.className = 'comment-user';
  userLink.textContent = comment.username || '';

  const text = document.createElement('span');
  text.className = 'comment-text';
  text.append(...linkMentions(comment.comment || ''));

  const replyBtn = document.createElement('button');
  replyBtn.type = 'button';
  replyBtn.className = 'comment-reply-btn';
  replyBtn.dataset.replyTo = comment.id;
  replyBtn.dataset.username = comment.username || '';
  replyBtn.textContent = 'Responder';

  item.append(userLink, text, replyBtn);
  return item;
}

function linkMentions(text) {
  const nodes = [];
  let last = 0;

  for (const match of text.matchAll(/(?<![\w@])@(\w+)/g)) {
    nodes.push(document.createTextNode(text.slice(last, match.index)));

    const link = document.createElement('a');
    link.href = profileUrl(match[1]);
    link.className = 'comment-mention';
    link.textContent = match[0];
    nodes.push(link);

    last = match.index + match[0].length;
  }
  nodes.push(document.createTextNode(text.slice(last)));
  return nodes;
}

function setAvatar(el, avatarUrl, initials) {
  if (avatarUrl) {
    const img = document.createElement('img');
    img.src = avatarUrl;
    img.alt = '';
    img.className = 'h-full w-full object-cover';
    el.replaceChildren(img);
  } else {
    el.textContent = initials || '??';
  }
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
    setAvatar(card.querySelector('.post-user-avatar'), post.avatar_url, post.avatar_initials);
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
    const likeBtn = card.querySelector('.like-btn');
    likeBtn.classList.toggle('liked', Boolean(post.liked_by_me));
    likeBtn.setAttribute('aria-pressed', post.liked_by_me ? 'true' : 'false');
    const caption = post.caption || '';
    card.querySelector('.post-caption-text').textContent = caption || 'Sin descripción';
    card.querySelector('.post-edit-textarea').value = caption;

    const commentsList = card.querySelector('.post-comments-list');
    (Array.isArray(post.comments) ? post.comments : []).forEach((thread) => {
      commentsList.appendChild(createCommentThread(thread));
    });

    return card;
  }
}

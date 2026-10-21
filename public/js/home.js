import { showAlert, showConfirm } from './modal.js';

document.addEventListener('DOMContentLoaded', () => {
  // Initialize elements
  initLikeAndCommentSystem();
  initPostMenu();
  initFeedRefresh(initInfiniteScroll());
  initLiveUpdates();
  initCommentsModal();
  initCommentMore();
});

/**
 * 2. Lógica de Interacciones de Post (Me gusta y Comentarios)
 * Usamos delegación de eventos en el contenedor del feed para optimizar el rendimiento
 */
function initLikeAndCommentSystem() {
  const feed = document.querySelector('.feed-container');
  if (!feed) return;

  document.addEventListener('click', async (e) => {
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

  document.addEventListener('click', (e) => {
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

  document.addEventListener('submit', async (e) => {
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

        // The live poll may have already drawn it
        if (!commentsList.querySelector(`[data-comment-id="${data.comment.id}"]`)) {
          if (thread) {
            thread.appendChild(createCommentItem(data.comment));
            thread.lastElementChild.scrollIntoView({ block: 'nearest' });
          } else {
            commentsList.appendChild(createCommentThread(data.comment));
            commentsList.scrollTop = commentsList.scrollHeight; // Auto-scroll to bottom of comments
          }
          trimPreview(commentsList);
        }
        if (card.id === 'comments-modal-card') {
          appendComment(document.getElementById(`post-${body.id}`), data.comment);
        }
        const num = document.getElementById(`post-${body.id}`)?.querySelector('.comments-num');
        if (num) num.textContent = formatNumber(Number(num.textContent.replace(/\./g, '')) + 1);
        input.value = '';
        setReplyTarget(card, null);
      }
    } finally {
      submitBtn.disabled = false;
    }
  });
}

/**
 * Actualizaciones en tiempo real: cada minuto pregunta al servidor
 * por los likes de los posts que hay en pantalla y por los comentarios
 * nuevos desde el último que hemos visto. Se pausa con la pestaña oculta.
 */
function initLiveUpdates() {
  const feed = document.querySelector('.feed-container');
  if (!feed) return;

  const POLL_MS = 60 * 1000; // 1 minuto: suficiente sin cargar el servidor
  let since = null;
  let timer = null;
  let polling = false;

  async function poll() {
    const cards = feed.querySelectorAll('.post-card[id^="post-"]');
    const ids = Array.from(cards, postId).filter(Boolean);
    if (!ids.length || polling) return;

    const url = new URL('/api/posts/live', window.location.origin);
    url.searchParams.set('ids', ids.join(','));
    if (since !== null) url.searchParams.set('since', String(since));

    polling = true;
    try {
      const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) return;
      const data = await response.json();

      Object.entries(data.likes || {}).forEach(([id, state]) => {
        const card = document.getElementById(`post-${id}`);
        const likeBtn = card?.querySelector('.like-btn');
        if (!likeBtn || likeBtn.disabled) return; // a toggle of ours is in flight
        likeBtn.classList.toggle('liked', state.liked);
        likeBtn.setAttribute('aria-pressed', state.liked ? 'true' : 'false');
        card.querySelector('.likes-num').textContent = formatNumber(state.likes_count);
      });

      Object.entries(data.comment_counts || {}).forEach(([id, count]) => {
        const num = document.getElementById(`post-${id}`)?.querySelector('.comments-num');
        if (num) num.textContent = formatNumber(count);
      });

      (data.comments || []).forEach(appendLiveComment);
      since = Math.max(since ?? 0, Number(data.last_comment_id) || 0);
    } catch (error) {
      // Network blip: try again on the next tick
    } finally {
      polling = false;
    }
  }

  function appendLiveComment(comment) {
    appendComment(document.getElementById(`post-${comment.image_id}`), comment);
  }

  function start() {
    if (timer) return;
    poll();
    timer = setInterval(poll, POLL_MS);
  }

  function stop() {
    clearInterval(timer);
    timer = null;
  }

  document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
  if (!document.hidden) start();
}

function appendComment(card, comment) {
  const list = card?.querySelector('.post-comments-list');
  if (!list || list.querySelector(`[data-comment-id="${comment.id}"]`)) return;

  const atBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 8;
  if (comment.parent_id) {
    const thread = list.querySelector(`.comment-thread[data-comment-id="${comment.parent_id}"]`);
    if (!thread) return; // its thread isn't among the ones shown
    thread.appendChild(createCommentItem(comment));
  } else {
    list.appendChild(createCommentThread(comment));
  }
  trimPreview(list);
  if (atBottom) list.scrollTop = list.scrollHeight;
}

// Feed cards keep the same preview the server sends: last 3 threads, newest reply each.
function trimPreview(list) {
  if (!list.hasAttribute('data-preview')) return;
  const threads = list.querySelectorAll(':scope > .comment-thread');
  Array.from(threads).slice(0, -3).forEach((thread) => thread.remove());
  list.querySelectorAll(':scope > .comment-thread').forEach((thread) => {
    Array.from(thread.querySelectorAll(':scope > .comment-reply')).slice(0, -1).forEach((reply) => reply.remove());
  });
}

/**
 * Botón de comentarios: abre un modal con la imagen y todos sus comentarios.
 */
function initCommentsModal() {
  const modal = document.getElementById('comments-modal');
  if (!modal) return;
  const card = modal.querySelector('#comments-modal-card');

  document.addEventListener('click', async (e) => {
    const trigger = e.target.closest('.comment-trigger-btn');
    if (!trigger) return;
    e.preventDefault();

    const id = postId(trigger.closest('.post-card'));
    try {
      const response = await fetch(`/api/posts/details?id=${id}`, { headers: { 'Accept': 'application/json' } });
      const data = await response.json();
      if (!data.post) {
        await showAlert(Object.values(data.errors || {})[0] || 'No se pudo cargar el post');
        return;
      }
      fill(data.post);
    } catch (error) {
      await showAlert('No se pudo conectar con el servidor');
      return;
    }

    document.documentElement.style.overflow = 'hidden';
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
  });

  function fill(post) {
    card.dataset.postId = post.id;
    const image = card.querySelector('.comments-modal-image');
    const video = card.querySelector('.comments-modal-video');
    image.hidden = isVideo(post.image_url);
    video.hidden = !image.hidden;
    if (image.hidden) {
      video.src = post.image_url;
    } else {
      image.src = post.image_url;
      video.removeAttribute('src');
    }
    setAvatar(card.querySelector('.post-user-avatar'), post.avatar_url, post.avatar_initials);
    card.querySelectorAll('.post-username, .post-caption-user').forEach((el) => {
      el.textContent = post.username;
      el.href = profileUrl(post.username);
    });
    setCaption(card, post.caption);
    card.querySelector('.post-comments-list').replaceChildren(...post.comments.map(createCommentThread));
    card.querySelector('.comment-input').value = '';
    setReplyTarget(card, null);
  }

  function close() {
    document.documentElement.style.overflow = '';
    modal.classList.remove('show');
    setTimeout(() => {
      if (!modal.classList.contains('show')) modal.style.display = 'none';
    }, 300);
  }

  modal.querySelector('.comments-modal-close').addEventListener('click', close);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) close();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('show')) close();
  });
}

// Same rule as $commentPreview in partials/post-card.php.
function commentPreview(text) {
  if (text.length <= 150) return null;
  const cut = text.slice(0, 150);
  const space = cut.lastIndexOf(' ');
  return (space > 50 ? cut.slice(0, space) : cut).trimEnd() + '…';
}

function initCommentMore() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.comment-more-btn');
    if (!btn) return;
    const box = btn.parentElement;
    const short = box.querySelector('.comment-text, .post-caption-text');
    const full = box.querySelector('.comment-text-full, .post-caption-full');
    const expand = full.hidden;
    full.hidden = !expand;
    short.hidden = expand;
    btn.textContent = expand ? 'ver menos' : 'ver más';
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
  return Number(card.dataset.postId || card.id.replace('post-', ''));
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

  const full = comment.comment || '';
  const short = commentPreview(full);

  const text = document.createElement('span');
  text.className = 'comment-text';
  text.append(...linkMentions(short ?? full));

  const replyBtn = document.createElement('button');
  replyBtn.type = 'button';
  replyBtn.className = 'comment-reply-btn';
  replyBtn.dataset.replyTo = comment.id;
  replyBtn.dataset.username = comment.username || '';
  replyBtn.textContent = 'Responder';

  item.append(userLink, text);
  if (short) {
    const fullText = document.createElement('span');
    fullText.className = 'comment-text-full';
    fullText.hidden = true;
    fullText.append(...linkMentions(full));

    const moreBtn = document.createElement('button');
    moreBtn.type = 'button';
    moreBtn.className = 'comment-more-btn';
    moreBtn.textContent = 'ver más';
    item.append(fullText, ' ', moreBtn);
  }
  item.append(replyBtn);
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

// Caption with @mentions linked, mirroring $renderComment in post-card.php
function setCaption(card, caption) {
  const text = caption || '';
  const short = text ? commentPreview(text) : null;
  const box = card.querySelector('.post-caption');
  box.dataset.caption = text;
  box.querySelectorAll('.post-caption-full, .comment-more-btn').forEach((el) => el.remove());

  const shown = box.querySelector('.post-caption-text');
  shown.hidden = false;
  shown.replaceChildren(...(text ? linkMentions(short ?? text) : [document.createTextNode('Sin descripción')]));

  if (short) {
    const full = document.createElement('span');
    full.className = 'post-caption-full';
    full.hidden = true;
    full.append(...linkMentions(text));

    const moreBtn = document.createElement('button');
    moreBtn.type = 'button';
    moreBtn.className = 'comment-more-btn';
    moreBtn.textContent = 'ver más';
    box.append(full, ' ', moreBtn);
  }

  const textarea = card.querySelector('.post-edit-textarea');
  if (textarea) textarea.value = text;
}

// Same rule as Helper::isVideo(): animated posts are stored as MP4.
function isVideo(url) {
  return /\.mp4$/i.test(url || '');
}

function mediaVideo(url) {
  const video = document.createElement('video');
  video.className = 'media-video';
  video.src = url;
  video.muted = true;
  video.loop = true;
  video.autoplay = true;
  video.playsInline = true;
  return video;
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
      form.querySelector('.post-edit-textarea').value = captionEl.dataset.caption || '';
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
        setCaption(card, data.caption);
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
 * Devuelve { reset } para que el refresh manual pueda volver a empezar
 * desde la página 2 después de recargar la primera.
 */
function initInfiniteScroll() {
  const loader = document.getElementById('infinite-loader');
  const feed = document.querySelector('.feed-container');
  if (!loader || !feed) return null;

  const endpoint = loader.dataset.endpoint || '/api/posts';
  const pageSize = Number(loader.dataset.pageSize || '4');
  let currentPage = Number(loader.dataset.nextPage || '2');
  let hasMorePosts = loader.dataset.hasMore !== '0';
  let isLoading = false;
  let generation = 0; // bumped on reset so a load in flight is discarded

  // IntersectionObserver to watch the loader
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoading && hasMorePosts) {
      triggerF1LightsLoad();
    }
  }, {
    rootMargin: '100px' // Load when loader is 100px from viewport bottom
  });

  if (hasMorePosts) {
    observer.observe(loader);
  }

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
      stopLoader();
      return;
    }

    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('page', String(currentPage));
    url.searchParams.set('limit', String(pageSize));
    const requestGeneration = generation;

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
      if (requestGeneration !== generation) return; // the feed was refreshed meanwhile
      const posts = Array.isArray(data.posts) ? data.posts : [];

      if (!posts.length) {
        stopLoader();
        return;
      }

      posts.forEach((post) => {
        // Posts published since the last refresh shift the pages: skip repeats
        if (!document.getElementById(`post-${post.id}`)) {
          loader.before(createPostCard(post));
        }
      });

      currentPage += 1;
      if (!data.hasMore) {
        stopLoader();
      }
    } catch (error) {
      console.error('Error loading posts:', error);
    }
  }

  function stopLoader() {
    hasMorePosts = false;
    observer.unobserve(loader);
    loader.hidden = true;
  }

  return {
    reset(nextPage, hasMore) {
      generation += 1;
      currentPage = nextPage;
      hasMorePosts = hasMore;
      loader.hidden = !hasMore;
      observer.unobserve(loader);
      if (hasMore) {
        observer.observe(loader);
      }
    }
  };
}

/**
 * 5. Refresh manual: vuelve a pedir la primera página del feed y sustituye
 * los posts que hay en pantalla (posts nuevos, borrados, likes y comentarios
 * al día) sin recargar la página entera.
 */
function initFeedRefresh(infiniteScroll) {
  const button = document.getElementById('feed-refresh-btn');
  const feed = document.querySelector('.feed-container');
  const loader = document.getElementById('infinite-loader');
  if (!button || !feed || !loader || !infiniteScroll) return;

  const pageSize = Number(loader.dataset.pageSize || '4');

  button.addEventListener('click', async () => {
    button.disabled = true;
    try {
      const url = new URL(loader.dataset.endpoint || '/api/posts', window.location.origin);
      url.searchParams.set('page', '1');
      url.searchParams.set('limit', String(pageSize));

      const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      const posts = Array.isArray(data.posts) ? data.posts : [];

      feed.querySelectorAll('.post-card[id^="post-"], .feed-empty').forEach((el) => el.remove());
      if (posts.length) {
        posts.forEach((post) => loader.before(createPostCard(post)));
      } else {
        loader.before(createEmptyCard());
      }
      infiniteScroll.reset(2, Boolean(data.hasMore));
      button.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      await showAlert('No se pudo actualizar el feed');
    } finally {
      button.disabled = false;
    }
  });

  function createEmptyCard() {
    const card = document.createElement('div');
    card.className = 'post-card feed-empty';
    const inner = document.createElement('div');
    inner.className = 'p-6 text-center';
    inner.textContent = 'Track clear';
    card.appendChild(inner);
    return card;
  }
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
  const currentUserId = Number(document.querySelector('.feed-container')?.dataset.currentUserId || 0);

  card.id = `post-${post.id}`;
  setAvatar(card.querySelector('.post-user-avatar'), post.avatar_url, post.avatar_initials);
  card.querySelector('.post-badge').textContent = post.badge || 'Post';
  card.querySelectorAll('.post-username, .post-caption-user').forEach((el) => {
    el.textContent = post.username || '';
    el.href = profileUrl(post.username);
  });

  if (post.image_url) {
    const media = card.querySelector('.post-media');
    if (isVideo(post.image_url)) {
      const video = mediaVideo(post.image_url);
      video.className = 'post-media';
      media.replaceWith(video);
    } else {
      media.src = post.image_url;
    }
  }

  const menu = card.querySelector('.post-menu');
  if (menu) {
    menu.hidden = !(currentUserId > 0 && Number(post.user_id) === currentUserId);
  }

  card.querySelector('.likes-num').textContent = formatNumber(Number(post.likes_count || 0));
  card.querySelector('.comments-num').textContent = formatNumber(Number(post.comments_count || 0));
  const likeBtn = card.querySelector('.like-btn');
  likeBtn.classList.toggle('liked', Boolean(post.liked_by_me));
  likeBtn.setAttribute('aria-pressed', post.liked_by_me ? 'true' : 'false');
  setCaption(card, post.caption);

  const commentsList = card.querySelector('.post-comments-list');
  (Array.isArray(post.comments) ? post.comments : []).forEach((thread) => {
    commentsList.appendChild(createCommentThread(thread));
  });

  return card;
}

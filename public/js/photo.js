import { showConfirm } from './modal.js';

const PHOTO_SIZE = 720;

document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('camera-preview');
  const status = document.getElementById('camera-status');
  if (!video || !status) {
    return;
  }

  const editPreview = document.getElementById('edit-preview');
  const uploadInput = document.getElementById('upload-image');
  const captureButton = document.getElementById('capture-photo-btn');
  const enableCameraButton = document.getElementById('enable-camera-btn');
  const cameraActions = document.getElementById('camera-actions');
  const editActions = document.getElementById('edit-actions');
  const publishButton = document.getElementById('publish-photo-btn');
  const saveDraftButton = document.getElementById('save-draft-btn');
  const discardButton = document.getElementById('discard-btn');
  const editorError = document.getElementById('editor-error');
  const thumbnails = document.getElementById('thumbnails-container');
  const thumbnailsEmpty = document.getElementById('thumbnails-empty');
  const thumbTemplate = document.getElementById('studio-thumb-template');

  const modal = document.getElementById('publish-modal');
  const publishPreview = document.getElementById('publish-preview');
  const description = document.getElementById('publish-description');
  const publishConfirm = document.getElementById('publish-confirm-btn');
  const publishCancel = document.getElementById('publish-cancel-btn');

  let activeStream = null;
  let cameraReady = false;
  let selected = [];
  let activeId = null;
  let photo = null;

  const sizeControls = document.getElementById('overlay-controls');
  const sizeSlider = document.getElementById('overlay-size');
  const resetButton = document.getElementById('overlay-reset');

  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  function activeOverlay() {
    return selected.find((overlay) => overlay.id === activeId);
  }

  function renderOverlays() {
    document.querySelectorAll('.overlay-option').forEach((btn) => {
      btn.setAttribute('aria-pressed', selected.some((o) => o.id === btn.dataset.overlay) ? 'true' : 'false');
    });

    document.querySelectorAll('.overlay-layer').forEach((layer) => {
      const items = selected.map((overlay) => {
        let item = layer.querySelector(`.overlay-item[data-id="${overlay.id}"]`);
        if (!item) {
          item = document.createElement('div');
          item.className = 'overlay-item';
          item.dataset.id = overlay.id;
          const img = document.createElement('img');
          img.src = `/overlays/${overlay.id}.png`;
          img.alt = '';
          img.draggable = false;
          const handle = document.createElement('span');
          handle.className = 'overlay-handle';
          item.append(img, handle);
        }
        item.style.left = `${overlay.x * 100}%`;
        item.style.top = `${overlay.y * 100}%`;
        item.style.width = `${overlay.size * 100}%`;
        item.classList.toggle('active', overlay.id === activeId);
        return item;
      });
      layer.replaceChildren(...items);
    });

    const active = activeOverlay();
    sizeControls.hidden = !active;
    if (active) {
      sizeSlider.value = Math.round(active.size * 100);
    }

    captureButton.disabled = !cameraReady || selected.length === 0;
    publishConfirm.disabled = selected.length === 0;
  }

  function setOverlays(overlays) {
    selected = overlays;
    activeId = selected.length ? selected[selected.length - 1].id : null;
    renderOverlays();
  }

  document.querySelectorAll('.overlay-option').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.overlay;
      if (selected.some((o) => o.id === id)) {
        selected = selected.filter((o) => o.id !== id);
        if (activeId === id) {
          activeId = selected.length ? selected[selected.length - 1].id : null;
        }
      } else {
        selected = [...selected, { id, x: 0.5, y: 0.5, size: 1 }];
        activeId = id;
      }
      renderOverlays();
    });
  });

  sizeSlider.addEventListener('input', () => {
    const active = activeOverlay();
    if (active) {
      active.size = clamp(sizeSlider.value / 100, 0.1, 3);
      renderOverlays();
    }
  });

  resetButton.addEventListener('click', () => {
    const active = activeOverlay();
    if (active) {
      Object.assign(active, { x: 0.5, y: 0.5, size: 1 });
      renderOverlays();
    }
  });

  document.querySelectorAll('.overlay-layer').forEach((layer) => {
    layer.addEventListener('pointerdown', (e) => {
      const item = e.target.closest('.overlay-item');
      if (!item) {
        return;
      }
      e.preventDefault();

      const overlay = selected.find((o) => o.id === item.dataset.id);
      activeId = overlay.id;
      selected = [...selected.filter((o) => o !== overlay), overlay];

      const rect = layer.getBoundingClientRect();
      const resizing = e.target.classList.contains('overlay-handle');
      const start = { px: e.clientX, py: e.clientY, x: overlay.x, y: overlay.y, size: overlay.size };

      const move = (ev) => {
        const dx = (ev.clientX - start.px) / rect.width;
        const dy = (ev.clientY - start.py) / rect.height;
        if (resizing) {
          overlay.size = clamp(start.size + 2 * dx, 0.1, 3);
        } else {
          overlay.x = clamp(start.x + dx, -0.5, 1.5);
          overlay.y = clamp(start.y + dy, -0.5, 1.5);
        }
        renderOverlays();
      };
      const stop = () => {
        layer.removeEventListener('pointermove', move);
        layer.removeEventListener('pointerup', stop);
        layer.removeEventListener('pointercancel', stop);
      };

      layer.setPointerCapture(e.pointerId);
      layer.addEventListener('pointermove', move);
      layer.addEventListener('pointerup', stop);
      layer.addEventListener('pointercancel', stop);
      renderOverlays();
    });

    layer.addEventListener('wheel', (e) => {
      const item = e.target.closest('.overlay-item');
      if (!item) {
        return;
      }
      e.preventDefault();
      const overlay = selected.find((o) => o.id === item.dataset.id);
      activeId = overlay.id;
      overlay.size = clamp(overlay.size * (1 - e.deltaY * 0.001), 0.1, 3);
      renderOverlays();
    }, { passive: false });
  });

  function stopCamera() {
    if (activeStream) {
      activeStream.getTracks().forEach((track) => track.stop());
      activeStream = null;
    }
    cameraReady = false;
  }

  const CAMERA_ERRORS = {
    NotAllowedError: 'Camera blocked: allow it from the icon in the address bar',
    SecurityError: 'Camera blocked: allow it from the icon in the address bar',
    NotFoundError: 'No camera found',
    OverconstrainedError: 'No camera found',
    NotReadableError: 'The camera is being used by another app',
    AbortError: 'The camera is being used by another app',
  };

  function showEnableButton(show) {
    enableCameraButton.classList.toggle('hidden', !show);
    captureButton.classList.toggle('hidden', show);
  }

  async function startCamera() {
    if (!window.isSecureContext) {
      status.textContent = 'Camera needs https or localhost';
      renderOverlays();
      return;
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      status.textContent = window.isSecureContext
        ? 'Your browser does not support camera access'
        : 'Camera needs HTTPS: open the site with https:// (or upload an image)';
      renderOverlays();
      return;
    }

    status.textContent = 'Requesting camera access...';
    showEnableButton(false);

    try {
      stopCamera();
      try {
        activeStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
          audio: false
        });
      } catch (error) {
        if (error.name !== 'OverconstrainedError') throw error;
        activeStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      }
      video.srcObject = activeStream;
      // Brave and Edge can block autoplay even on a muted video
      await video.play().catch(() => {});
      cameraReady = true;
      status.textContent = 'Camera active';
    } catch (error) {
      console.error('Camera error:', error);
      stopCamera();
      status.textContent = CAMERA_ERRORS[error.name] || 'Could not access camera';
      showEnableButton(true);
    }
    renderOverlays();
  }

  // Edge and Brave hide (or silently drop) permission prompts that aren't
  // triggered by a click, so we only ask on page load when access was
  // already granted; otherwise we wait for the "Enable Camera" button.
  async function autoStartCamera() {
    let state = 'prompt';
    try {
      state = (await navigator.permissions.query({ name: 'camera' })).state;
    } catch (error) {
      // Firefox/Safari may not know the "camera" permission name: just try
      state = 'granted';
    }

    if (state === 'granted') {
      startCamera();
    } else {
      status.textContent = state === 'denied' ? CAMERA_ERRORS.NotAllowedError : 'Camera is off';
      showEnableButton(true);
      renderOverlays();
    }
  }

  enableCameraButton.addEventListener('click', startCamera);

  function toSquarePng(source, width, height, mirror) {
    const side = Math.min(width, height);
    const canvas = document.createElement('canvas');
    canvas.width = PHOTO_SIZE;
    canvas.height = PHOTO_SIZE;
    const ctx = canvas.getContext('2d');
    if (mirror) {
      ctx.translate(PHOTO_SIZE, 0);
      ctx.scale(-1, 1);
    }
    ctx.drawImage(source, (width - side) / 2, (height - side) / 2, side, side, 0, 0, PHOTO_SIZE, PHOTO_SIZE);
    return canvas.toDataURL('image/png');
  }

  function enterEdit(next) {
    photo = next;
    stopCamera();
    editPreview.src = next.src;
    video.classList.add('hidden');
    editPreview.classList.remove('hidden');
    cameraActions.classList.add('hidden');
    editActions.classList.replace('hidden', 'flex');
    status.textContent = next.draftId ? 'Editing draft' : 'Photo ready';
    editorError.textContent = '';
    markActiveThumb();
  }

  function enterCamera() {
    photo = null;
    editPreview.src = '';
    editPreview.classList.add('hidden');
    video.classList.remove('hidden');
    editActions.classList.replace('flex', 'hidden');
    cameraActions.classList.remove('hidden');
    editorError.textContent = '';
    markActiveThumb();
    autoStartCamera();
  }

  captureButton.addEventListener('click', () => {
    if (!video.videoWidth) {
      return;
    }
    enterEdit({ src: toSquarePng(video, video.videoWidth, video.videoHeight, true) });
  });

  uploadInput.addEventListener('change', () => {
    const file = uploadInput.files[0];
    uploadInput.value = '';
    if (!file) {
      return;
    }
    const img = new Image();
    img.onload = () => {
      enterEdit({ src: toSquarePng(img, img.naturalWidth, img.naturalHeight, false) });
      URL.revokeObjectURL(img.src);
    };
    img.onerror = () => {
      editorError.textContent = 'That file is not an image we can read';
    };
    img.src = URL.createObjectURL(file);
  });

  discardButton.addEventListener('click', enterCamera);

  async function send(url, fields) {
    const form = new FormData();
    if (photo.draftId) {
      form.append('draft_id', photo.draftId);
    } else {
      form.append('image', await (await fetch(photo.src)).blob(), 'photo.png');
    }
    form.append('overlays', JSON.stringify(selected));
    Object.entries(fields).forEach(([key, value]) => form.append(key, value));

    const response = await fetch(url, { method: 'POST', body: form });
    return response.json();
  }

  saveDraftButton.addEventListener('click', async () => {
    saveDraftButton.disabled = true;
    try {
      const data = await send('/photo/draft', {});
      if (data.success) {
        upsertThumb(data.photo);
        enterCamera();
        status.textContent = 'Draft saved';
      } else {
        editorError.textContent = Object.values(data.errors || {})[0] || 'Could not save draft';
      }
    } catch (error) {
      editorError.textContent = 'Could not save draft';
    } finally {
      saveDraftButton.disabled = false;
    }
  });

  function openModal() {
    publishPreview.src = photo.src;
    modal.querySelectorAll('[data-error]').forEach((el) => el.textContent = '');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    description.focus();
  }

  function closeModal() {
    modal.classList.remove('show');
    setTimeout(() => {
      if (!modal.classList.contains('show')) {
        modal.style.display = 'none';
      }
    }, 300);
  }

  publishButton.addEventListener('click', openModal);
  publishCancel.addEventListener('click', closeModal);
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

  publishConfirm.addEventListener('click', async () => {
    modal.querySelectorAll('[data-error]').forEach((el) => el.textContent = '');
    publishConfirm.disabled = true;
    try {
      const data = await send('/photo/publish', { description: description.value });
      if (data.success) {
        // Back to the feed, where the new post shows up first; the button
        // stays disabled so it can't be published twice while navigating
        stopCamera();
        window.location.href = '/';
        return;
      }
      Object.entries(data.errors || {}).forEach(([field, message]) => {
        const el = modal.querySelector(`[data-error="${field}"]`) || modal.querySelector('[data-error="error"]');
        el.textContent = message;
      });
    } catch (error) {
      modal.querySelector('[data-error="error"]').textContent = 'Could not publish photo';
    }
    renderOverlays();
  });

  function fillThumb(thumb, entry) {
    thumb.dataset.id = entry.id;
    thumb.dataset.draft = entry.is_draft ? '1' : '0';
    thumb.dataset.overlays = JSON.stringify(entry.overlays);
    thumb.dataset.src = entry.source_url;
    thumb.querySelector('img').src = entry.image_url;
    thumb.querySelector('.studio-thumb-open').setAttribute('aria-label', entry.is_draft ? 'Edit draft' : 'View post');
    thumb.querySelector('.studio-thumb-badge').hidden = !entry.is_draft;
    thumb.querySelector('.studio-thumb-delete').hidden = !entry.is_draft;
  }

  function upsertThumb(entry) {
    let thumb = thumbnails.querySelector(`.studio-thumb[data-id="${entry.id}"]`);
    if (!thumb) {
      thumb = thumbTemplate.content.firstElementChild.cloneNode(true);
      thumbnails.prepend(thumb);
    }
    fillThumb(thumb, entry);
    thumbnailsEmpty.hidden = true;
  }

  function markActiveThumb() {
    thumbnails.querySelectorAll('.studio-thumb').forEach((thumb) => {
      thumb.classList.toggle('active', !!photo && String(photo.draftId) === thumb.dataset.id);
    });
  }

  thumbnails.addEventListener('click', async (e) => {
    const thumb = e.target.closest('.studio-thumb');
    if (!thumb) {
      return;
    }

    if (e.target.closest('.studio-thumb-delete')) {
      await deleteDraft(thumb);
      return;
    }

    if (thumb.dataset.draft !== '1') {
      window.location.href = `/post?id=${thumb.dataset.id}`;
      return;
    }

    enterEdit({ src: thumb.dataset.src, draftId: thumb.dataset.id });
    setOverlays(JSON.parse(thumb.dataset.overlays || '[]'));
  });

  async function deleteDraft(thumb) {
    const confirmed = await showConfirm('Delete this draft? This cannot be undone.', 'Delete draft');
    if (!confirmed) {
      return;
    }

    try {
      const response = await fetch('/api/posts/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: Number(thumb.dataset.id) })
      });
      const data = await response.json();
      if (!data.success) {
        editorError.textContent = Object.values(data.errors || {})[0] || 'Could not delete draft';
        return;
      }
    } catch (error) {
      editorError.textContent = 'Could not delete draft';
      return;
    }

    if (photo && String(photo.draftId) === thumb.dataset.id) {
      enterCamera();
    }
    thumb.remove();
    thumbnailsEmpty.hidden = thumbnails.children.length > 0;
  }

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopCamera();
      renderOverlays();
    } else if (!photo) {
      autoStartCamera();
    }
  });

  window.addEventListener('beforeunload', stopCamera);

  renderOverlays();
  autoStartCamera();
});

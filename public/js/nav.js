document.addEventListener('DOMContentLoaded', () => {
  initUserDropdown();
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

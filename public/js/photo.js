document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('camera-preview');
  const status = document.getElementById('camera-status');
  const retryButton = document.getElementById('retry-camera-btn');
  const captureButton = document.getElementById('capture-photo-btn');
  const backButton = document.getElementById('feed-btn');

  if (!video || !status) {
    return;
  }

  let activeStream = null;

  async function startCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      status.textContent = 'Tu navegador no soporta cámara';
      if (retryButton) {
        retryButton.disabled = true;
      }
      if (captureButton) {
        captureButton.disabled = true;
      }
      return;
    }

    status.textContent = 'Solicitando cámara...';

    try {
      if (activeStream) {
        activeStream.getTracks().forEach((track) => track.stop());
      }

      activeStream = await navigator.mediaDevices.getUserMedia({
        video: { 
          facingMode: 'user',
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: false
      });

      video.srcObject = activeStream;
      status.textContent = 'Cámara activa';

      if (captureButton) {
        captureButton.disabled = false;
      }
      if (retryButton) {
        retryButton.disabled = false;
      }
    } catch (error) {
      console.error('Camera error:', error);
      status.textContent = 'No se pudo acceder a la cámara';
      if (retryButton) {
        retryButton.disabled = false;
      }
      if (captureButton) {
        captureButton.disabled = true;
      }
    }
  }

  if (retryButton) {
    retryButton.addEventListener('click', () => {
      startCamera();
    });
  }

  if (backButton) {
    backButton.addEventListener('click', () => {
      window.location.href = '/';
    });
  }

  if (captureButton) {
    captureButton.addEventListener('click', () => {
      status.textContent = 'Captura pendiente de implementar';
    });
  }

  document.addEventListener('visibilitychange', () => {
    if (document.hidden && activeStream) {
      activeStream.getTracks().forEach((track) => track.stop());
      activeStream = null;
    }
  });

  window.addEventListener('beforeunload', () => {
    if (activeStream) {
      activeStream.getTracks().forEach((track) => track.stop());
    }
  });

  startCamera();
});
document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('camera-preview');
  const status = document.getElementById('camera-status');
  const uploadInput = document.getElementById('upload-image');
  const captureButton = document.getElementById('capture-photo-btn');
  const backButton = document.getElementById('feed-btn');
  const publishButton = document.getElementById('publish-photo-btn');
  const cancelEditButton = document.getElementById('cancel-edit-btn');
  const cameraMode = document.getElementById('camera-mode');
  const editMode = document.getElementById('edit-mode');
  const editPreview = document.getElementById('edit-preview');

  if (!video || !status) {
    return;
  }

  let activeStream = null;
  let tempImageData = null;

  function switchToEditMode(imageSrc) {
    tempImageData = imageSrc;
    editPreview.src = imageSrc;
    cameraMode.classList.remove('active');
    editMode.classList.add('active');
    
    // Stop camera when switching to edit mode
    if (activeStream) {
      activeStream.getTracks().forEach((track) => track.stop());
      activeStream = null;
    }
  }

  function switchToCameraMode() {
    tempImageData = null;
    cameraMode.classList.add('active');
    editMode.classList.remove('active');
    startCamera();
  }

  async function startCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      status.textContent = 'Your browser does not support camera access';
      if (captureButton) {
        captureButton.disabled = true;
      }
      return;
    }

    status.textContent = 'Requesting camera access...';

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
      status.textContent = 'Camera active';

      if (captureButton) {
        captureButton.disabled = false;
      }
    } catch (error) {
      console.error('Camera error:', error);
      status.textContent = 'Could not access camera';
      if (captureButton) {
        captureButton.disabled = true;
      }
    }
  }

  // Handle image upload
  if (uploadInput) {
    uploadInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
          switchToEditMode(event.target.result);
        };
        reader.readAsDataURL(file);
      }
    });
  }

  if (captureButton) {
    captureButton.addEventListener('click', () => {
      const canvas = document.createElement('canvas');
      // Create a square canvas with the minimum of width or height
      const size = Math.min(video.videoWidth, video.videoHeight);
      canvas.width = size;
      canvas.height = size;
      const ctx = canvas.getContext('2d');
      // Calculate offset to center the crop
      const offsetX = (video.videoWidth - size) / 2;
      const offsetY = (video.videoHeight - size) / 2;
      // Scale and mirror for selfie camera
      ctx.scale(-1, 1);
      // Draw the cropped and centered portion
      ctx.drawImage(video, offsetX, offsetY, size, size, -size, 0, size, size);
      const imageSrc = canvas.toDataURL('image/jpeg');
      switchToEditMode(imageSrc);
    });
  }

  if (publishButton) {
    publishButton.addEventListener('click', async () => {
      if (!tempImageData) {
        return;
      }

      publishButton.disabled = true;

      try {
        const response = await fetch(tempImageData);
        const blob = await response.blob();
        const formData = new FormData();
        formData.append('image', blob, 'photo.jpg');

        const uploadResponse = await fetch('/photo/upload', {
          method: 'POST',
          body: formData
        });

        if (uploadResponse.ok) {
          window.location.href = '/';
          return;
        }

        status.textContent = 'Failed to publish image';
        publishButton.disabled = false;
      } catch (error) {
        console.error('Error publishing image:', error);
        status.textContent = 'Error publishing image';
        publishButton.disabled = false;
      }
    });
  }

  if (cancelEditButton) {
    cancelEditButton.addEventListener('click', () => {
      switchToCameraMode();
    });
  }

  if (backButton) {
    backButton.addEventListener('click', () => {
      window.location.href = '/';
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
/**
 * Notification utility to replace alert() with proper UI notifications
 * Uses the existing message system for consistency
 */

/**
 * Show notification message
 * @param {string} message - The message to display
 * @param {'success'|'error'|'warning'|'info'} type - Type of notification
 * @param {number} duration - Duration in milliseconds (default: 5000)
 */
export const notify = (message, type = 'info', duration = 5000) => {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `alert alert-${getAlertClass(type)} alert-dismissible fade show position-fixed`;
  notification.style.cssText = `
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 500px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  `;

  notification.innerHTML = `
    <div class="d-flex align-items-center">
      <i class="fas fa-${getIcon(type)} me-2"></i>
      <span class="flex-grow-1">${message}</span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;

  // Add to document
  document.body.appendChild(notification);

  // Auto-remove after duration
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, duration);

  return notification;
};

/**
 * Show success notification
 * @param {string} message - Success message
 * @param {number} duration - Duration in milliseconds
 */
export const notifySuccess = (message, duration = 4000) => {
  return notify(message, 'success', duration);
};

/**
 * Show error notification
 * @param {string} message - Error message
 * @param {number} duration - Duration in milliseconds
 */
export const notifyError = (message, duration = 6000) => {
  return notify(message, 'error', duration);
};

/**
 * Show warning notification
 * @param {string} message - Warning message
 * @param {number} duration - Duration in milliseconds
 */
export const notifyWarning = (message, duration = 5000) => {
  return notify(message, 'warning', duration);
};

/**
 * Show info notification
 * @param {string} message - Info message
 * @param {number} duration - Duration in milliseconds
 */
export const notifyInfo = (message, duration = 4000) => {
  return notify(message, 'info', duration);
};

/**
 * Show confirmation dialog
 * @param {string} message - Confirmation message
 * @param {Function} onConfirm - Callback when confirmed
 * @param {Function} onCancel - Callback when cancelled (optional)
 */
export const confirm = (message, onConfirm, onCancel = null) => {
  const modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.setAttribute('tabindex', '-1');
  modal.innerHTML = `
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-question-circle me-2 text-warning"></i>
            Konfirmasi
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">${message}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Batal
          </button>
          <button type="button" class="btn btn-primary confirm-btn">
            <i class="fas fa-check me-1"></i>Ya, Lanjutkan
          </button>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  // Initialize Bootstrap modal (check if bootstrap is available)
  const bootstrapModal = window.bootstrap ? new window.bootstrap.Modal(modal) : null;
  
  // Handle confirm button
  const confirmBtn = modal.querySelector('.confirm-btn');
  confirmBtn.addEventListener('click', () => {
    if (bootstrapModal) {
      bootstrapModal.hide();
    } else {
      modal.remove();
    }
    if (onConfirm) onConfirm();
  });

  // Handle modal hidden
  modal.addEventListener('hidden.bs.modal', () => {
    modal.remove();
    if (onCancel) onCancel();
  });

  if (bootstrapModal) {
    bootstrapModal.show();
  } else {
    // Fallback if bootstrap is not available
    modal.style.display = 'block';
    modal.classList.add('show');
  }
  
  return bootstrapModal;
};

// Helper functions
function getAlertClass(type) {
  const classMap = {
    success: 'success',
    error: 'danger',
    warning: 'warning',
    info: 'info'
  };
  return classMap[type] || 'info';
}

function getIcon(type) {
  const iconMap = {
    success: 'check-circle',
    error: 'exclamation-triangle',
    warning: 'exclamation-triangle',
    info: 'info-circle'
  };
  return iconMap[type] || 'info-circle';
}
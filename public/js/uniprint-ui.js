(function () {
  const UniPrintUI = {};

  function el(tag, attrs = {}, children = []) {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
      if (k === 'class') node.className = v;
      else if (k === 'text') node.textContent = v;
      else if (k === 'html') node.innerHTML = v;
      else if (k.startsWith('on') && typeof v === 'function') node.addEventListener(k.substring(2), v);
      else node.setAttribute(k, v);
    });
    for (const c of children) node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    return node;
  }

  let modalRoot;
  let toastRoot;

  function ensureRoots() {
    if (!modalRoot) {
      modalRoot = el('div', { class: 'up-modal-root', 'aria-live': 'polite' });
      document.body.appendChild(modalRoot);
    }
    if (!toastRoot) {
      toastRoot = el('div', { class: 'up-toast-root', 'aria-live': 'polite', 'aria-relevant': 'additions' });
      document.body.appendChild(toastRoot);
    }
  }

  function showModal({
    title = 'Notice',
    message = '',
    variant = 'info',
    confirmText = 'OK',
    cancelText = null,
    danger = false,
  }) {
    ensureRoots();

    return new Promise((resolve) => {
      const overlay = el('div', { class: 'up-modal-overlay' });
      const dialog = el('div', {
        class: 'up-modal-dialog',
        role: 'dialog',
        'aria-modal': 'true',
        'aria-label': title,
        tabindex: '-1',
      });

      const header = el('div', { class: 'up-modal-header' }, [
        el('div', { class: 'up-modal-title', text: title }),
      ]);

      const body = el('div', { class: 'up-modal-body' });
      const messageNode = el('div', { class: `up-modal-message up-modal-message--${variant}` });
      if (typeof message === 'string') {
        messageNode.textContent = message;
      } else if (message instanceof Node) {
        messageNode.appendChild(message);
      }
      body.appendChild(messageNode);

      const footer = el('div', { class: 'up-modal-footer' });
      const buttons = [];

      function cleanup(result) {
        window.removeEventListener('keydown', onKeydown, true);
        overlay.removeEventListener('click', onOverlayClick);
        modalRoot.removeChild(wrapper);
        resolve(result);
      }

      function onOverlayClick(e) {
        if (e.target === overlay) {
          if (cancelText) cleanup(false);
          else cleanup(true);
        }
      }

      function onKeydown(e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          if (cancelText) cleanup(false);
          else cleanup(true);
        }
        if (e.key === 'Enter') {
          if (!cancelText) {
            e.preventDefault();
            cleanup(true);
          }
        }
        if (e.key === 'Tab') {
          const focusables = dialog.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
          if (!focusables.length) return;
          const first = focusables[0];
          const last = focusables[focusables.length - 1];
          if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
          } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      }

      if (cancelText) {
        const cancelBtn = el('button', {
          class: 'up-btn up-btn-secondary',
          type: 'button',
          text: cancelText,
          onclick: () => cleanup(false),
        });
        buttons.push(cancelBtn);
      }

      const confirmBtn = el('button', {
        class: `up-btn ${danger ? 'up-btn-danger' : 'up-btn-primary'}`,
        type: 'button',
        text: confirmText,
        onclick: () => cleanup(true),
      });
      buttons.push(confirmBtn);

      buttons.forEach((b) => footer.appendChild(b));

      dialog.appendChild(header);
      dialog.appendChild(body);
      dialog.appendChild(footer);

      const wrapper = el('div', { class: 'up-modal-wrapper' }, [overlay, dialog]);
      modalRoot.appendChild(wrapper);

      overlay.addEventListener('click', onOverlayClick);
      window.addEventListener('keydown', onKeydown, true);

      // focus
      setTimeout(() => {
        (cancelText ? confirmBtn : confirmBtn).focus();
      }, 0);
    });
  }

  UniPrintUI.alert = function alert(message, options = {}) {
    return showModal({
      title: options.title || 'Notice',
      message,
      variant: options.variant || 'info',
      confirmText: options.confirmText || 'OK',
      cancelText: null,
      danger: options.danger || false,
    }).then(() => undefined);
  };

  UniPrintUI.confirm = function confirm(message, options = {}) {
    return showModal({
      title: options.title || 'Confirm',
      message,
      variant: options.variant || 'warning',
      confirmText: options.confirmText || 'Confirm',
      cancelText: options.cancelText || 'Cancel',
      danger: options.danger || false,
    });
  };

  UniPrintUI.toast = function toast(message, options = {}) {
    ensureRoots();

    const variant = options.variant || 'info';
    const timeout = typeof options.timeout === 'number' ? options.timeout : 4000;

    const node = el('div', { class: `up-toast up-toast--${variant}` });
    node.textContent = message;
    toastRoot.appendChild(node);

    setTimeout(() => {
      node.classList.add('up-toast--hide');
      setTimeout(() => node.remove(), 250);
    }, timeout);
  };

  window.UniPrintUI = UniPrintUI;

  document.addEventListener('DOMContentLoaded', function () {
    // reserved
  });
})();

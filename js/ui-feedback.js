// Reusable Bootstrap feedback helpers: toast notifications + confirm modal
(function () {
    'use strict';

    function ensureToastContainer() {
        let container = document.getElementById('appToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'appToastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1085';
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(message, type, title) {
        const toastContainer = ensureToastContainer();
        const toneMap = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };
        const iconMap = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        const bsTone = toneMap[type] || 'info';
        const toastTitle = title || (type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : type === 'success' ? 'Success' : 'Notice');

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center border-0 mb-2';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML =
            '<div class="toast-header">' +
            '  <i class="fa ' + iconMap[type] + ' text-' + bsTone + ' me-2"></i>' +
            '  <strong class="me-auto">' + escapeHtml(toastTitle) + '</strong>' +
            '  <small>just now</small>' +
            '  <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '<div class="toast-body">' + escapeHtml(message || '') + '</div>';

        toastContainer.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    function ensureConfirmModal() {
        let modalEl = document.getElementById('appConfirmModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'appConfirmModal';
            modalEl.className = 'modal fade';
            modalEl.tabIndex = -1;
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.innerHTML =
                '<div class="modal-dialog modal-dialog-centered">' +
                '  <div class="modal-content">' +
                '    <div class="modal-header">' +
                '      <h5 class="modal-title" id="appConfirmTitle">Confirm</h5>' +
                '      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '    </div>' +
                '    <div class="modal-body" id="appConfirmBody"></div>' +
                '    <div class="modal-footer">' +
                '      <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>' +
                '      <button type="button" class="btn btn-primary" data-confirm-ok>Confirm</button>' +
                '    </div>' +
                '  </div>' +
                '</div>';
            document.body.appendChild(modalEl);
        }
        return modalEl;
    }

    function showConfirm(message, callback, options) {
        const modalEl = ensureConfirmModal();
        const modalTitle = modalEl.querySelector('#appConfirmTitle');
        const modalBody = modalEl.querySelector('#appConfirmBody');
        const okBtn = modalEl.querySelector('[data-confirm-ok]');
        const cancelBtn = modalEl.querySelector('[data-confirm-cancel]');
        const settings = options || {};

        modalTitle.textContent = settings.title || 'Confirm Action';
        modalBody.textContent = message || 'Are you sure?';
        okBtn.textContent = settings.okText || 'Confirm';
        cancelBtn.textContent = settings.cancelText || 'Cancel';
        okBtn.className = settings.okClass || 'btn btn-primary';

        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: 'static',
            keyboard: false
        });

        return new Promise(function (resolve) {
            let settled = false;

            function cleanup(result) {
                if (settled) return;
                settled = true;
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                bsModal.hide();
                if (result && typeof callback === 'function') {
                    callback();
                }
                resolve(result);
            }

            function onOk() { cleanup(true); }
            function onCancel() { cleanup(false); }
            function onHidden() { cleanup(false); }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            modalEl.addEventListener('hidden.bs.modal', onHidden);

            bsModal.show();
        });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.showSuccess = function (message) { showToast(message, 'success', 'Success'); };
    window.showError = function (message) { showToast(message, 'error', 'Error'); };
    window.showWarning = function (message) { showToast(message, 'warning', 'Warning'); };
    window.showInfo = function (message) { showToast(message, 'info', 'Notice'); };
    window.showConfirm = showConfirm;
})();


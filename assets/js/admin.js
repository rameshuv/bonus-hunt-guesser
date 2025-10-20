(function () {
    'use strict';

    function initConfirmModal() {
        var modal = document.getElementById('bhg-confirm-modal');

        if (!modal) {
            return;
        }

        var messageEl = modal.querySelector('#bhg-confirm-modal-message');
        var confirmBtn = modal.querySelector('[data-bhg-confirm]');
        var dismissEls = modal.querySelectorAll('[data-bhg-dismiss]');
        var dialog = modal.querySelector('.bhg-modal__dialog');
        var activeForm = null;

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('hidden', 'hidden');

            if (dialog && dialog.hasAttribute('tabindex')) {
                dialog.removeAttribute('tabindex');
            }

            activeForm = null;
        }

        function openModal(form) {
            activeForm = form;
            var message = form.getAttribute('data-confirm-message') || '';

            if (messageEl) {
                messageEl.textContent = message;
            }

            modal.classList.add('is-open');
            modal.removeAttribute('hidden');

            if (dialog) {
                dialog.setAttribute('tabindex', '-1');
                if (typeof dialog.focus === 'function') {
                    dialog.focus();
                }
            }
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (activeForm) {
                    activeForm.dataset.bhgConfirmed = '1';
                    activeForm.submit();
                }

                closeModal();
            });
        }

        Array.prototype.forEach.call(dismissEls, function (button) {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if ('Escape' === event.key && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!form || !form.classList || !form.classList.contains('bhg-confirm-form')) {
                return;
            }

            if (form.dataset.bhgConfirmed === '1') {
                return;
            }

            event.preventDefault();
            openModal(form);
        });
    }

    document.addEventListener('DOMContentLoaded', initConfirmModal);
})();

(() => {
    'use strict';

    const sidebar = document.getElementById('donor-sidebar');
    const toggle = document.querySelector('[data-donor-menu-toggle]');
    const backdrop = document.querySelector('[data-donor-backdrop]');

    const closeSidebar = () => {
        if (!sidebar || !toggle || !backdrop) return;
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka menu dashboard');
        backdrop.classList.remove('is-visible');
        backdrop.hidden = true;
    };

    if (sidebar && toggle && backdrop) {
        toggle.addEventListener('click', () => {
            const opened = sidebar.classList.contains('is-open');
            if (opened) {
                closeSidebar();
                return;
            }
            sidebar.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Tutup menu dashboard');
            backdrop.hidden = false;
            backdrop.classList.add('is-visible');
            const firstLink = sidebar.querySelector('.donor-nav-link');
            if (firstLink) firstLink.focus();
        });
        backdrop.addEventListener('click', () => { closeSidebar(); toggle.focus(); });
        sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
        document.addEventListener('keydown', (event) => {
            if (!sidebar.classList.contains('is-open')) return;
            if (event.key === 'Escape') {
                closeSidebar();
                toggle.focus();
                return;
            }
            if (event.key === 'Tab') {
                const focusable = Array.from(sidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                    .filter((element) => element.offsetParent !== null);
                if (!focusable.length) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && (document.activeElement === first || !sidebar.contains(document.activeElement))) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !sidebar.contains(document.activeElement))) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    const dialog = document.querySelector('[data-receipt-dialog]');
    if (dialog) {
        const fields = ['id', 'date', 'recipient', 'category', 'description', 'amount', 'milestone'];
        let lastTrigger = null;

        document.querySelectorAll('[data-receipt-open]').forEach((button) => {
            button.addEventListener('click', () => {
                lastTrigger = button;
                fields.forEach((field) => {
                    const target = dialog.querySelector(`[data-receipt-value="${field}"]`);
                    if (target) target.textContent = button.dataset[`receipt${field.charAt(0).toUpperCase()}${field.slice(1)}`] || '—';
                });
                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', 'open');
                }
            });
        });

        dialog.querySelectorAll('[data-receipt-close]').forEach((button) => {
            button.addEventListener('click', () => {
                if (typeof dialog.close === 'function') dialog.close();
                else dialog.removeAttribute('open');
                if (lastTrigger) lastTrigger.focus();
            });
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                if (typeof dialog.close === 'function') dialog.close();
                else dialog.removeAttribute('open');
                if (lastTrigger) lastTrigger.focus();
            }
        });
        dialog.addEventListener('close', () => {
            if (lastTrigger) lastTrigger.focus();
        });
    }
})();

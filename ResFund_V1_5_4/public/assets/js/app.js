(() => {
    'use strict';

    const menuToggle = document.querySelector('[data-mobile-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (menuToggle && mobileMenu) {
        const closeMenu = () => {
            mobileMenu.classList.remove('is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.setAttribute('aria-label', 'Buka navigasi');
        };

        menuToggle.addEventListener('click', () => {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
            menuToggle.setAttribute('aria-label', isOpen ? 'Buka navigasi' : 'Tutup navigasi');
            mobileMenu.classList.toggle('is-open', !isOpen);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileMenu.classList.contains('is-open')) {
                closeMenu();
                menuToggle.focus();
            }
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.textContent = showPassword ? 'Sembunyikan' : 'Lihat';
            button.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            input.focus();
        });
    });

    const fillDemoButton = document.querySelector('[data-fill-demo]');
    const fillCredentials = (emailValue) => {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        if (email) email.value = emailValue;
        if (password) password.value = 'ResFund123!';
        if (email) email.focus();
    };
    if (fillDemoButton) fillDemoButton.addEventListener('click', () => fillCredentials('donatur@resfund.test'));
    const fillResearcherButton = document.querySelector('[data-fill-researcher]');
    if (fillResearcherButton) fillResearcherButton.addEventListener('click', () => fillCredentials('peneliti@resfund.test'));
    const fillAdminButton = document.querySelector('[data-fill-admin]');
    if (fillAdminButton) fillAdminButton.addEventListener('click', () => fillCredentials('admin@resfund.test'));

    const roleInputs = document.querySelectorAll('input[name="role"]');
    const researcherFields = document.querySelector('[data-researcher-fields]');

    if (roleInputs.length && researcherFields) {
        const researcherRequiredFields = researcherFields.querySelectorAll('[data-researcher-required]');

        const updateRoleFields = () => {
            const selectedRole = document.querySelector('input[name="role"]:checked')?.value || '';
            const isResearcher = selectedRole === 'peneliti';
            researcherFields.hidden = !isResearcher;
            researcherRequiredFields.forEach((field) => {
                field.required = isResearcher;
                field.disabled = !isResearcher;
            });
            roleInputs.forEach((input) => {
                const card = input.closest('.role-choice');
                if (card) card.classList.toggle('is-selected', input.checked);
            });
        };

        roleInputs.forEach((input) => input.addEventListener('change', updateRoleFields));
        updateRoleFields();
    }

    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => {
            const alert = button.closest('.flash-message');
            if (alert) alert.remove();
        });
    });
})();

// Pengisian cepat akun demo Reviewer pada halaman login.
document.querySelectorAll('[data-fill-reviewer]').forEach((button) => {
    button.addEventListener('click', () => {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        if (email) email.value = button.getAttribute('data-fill-reviewer') || '';
        if (password) password.value = 'ResFund123!';
        email?.focus();
    });
});

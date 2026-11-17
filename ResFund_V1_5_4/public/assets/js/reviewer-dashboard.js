document.addEventListener('DOMContentLoaded', () => {
    const decisionNotes = document.querySelector('[data-decision-notes]');
    const decisionInputs = document.querySelectorAll('input[name="decision"]');
    const decisionNotesInput = document.getElementById('decision-notes');

    const updateDecisionNotes = () => {
        if (!decisionNotes) return;
        const selected = document.querySelector('input[name="decision"]:checked')?.value || '';
        const needsNotes = selected === 'revision_minor' || selected === 'rejected';
        decisionNotes.hidden = !needsNotes;
        if (decisionNotesInput) {
            decisionNotesInput.required = needsNotes;
            decisionNotesInput.disabled = !needsNotes;
        }
    };
    decisionInputs.forEach((input) => input.addEventListener('change', updateDecisionNotes));
    updateDecisionNotes();

    const modal = document.querySelector('[data-evidence-modal]');
    const previewButtons = document.querySelectorAll('[data-document-preview]');
    let lastFocused = null;

    const closeModal = () => {
        if (!modal) return;
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        if (lastFocused instanceof HTMLElement) lastFocused.focus();
    };

    previewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!modal) return;
            lastFocused = button;
            modal.querySelector('[data-modal-title]').textContent = button.dataset.previewTitle || 'Dokumen pendukung';
            modal.querySelector('[data-modal-filename]').textContent = button.dataset.previewFile || '';
            modal.querySelector('[data-modal-preview-title]').textContent = button.dataset.previewTitle || 'Ringkasan dokumen';
            modal.querySelector('[data-modal-summary]').textContent = button.dataset.previewSummary || 'Tidak ada ringkasan dokumen.';
            modal.hidden = false;
            document.body.classList.add('modal-open');
            modal.querySelector('[data-modal-close]')?.focus();
        });
    });

    modal?.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', closeModal));
    modal?.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab' || modal.hidden) return;
        const focusable = Array.from(modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter((element) => !element.hasAttribute('hidden') && element.getAttribute('aria-hidden') !== 'true');
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && !modal.hidden) closeModal();
    });
});

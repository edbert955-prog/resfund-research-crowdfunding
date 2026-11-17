(() => {
  'use strict';

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const message = form.getAttribute('data-confirm');
    if (message && !window.confirm(message)) {
      event.preventDefault();
      return;
    }

    if (form.matches('[data-admin-assignment-form]')) {
      const selected = form.querySelectorAll('input[name="reviewer_ids[]"]:checked');
      if (selected.length !== 2) {
        event.preventDefault();
        window.alert('Pilih tepat dua reviewer untuk satu panel penilaian.');
      }
    }
  });
})();

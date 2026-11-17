(() => {
    const amountInput = document.querySelector('#amount');
    if (!amountInput) return;

    const summaryAmount = document.querySelector('#summary-amount');
    const summaryTotal = document.querySelector('#summary-total');
    const presets = [...document.querySelectorAll('[data-amount-preset]')];
    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
    }).format(Math.max(0, Number(value) || 0));

    const update = () => {
        const value = Number(amountInput.value) || 0;
        if (summaryAmount) summaryAmount.textContent = formatRupiah(value);
        if (summaryTotal) summaryTotal.textContent = formatRupiah(value);
        presets.forEach((button) => {
            const selected = Number(button.dataset.amountPreset) === value;
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    };

    presets.forEach((button) => button.addEventListener('click', () => {
        amountInput.value = button.dataset.amountPreset;
        amountInput.dispatchEvent(new Event('input', { bubbles: true }));
        amountInput.focus();
    }));
    amountInput.addEventListener('input', update);
    update();
})();

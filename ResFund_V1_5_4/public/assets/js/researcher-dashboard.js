(() => {
    'use strict';

    const builder = document.querySelector('[data-milestone-builder]');
    if (builder) {
        const template = document.getElementById('milestone-template');
        const addButton = document.querySelector('[data-add-milestone]');
        const totalLabel = document.querySelector('[data-allocation-total]');
        const feedback = document.querySelector('[data-allocation-feedback]');
        const totalPanel = document.querySelector('.researcher-allocation-total');
        const form = builder.closest('form');
        const minimum = 2;
        const maximum = 5;

        const updateRows = () => {
            const rows = Array.from(builder.querySelectorAll('[data-milestone-row]'));
            rows.forEach((row, index) => {
                row.querySelector('[data-milestone-number]').textContent = String(index + 1);
                row.querySelectorAll('[data-name]').forEach((input) => {
                    const field = input.dataset.name;
                    input.name = `milestones[${index}][${field}]`;
                    const id = `milestone-${field}-${index}`;
                    input.id = id;
                    const label = row.querySelector(`[data-label="${field}"]`);
                    if (label) label.htmlFor = id;
                });
                row.querySelector('[data-remove-milestone]').disabled = rows.length <= minimum;
            });
            if (addButton) addButton.disabled = rows.length >= maximum;
            updateAllocation();
        };

        const updateAllocation = () => {
            const values = Array.from(builder.querySelectorAll('[data-allocation]')).map((input) => Number.parseFloat(input.value) || 0);
            const total = values.reduce((sum, value) => sum + value, 0);
            const valid = Math.abs(total - 100) < 0.001;
            if (totalLabel) totalLabel.textContent = `${Number.isInteger(total) ? total : total.toFixed(2)}%`;
            if (feedback) feedback.textContent = valid ? 'Total alokasi sudah tepat 100%.' : `Total saat ini ${total.toFixed(2)}%. Sesuaikan hingga tepat 100%.`;
            if (totalPanel) {
                totalPanel.classList.toggle('is-valid', valid);
                totalPanel.classList.toggle('is-invalid', !valid);
            }
        };

        builder.addEventListener('input', (event) => {
            if (event.target.matches('[data-allocation]')) updateAllocation();
        });

        builder.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-milestone]');
            if (!removeButton) return;
            const rows = builder.querySelectorAll('[data-milestone-row]');
            if (rows.length <= minimum) return;
            removeButton.closest('[data-milestone-row]').remove();
            updateRows();
        });

        if (addButton && template) {
            addButton.addEventListener('click', () => {
                const count = builder.querySelectorAll('[data-milestone-row]').length;
                if (count >= maximum) return;
                builder.appendChild(template.content.cloneNode(true));
                updateRows();
                const lastInput = builder.querySelector('[data-milestone-row]:last-child input');
                if (lastInput) lastInput.focus();
            });
        }

        if (form) {
            form.addEventListener('submit', (event) => {
                const total = Array.from(builder.querySelectorAll('[data-allocation]')).reduce((sum, input) => sum + (Number.parseFloat(input.value) || 0), 0);
                if (Math.abs(total - 100) >= 0.001) {
                    event.preventDefault();
                    updateAllocation();
                    if (feedback) feedback.focus?.();
                    const firstAllocation = builder.querySelector('[data-allocation]');
                    if (firstAllocation) firstAllocation.focus();
                }
            });
        }
        updateRows();
    }

    const projectSelect = document.querySelector('[data-project-milestones]');
    const milestoneSelect = document.getElementById('expense-milestone');
    if (projectSelect && milestoneSelect) {
        const updateMilestones = () => {
            const slug = projectSelect.value;
            let visibleCount = 0;
            Array.from(milestoneSelect.options).forEach((option, index) => {
                if (index === 0) return;
                const visible = option.dataset.project === slug;
                option.hidden = !visible;
                if (!visible && option.selected) option.selected = false;
                if (visible) visibleCount += 1;
            });
            if (visibleCount === 0) milestoneSelect.value = '';
        };
        projectSelect.addEventListener('change', updateMilestones);
        updateMilestones();
    }
})();

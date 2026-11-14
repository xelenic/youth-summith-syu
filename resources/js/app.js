// Ask for confirmation on forms marked with data-confirm, and stop double submissions
// on buttons marked with data-busy-label.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();

        return;
    }

    const button = form.querySelector('[data-busy-label]');

    if (button) {
        button.dataset.idleLabel = button.textContent;
        button.textContent = button.dataset.busyLabel;
        button.disabled = true;
    }
});

// Re-enable the button if the visitor comes back with the browser's back button.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-busy-label][data-idle-label]').forEach((button) => {
        button.textContent = button.dataset.idleLabel;
        button.disabled = false;
    });
});

// The chevron beside a suggestions input opens its list.
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-open-picker]');

    if (!trigger) {
        return;
    }

    const input = document.getElementById(trigger.dataset.openPicker);

    input.focus();

    try {
        input.showPicker?.();
    } catch {
        // Some browsers cannot open a datalist programmatically; focusing is enough there.
    }
});

// Selects marked with data-auto-submit submit their form when changed.
document.addEventListener('change', (event) => {
    if (event.target.matches('select[data-auto-submit]')) {
        event.target.form.requestSubmit();
    }
});

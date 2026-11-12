const initializeCancellationForms = () => {
    document.querySelectorAll('[data-cancellation-form] [name="order_status"]').forEach((statusSelect) => {
        const form = statusSelect.closest('[data-cancellation-form]');
        if (form && statusSelect instanceof HTMLSelectElement) {
            form.dataset.previousOrderStatus = statusSelect.value;
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCancellationForms, { once: true });
} else {
    initializeCancellationForms();
}

document.addEventListener('change', (event) => {
    const statusSelect = event.target;
    if (!(statusSelect instanceof HTMLSelectElement) || statusSelect.name !== 'order_status') {
        return;
    }

    const form = statusSelect.closest('[data-cancellation-form]');
    if (!form) {
        return;
    }

    if (statusSelect.value === 'cancelled') {
        // Open the cancellation reason dialog immediately on select.
        const dialog = form.querySelector('[data-cancellation-dialog]');
        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
        }
    } else {
        // Track last non-cancel status so we can restore it on dismiss.
        form.dataset.previousOrderStatus = statusSelect.value;
    }
});

// Handle ESC key dismissing the dialog — reset select to previous value.
document.addEventListener('cancel', (event) => {
    const dialog = event.target;
    if (!(dialog instanceof HTMLDialogElement) || !dialog.matches('[data-cancellation-dialog]')) {
        return;
    }

    const form = dialog.closest('[data-cancellation-form]');
    if (form) {
        const statusSelect = form.querySelector('[name="order_status"]');
        if (statusSelect instanceof HTMLSelectElement && form.dataset.previousOrderStatus) {
            statusSelect.value = form.dataset.previousOrderStatus;
        }
    }
});

// Guard the submit event: only intercept when order_status is 'cancelled'
// and the cancellation hasn't yet been confirmed through the dialog.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-cancellation-form]')) {
        return;
    }

    const statusSelect = form.querySelector('[name="order_status"]');

    // Non-cancel status — allow the form to submit normally.
    if (statusSelect && statusSelect.value !== 'cancelled') {
        return;
    }

    // The dialog "Confirm cancellation" button already filled in the reason
    // and set this flag before calling requestSubmit() — let it through.
    if (form.dataset.cancellationConfirmed === 'true') {
        delete form.dataset.cancellationConfirmed;
        return;
    }

    // Cancelled was submitted without going through the dialog — stop it
    // and re-open the dialog so the admin must enter a reason.
    event.preventDefault();

    const dialog = form.querySelector('[data-cancellation-dialog]');
    if (dialog instanceof HTMLDialogElement) {
        dialog.showModal();
    }
});

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const button = event.target.closest('[data-confirm-cancellation], [data-dismiss-cancellation]');
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    const dialog = button.closest('[data-cancellation-dialog]');
    const form = button.closest('form[data-cancellation-form]');
    if (!dialog || !form) {
        return;
    }

    if (button.matches('[data-dismiss-cancellation]')) {
        // "Keep order" — reset select and close modal.
        const statusSelect = form.querySelector('[name="order_status"]');
        if (statusSelect instanceof HTMLSelectElement && form.dataset.previousOrderStatus) {
            statusSelect.value = form.dataset.previousOrderStatus;
        }
        dialog.close();
        return;
    }

    // "Confirm cancellation" — validate textarea, copy reason into hidden input, submit.
    const reasonTextarea = dialog.querySelector('[data-cancellation-reason]');
    const reasonInput = form.querySelector('[name="cancellation_reason"]');
    if (!(reasonTextarea instanceof HTMLTextAreaElement) || !(reasonInput instanceof HTMLInputElement)) {
        return;
    }

    const reason = reasonTextarea.value.trim();
    if (!reason) {
        reasonTextarea.setCustomValidity('Please provide a reason for cancelling this order.');
        reasonTextarea.reportValidity();
        return;
    }
    reasonTextarea.setCustomValidity('');

    reasonInput.value = reason;
    form.dataset.cancellationConfirmed = 'true';
    dialog.close();
    form.requestSubmit();
});

document.addEventListener('input', (event) => {
    if (event.target instanceof HTMLTextAreaElement && event.target.matches('[data-cancellation-reason]')) {
        event.target.setCustomValidity('');
    }
});

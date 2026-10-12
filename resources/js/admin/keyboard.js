document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') {
        return;
    }

    const field = event.target.closest('input, select, textarea, button');

    if (!field) {
        return;
    }

    if (field.closest('[data-skip-enter-next]')) {
        return;
    }

    if (field.matches('textarea, button, [type="submit"], [type="button"], [type="reset"]')) {
        return;
    }

    const form = field.closest('form');

    if (!form) {
        return;
    }

    const fields = Array.from(
        form.querySelectorAll('input, select, textarea, button')
    ).filter((item) => {
        if (item.disabled || item.readOnly || item.type === 'hidden') {
            return false;
        }

        if (item.matches('textarea, [type="submit"], [type="button"], [type="reset"]')) {
            return false;
        }

        const style = window.getComputedStyle(item);
        const rect = item.getBoundingClientRect();

        return style.display !== 'none'
            && style.visibility !== 'hidden'
            && rect.width > 0
            && rect.height > 0;
    });
    const index = fields.indexOf(field);
    const next = fields[index + 1];

    if (next) {
        event.preventDefault();
        next.focus();

        if (typeof next.select === 'function' && next.matches('input:not([type="search"])')) {
            next.select();
        }
    }
});

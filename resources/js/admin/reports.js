document.addEventListener('DOMContentLoaded', () => {
    const periodSelect = document.getElementById('reportPeriodSelect');

    if (!periodSelect) {
        return;
    }

    const dateField = document.querySelector('[data-report-date-field]');
    const customFields = document.querySelectorAll('[data-report-custom-field]');

    function syncDateFields() {
        const custom = periodSelect.value === 'custom';

        dateField?.toggleAttribute('hidden', custom);

        customFields.forEach((field) => {
            field.toggleAttribute('hidden', !custom);
        });
    }

    periodSelect.addEventListener('change', syncDateFields);
    syncDateFields();
});

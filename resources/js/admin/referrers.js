document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('referrerCrudPage');

    if (!page) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const addForm = document.getElementById('addReferrerForm');
    const editForm = document.getElementById('editReferrerForm');
    const tableBody = document.getElementById('referrerTableBody');
    const addSubmit = document.getElementById('addReferrerSubmitButton');
    const editSubmit = document.getElementById('editReferrerSubmitButton');
    const deleteSubmit = document.getElementById('confirmDeleteReferrerButton');

    let toastTimer;
    const codeCheckTimers = {};

    const routeUrl = (template, id) => template.replace('__ID__', id);

    function openModal(id) {
        const modal = document.getElementById(id);
        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('brand-modal-open');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.brand-modal.open')) {
            document.body.classList.remove('brand-modal-open');
        }
    }

    function clearErrors(form) {
        form?.querySelectorAll('.brand-field-error').forEach((item) => {
            item.textContent = '';
        });

        form?.querySelectorAll('input, select, textarea').forEach((item) => {
            item.classList.remove('brand-input-invalid');
        });
    }

    function displayErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const error = form.querySelector(`.${field}_error`);
            const input = form.querySelector(`[name="${field}"]`);

            if (error) {
                error.textContent = Array.isArray(messages)
                    ? messages[0]
                    : messages;
            }

            input?.classList.add('brand-input-invalid');
        });
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('referrerToast');
        const title = document.getElementById('referrerToastTitle');
        const body = document.getElementById('referrerToastMessage');
        const icon = document.getElementById('referrerToastIcon');

        toast.classList.toggle('error', type === 'error');
        title.textContent = type === 'error' ? 'Error' : 'Success';
        body.textContent = message;
        icon.textContent = type === 'error' ? '!' : '✓';
        toast.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    function setLoading(button, state, text) {
        if (!button) {
            return;
        }

        if (state) {
            button.dataset.originalText = button.textContent;
            button.textContent = text;
            button.disabled = true;
            return;
        }

        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
    }

    function sanitizeCode(value = '') {
        return value
            .replace(/[^a-zA-Z0-9_-]/g, '')
            .toUpperCase()
            .slice(0, 40);
    }

    function codeFromName(name = '') {
        const compactName = name
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9]+/g, ' ')
            .trim()
            .split(/\s+/)
            .join('');

        if (!compactName) {
            return '';
        }

        return sanitizeCode(`${compactName}100`);
    }

    function codeStatus(prefix) {
        return document.querySelector(`[data-code-status="${prefix}"]`);
    }

    function setCodeStatus(prefix, message = '', type = '') {
        const status = codeStatus(prefix);

        if (!status) {
            return;
        }

        status.textContent = message;
        status.classList.remove('checking', 'success', 'error');

        if (type) {
            status.classList.add(type);
        }
    }

    function checkCodeAvailability(prefix) {
        const input = document.getElementById(`${prefix}_code`);
        const code = sanitizeCode(input?.value || '');

        if (!input || !page.dataset.checkCodeUrl) {
            return;
        }

        input.value = code;
        clearTimeout(codeCheckTimers[prefix]);

        if (!code) {
            setCodeStatus(prefix);
            return;
        }

        setCodeStatus(prefix, 'Checking...', 'checking');

        codeCheckTimers[prefix] = setTimeout(async () => {
            const params = new URLSearchParams({ code });
            const editId = document.getElementById('edit_referrer_id')?.value;

            if (prefix === 'edit' && editId) {
                params.set('ignore_id', editId);
            }

            try {
                const response = await fetch(`${page.dataset.checkCodeUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await parseResponse(response);
                setCodeStatus(
                    prefix,
                    data.message,
                    data.available ? 'success' : 'error'
                );
            } catch (error) {
                setCodeStatus(prefix, 'Unable to check code now', 'error');
            }
        }, 280);
    }

    function resetCodeAutomation(prefix, isManual = false) {
        const code = document.getElementById(`${prefix}_code`);

        if (code) {
            code.dataset.manuallyEdited = isManual ? '1' : '0';
        }

        setCodeStatus(prefix);
    }

    async function parseResponse(response) {
        const data = await response.json().catch(() => ({
            message: 'Invalid server response.',
        }));

        if (!response.ok) {
            throw { status: response.status, data };
        }

        return data;
    }

    function escapeHtml(value = '') {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function money(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function percent(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function createRow(referrer) {
        const statusClass = referrer.is_active ? 'active' : 'inactive';
        const memberLabel = referrer.member_email
            ? escapeHtml(referrer.member_email)
            : 'Not linked';

        return `
            <tr id="referrerRow${referrer.id}">
                <td><span class="brand-id">#${referrer.id}</span></td>
                <td>
                    <div class="brand-name-cell">
                        <div class="brand-table-logo">
                            <span>${escapeHtml(referrer.name.charAt(0).toUpperCase())}</span>
                        </div>
                        <div>
                            <strong>${escapeHtml(referrer.name)}</strong>
                            <small>${memberLabel}</small>
                        </div>
                    </div>
                </td>
                <td><code class="brand-slug">${escapeHtml(referrer.code)}</code></td>
                <td>${percent(referrer.commission_rate)}%</td>
                <td>${referrer.successful_referrals}</td>
                <td><strong>৳${money(referrer.balance)}</strong></td>
                <td><strong>৳${money(referrer.gift_balance)}</strong></td>
                <td><strong>৳${money(referrer.total_balance)}</strong></td>
                <td><span class="brand-status-badge ${statusClass}">${escapeHtml(referrer.status_label)}</span></td>
                <td>
                    <div class="brand-table-actions">
                        <button type="button" class="brand-action-button edit editReferrerButton" data-id="${referrer.id}">Edit</button>
                        <button type="button" class="brand-action-button delete deleteReferrerButton" data-id="${referrer.id}" data-name="${escapeHtml(referrer.name)}">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncRow(referrer) {
        const existing = document.getElementById(`referrerRow${referrer.id}`);
        const html = createRow(referrer);

        if (existing) {
            existing.outerHTML = html;
            return;
        }

        document.getElementById('emptyReferrerRow')?.remove();
        tableBody?.insertAdjacentHTML('afterbegin', html);
    }

    function populateEditForm(referrer) {
        [
            'member_id',
            'name',
            'code',
            'commission_rate',
            'gift_balance',
        ].forEach((field) => {
            const input = document.getElementById(`edit_${field}`);

            if (input) {
                input.value = referrer[field] ?? '';
            }
        });

        document.getElementById('edit_is_active').value =
            referrer.is_active ? '1' : '0';

        document.getElementById('edit_referrer_id').value = referrer.id;
        resetCodeAutomation('edit', true);
        checkCodeAvailability('edit');
    }

    document
        .getElementById('openAddReferrerModal')
        ?.addEventListener('click', () => {
            addForm?.reset();
            clearErrors(addForm);
            resetCodeAutomation('add');
            document.getElementById('add_commission_rate').value = '1';
            document.getElementById('add_gift_balance').value = '0';
            document.getElementById('add_is_active').value = '1';
            openModal('addReferrerModal');
        });

    document
        .querySelectorAll('[data-close-modal]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(button.dataset.closeModal);
            });
        });

    addForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(addForm);
        setLoading(addSubmit, true, 'Adding...');

        try {
            const response = await fetch(page.dataset.storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(addForm),
            });

            const data = await parseResponse(response);
            syncRow(data.referrer);
            closeModal('addReferrerModal');
            addForm.reset();
            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(addForm, error.data.errors || {});
            } else {
                showToast(error.data?.message || 'Unable to add referrer.', 'error');
            }
        } finally {
            setLoading(addSubmit, false);
        }
    });

    tableBody?.addEventListener('click', async (event) => {
        const editButton = event.target.closest('.editReferrerButton');
        const deleteButton = event.target.closest('.deleteReferrerButton');

        if (editButton) {
            try {
                editButton.disabled = true;
                const response = await fetch(
                    routeUrl(page.dataset.showUrl, editButton.dataset.id),
                    { headers: { 'Accept': 'application/json' } }
                );

                const data = await parseResponse(response);
                editForm?.reset();
                clearErrors(editForm);
                populateEditForm(data.referrer);
                openModal('editReferrerModal');
            } catch (error) {
                showToast(error.data?.message || 'Unable to load referrer.', 'error');
            } finally {
                editButton.disabled = false;
            }
        }

        if (deleteButton) {
            document.getElementById('delete_referrer_id').value =
                deleteButton.dataset.id;
            document.getElementById('deleteReferrerName').textContent =
                deleteButton.dataset.name;
            openModal('deleteReferrerModal');
        }
    });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(editForm);

        const id = document.getElementById('edit_referrer_id').value;
        setLoading(editSubmit, true, 'Updating...');

        try {
            const response = await fetch(routeUrl(page.dataset.updateUrl, id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(editForm),
            });

            const data = await parseResponse(response);
            syncRow(data.referrer);
            closeModal('editReferrerModal');
            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(editForm, error.data.errors || {});
            } else {
                showToast(error.data?.message || 'Unable to update referrer.', 'error');
            }
        } finally {
            setLoading(editSubmit, false);
        }
    });

    deleteSubmit?.addEventListener('click', async () => {
        const id = document.getElementById('delete_referrer_id').value;
        setLoading(deleteSubmit, true, 'Deleting...');

        try {
            const response = await fetch(routeUrl(page.dataset.deleteUrl, id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await parseResponse(response);
            document.getElementById(`referrerRow${id}`)?.remove();
            closeModal('deleteReferrerModal');
            showToast(data.message);
        } catch (error) {
            showToast(error.data?.message || 'Unable to delete referrer.', 'error');
        } finally {
            setLoading(deleteSubmit, false);
        }
    });

    ['add', 'edit'].forEach((prefix) => {
        const name = document.getElementById(`${prefix}_name`);
        const code = document.getElementById(`${prefix}_code`);
        const regenerate = document.querySelector(`[data-regenerate-code="${prefix}"]`);

        name?.addEventListener('input', () => {
            if (!code || code.dataset.manuallyEdited === '1') {
                return;
            }

            code.value = codeFromName(name.value);
            checkCodeAvailability(prefix);
        });

        regenerate?.addEventListener('click', () => {
            if (!code) {
                return;
            }

            code.value = codeFromName(name?.value || '');
            resetCodeAutomation(prefix);
            checkCodeAvailability(prefix);
        });

        code?.addEventListener('input', () => {
            code.dataset.manuallyEdited = '1';
            code.value = sanitizeCode(code.value);
            checkCodeAvailability(prefix);
        });
    });

    document
        .getElementById('closeReferrerToast')
        ?.addEventListener('click', () => {
            document.getElementById('referrerToast')?.classList.remove('show');
        });
});

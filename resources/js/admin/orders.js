document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('orderCrudPage');

    if (!page) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const addForm = document.getElementById('addOrderForm');
    const editForm = document.getElementById('editOrderForm');
    const addSubmit = document.getElementById('addOrderSubmitButton');
    const editSubmit = document.getElementById('editOrderSubmitButton');
    const deleteSubmit = document.getElementById('confirmDeleteOrderButton');
    const sendSubmit = document.getElementById('confirmSendSteadfastButton');
    const qcIssueSubmit = document.getElementById('confirmQcIssueButton');
    const resolveQcSubmit = document.getElementById('confirmResolveQcButton');
    const itemTemplate = document.getElementById('orderItemTemplate');
    let toastTimer = null;

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

    async function parseResponse(response) {
        const data = await response.json().catch(() => ({
            message: 'Invalid server response.',
        }));

        if (!response.ok) {
            throw {
                status: response.status,
                data,
            };
        }

        return data;
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('orderToast');
        const title = document.getElementById('orderToastTitle');
        const body = document.getElementById('orderToastMessage');
        const icon = document.getElementById('orderToastIcon');

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

    function escapeHtml(value = '') {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function money(value) {
        return `৳${Number(value || 0).toFixed(2)}`;
    }

    function orderStatusClass(status) {
        return {
            Pending: 'pending',
            Confirmed: 'confirmed',
            Cancelled: 'cancelled',
            Delivered: 'delivered',
        }[status] || 'pending';
    }

    function qcStatusClass(status) {
        return {
            passed: 'active',
            issue: 'cancelled',
            not_checked: 'pending',
        }[status] || 'pending';
    }

    function qcStatusLabel(status) {
        return {
            passed: 'QC Passed',
            issue: 'QC Issue',
            not_checked: 'QC Not Checked',
        }[status] || 'QC Not Checked';
    }

    function actionLabel(action) {
        return {
            order_confirmed: 'Order Confirmed',
            order_updated: 'Order Updated',
            qc_passed: 'QC Passed',
            qc_issue: 'QC Issue',
            qc_resolved: 'QC Resolved',
            sent_steadfast: 'Sent to Steadfast',
            order_cancelled: 'Order Cancelled',
        }[action] || String(action || 'Activity').replaceAll('_', ' ');
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
            const message = Array.isArray(messages) ? messages[0] : messages;

            if (field.startsWith('items')) {
                form.querySelector('.items_error').textContent = message;
                return;
            }

            const errorElement = form.querySelector(`.${field}_error`);
            const input = form.querySelector(`[name="${field}"]`);

            if (errorElement) {
                errorElement.textContent = message;
            }

            input?.classList.add('brand-input-invalid');
        });
    }

    function itemList(prefix) {
        return document.getElementById(`${prefix}_order_items`);
    }

    function addItemRow(prefix, item = {}) {
        const list = itemList(prefix);

        if (!list || !itemTemplate) {
            return;
        }

        const fragment = itemTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-order-item]');

        row.querySelector('[data-order-product]').value = item.product_id || '';
        row.querySelector('[data-order-color]').value = item.color || '';
        row.querySelector('[data-order-size]').value = item.size || '';
        row.querySelector('[data-order-quantity]').value = item.quantity || 1;
        row.querySelector('[data-order-unit-price]').value = item.unit_price || 0;

        list.appendChild(fragment);
    }

    function resetItemRows(prefix) {
        const list = itemList(prefix);
        if (list) {
            list.innerHTML = '';
        }
        addItemRow(prefix);
    }

    function readItemRows(prefix) {
        return Array.from(
            itemList(prefix)?.querySelectorAll('[data-order-item]') || []
        )
            .map((row) => ({
                product_id: row.querySelector('[data-order-product]')?.value || '',
                color: row.querySelector('[data-order-color]')?.value || '',
                size: row.querySelector('[data-order-size]')?.value || '',
                quantity: row.querySelector('[data-order-quantity]')?.value || '1',
                unit_price: row.querySelector('[data-order-unit-price]')?.value || '0',
            }))
            .filter((item) => item.product_id);
    }

    function buildFormData(form, prefix) {
        const data = new FormData(form);

        Array.from(data.keys()).forEach((key) => {
            if (key.startsWith('items[')) {
                data.delete(key);
            }
        });

        readItemRows(prefix).forEach((item, index) => {
            Object.entries(item).forEach(([field, value]) => {
                data.append(`items[${index}][${field}]`, value);
            });
        });

        return data;
    }

    function syncAddress(prefix) {
        const house = document.getElementById(`${prefix}_house_no`)?.value.trim();
        const road = document.getElementById(`${prefix}_road_no`)?.value.trim();
        const area = document.getElementById(`${prefix}_area_thana`)?.value.trim();
        const district = document.getElementById(`${prefix}_district`)?.value.trim();
        const output = document.getElementById(`${prefix}_full_address`);
        const parts = [];

        if (house) parts.push(`House- ${house}`);
        if (road) parts.push(`Road- ${road}`);
        if (area) parts.push(area);
        if (district) parts.push(district);
        if (output) output.value = parts.join(', ');
    }

    function handleDeliveryArea(prefix) {
        const deliveryArea = document.getElementById(`${prefix}_delivery_area`)?.value;
        const district = document.getElementById(`${prefix}_district`);

        if (deliveryArea === 'inside_dhaka' && district) {
            district.value = 'Dhaka';
            district.readOnly = true;
        }

        if (deliveryArea === 'outside_dhaka' && district) {
            if (district.value === 'Dhaka') {
                district.value = '';
            }
            district.readOnly = false;
        }

        syncAddress(prefix);
    }

    function fillForm(prefix, order) {
        [
            'brand_id',
            'customer_name',
            'phone',
            'alternative_phone',
            'customer_email',
            'delivery_area',
            'district',
            'area_thana',
            'road_no',
            'house_no',
            'full_address',
            'order_note',
            'status',
            'payment_status',
        ].forEach((field) => {
            const input = document.getElementById(`${prefix}_${field}`);
            if (input) {
                input.value = order[field] ?? '';
            }
        });

        const idInput = document.getElementById('edit_order_id');
        if (idInput) {
            idInput.value = order.id;
        }

        const list = itemList(prefix);
        if (list) {
            list.innerHTML = '';
        }

        (order.items || []).forEach((item) => addItemRow(prefix, item));

        if (!(order.items || []).length) {
            addItemRow(prefix);
        }

        handleDeliveryArea(prefix);

        if (prefix === 'edit') {
            fillEditQcPanel(order);
        }
    }

    function fillEditQcPanel(order) {
        const qcStatus = order.qc_status || 'not_checked';
        const statusLabel = document.getElementById('edit_qc_status_label');
        const qcBy = document.getElementById('edit_qc_by');
        const qcCheckedAt = document.getElementById('edit_qc_checked_at');
        const issueWrap = document.getElementById('edit_qc_issue_note_wrap');
        const issueNote = document.getElementById('edit_qc_issue_note');
        const helpText = document.getElementById('edit_qc_help_text');
        const passedButton = document.getElementById('editQcPassedButton');
        const issueButton = document.getElementById('editQcIssueButton');
        const resolveButton = document.getElementById('editResolveQcButton');
        const canQc = order.status === 'Confirmed' && !order.sent_to_steadfast_at;

        if (statusLabel) {
            statusLabel.textContent = order.qc_status_label || qcStatusLabel(qcStatus);
            statusLabel.className = `brand-status-badge ${order.qc_status_class || qcStatusClass(qcStatus)}`;
        }

        if (qcBy) qcBy.textContent = order.qc_by || '-';
        if (qcCheckedAt) qcCheckedAt.textContent = order.qc_checked_at || '-';
        if (issueNote) issueNote.textContent = order.qc_issue_note || '-';
        issueWrap?.classList.toggle('show', Boolean(order.qc_issue_note));

        [passedButton, issueButton, resolveButton].forEach((button) => {
            if (!button) return;
            button.dataset.id = order.id;
            button.dataset.invoice = order.invoice_number;
            button.hidden = true;
            button.disabled = !canQc;
        });

        if (canQc && qcStatus === 'issue') {
            if (resolveButton) resolveButton.hidden = false;
        } else if (canQc) {
            if (passedButton) passedButton.hidden = false;
            if (issueButton) issueButton.hidden = false;
        }

        if (helpText) {
            helpText.textContent = order.sent_to_steadfast_at
                ? 'This order was already sent to Steadfast, so QC is locked.'
                : order.status === 'Confirmed'
                    ? 'QC can be updated from here or from the order table action buttons.'
                    : 'Order must be Confirmed before QC can be updated.';
        }
    }

    function createRow(order) {
        const invoiceUrl = routeUrl(page.dataset.invoiceUrl, order.id);
        const qcStatus = order.qc_status || 'not_checked';
        const canQc = order.status === 'Confirmed' && !order.sent_to_steadfast_at;
        const qcActions = canQc
            ? qcStatus === 'issue'
                ? `
                    <button
                        type="button"
                        class="brand-action-button edit resolveQcButton"
                        data-id="${order.id}"
                        data-invoice="${escapeHtml(order.invoice_number)}"
                    >
                        Resolve QC
                    </button>
                `
                : `
                    <button
                        type="button"
                        class="brand-action-button edit qcPassedButton"
                        data-id="${order.id}"
                    >
                        QC Passed
                    </button>
                    <button
                        type="button"
                        class="brand-action-button delete qcIssueButton"
                        data-id="${order.id}"
                        data-invoice="${escapeHtml(order.invoice_number)}"
                    >
                        QC Issue
                    </button>
                `
            : '';
        const courierButton = order.sent_to_steadfast_at
            ? `
                <button
                    type="button"
                    class="brand-action-button view viewSteadfastButton"
                    data-id="${order.id}"
                >
                    Sent
                </button>
            `
            : `
                <button
                    type="button"
                    class="brand-action-button view sendSteadfastButton"
                    data-id="${order.id}"
                    data-invoice="${escapeHtml(order.invoice_number)}"
                    ${qcStatus !== 'passed' ? 'disabled' : ''}
                >
                    Send to Steadfast
                </button>
            `;

        return `
            <tr id="orderRow${order.id}">
                <td>
                    <code class="brand-slug">
                        ${escapeHtml(order.invoice_number)}
                    </code>
                    <small class="order-table-muted">
                        ${escapeHtml(order.order_number)}
                    </small>
                </td>
                <td>
                    <div class="brand-name-cell">
                        <div class="brand-table-logo">
                            <span>
                                ${escapeHtml(order.customer_name.charAt(0).toUpperCase())}
                            </span>
                        </div>
                        <div>
                            <strong>${escapeHtml(order.customer_name)}</strong>
                            <small>${escapeHtml(order.phone)}</small>
                        </div>
                    </div>
                </td>
                <td>${escapeHtml(order.brand_name || '-')}</td>
                <td>
                    ${escapeHtml(order.delivery_area_label)}
                    <small class="order-table-muted">
                        ${escapeHtml(order.district || '-')}
                    </small>
                </td>
                <td>${Number(order.item_count || 0)}</td>
                <td><strong>${money(order.grand_total)}</strong></td>
                <td>
                    <span class="brand-status-badge ${orderStatusClass(order.status)}">
                        ${escapeHtml(order.status)}
                    </span>
                </td>
                <td>
                    <span class="brand-status-badge ${order.qc_status_class || qcStatusClass(qcStatus)}">
                        ${escapeHtml(order.qc_status_label || qcStatusLabel(qcStatus))}
                    </span>
                    ${
                        order.qc_by
                            ? `
                                <small class="order-table-muted">
                                    ${escapeHtml(order.qc_by)}
                                </small>
                            `
                            : ''
                    }
                </td>
                <td>
                    <span class="brand-status-badge ${
                        order.sent_to_steadfast_at ? 'active' : 'inactive'
                    }">
                        ${
                            order.sent_to_steadfast_at
                                ? escapeHtml(order.courier_status || 'sent')
                                : escapeHtml(order.courier_status || 'Not Sent')
                        }
                    </span>
                </td>
                <td>
                    <div class="brand-table-actions order-table-actions">
                        <a
                            href="${invoiceUrl}"
                            target="_blank"
                            class="brand-action-button view"
                        >
                            Invoice
                        </a>
                        <button
                            type="button"
                            class="brand-action-button view orderActivityButton"
                            data-id="${order.id}"
                        >
                            Activity
                        </button>
                        ${qcActions}
                        ${courierButton}
                        <button
                            type="button"
                            class="brand-action-button edit editOrderButton"
                            data-id="${order.id}"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="brand-action-button delete deleteOrderButton"
                            data-id="${order.id}"
                            data-invoice="${escapeHtml(order.invoice_number)}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncRow(order) {
        const row = document.getElementById(`orderRow${order.id}`);
        const html = createRow(order);

        if (row) {
            row.outerHTML = html;
            return;
        }

        document.getElementById('emptyOrderRow')?.remove();
        document.getElementById('orderTableBody')?.insertAdjacentHTML('afterbegin', html);
    }

    function renderSteadfastDetails(details) {
        const body = document.getElementById('steadfastDetailsBody');
        if (!body) {
            return;
        }

        body.innerHTML = `
            <div><span>Invoice</span><strong>${escapeHtml(details.invoice_number || '-')}</strong></div>
            <div><span>Consignment ID</span><strong>${escapeHtml(details.consignment_id || '-')}</strong></div>
            <div><span>Courier Status</span><strong>${escapeHtml(details.courier_status || '-')}</strong></div>
            <div><span>Last Checked</span><strong>${escapeHtml(details.latest_status_checked_at || '-')}</strong></div>
            <div><span>Sent At</span><strong>${escapeHtml(details.sent_at || '-')}</strong></div>
            <div><span>Sent By</span><strong>${escapeHtml(details.sent_by || '-')}</strong></div>
            <div><span>Sender Email</span><strong>${escapeHtml(details.sent_by_email || '-')}</strong></div>
            <div class="order-details-wide">
                <span>Steadfast Response</span>
                <pre>${escapeHtml(JSON.stringify(details.response || {}, null, 2))}</pre>
            </div>
            ${
                details.error
                    ? `
                        <div class="order-details-wide">
                            <span>Error</span>
                            <pre>${escapeHtml(details.error)}</pre>
                        </div>
                    `
                    : ''
            }
        `;
    }

    function renderOrderActivity(order) {
        const body = document.getElementById('orderActivityBody');
        if (!body) {
            return;
        }

        const logs = order.activity_logs || [];

        if (!logs.length) {
            body.innerHTML = `
                <div class="brand-empty-state">
                    <strong>No activity yet.</strong>
                    <span>This order has no tracked admin actions.</span>
                </div>
            `;
            return;
        }

        body.innerHTML = logs
            .map((log) => `
                <article>
                    <i class="fa-solid fa-timeline"></i>
                    <div>
                        <strong>${escapeHtml(actionLabel(log.action))}</strong>
                        <p>
                            ${escapeHtml(log.user || 'System')}
                            ${
                                log.old_value || log.new_value
                                    ? ` changed ${escapeHtml(log.old_value || '-')} to ${escapeHtml(log.new_value || '-')}`
                                    : ''
                            }
                        </p>
                        ${
                            log.note
                                ? `<p class="order-activity-note">${escapeHtml(log.note)}</p>`
                                : ''
                        }
                    </div>
                    <time>${escapeHtml(log.created_at || '-')}</time>
                </article>
            `)
            .join('');
    }

    document.getElementById('openAddOrderModal')?.addEventListener('click', () => {
        addForm?.reset();
        clearErrors(addForm);
        resetItemRows('add');
        handleDeliveryArea('add');
        openModal('addOrderModal');
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            closeModal(button.dataset.closeModal);
        });
    });

    page.addEventListener('click', async (event) => {
        const addItemButton = event.target.closest('[data-add-order-item]');
        const removeItemButton = event.target.closest('[data-remove-order-item]');
        const editButton = event.target.closest('.editOrderButton');
        const deleteButton = event.target.closest('.deleteOrderButton');
        const sendButton = event.target.closest('.sendSteadfastButton');
        const sentButton = event.target.closest('.viewSteadfastButton');
        const qcPassedButton = event.target.closest('.qcPassedButton');
        const qcIssueButton = event.target.closest('.qcIssueButton');
        const resolveQcButton = event.target.closest('.resolveQcButton');
        const activityButton = event.target.closest('.orderActivityButton');

        if (addItemButton) {
            addItemRow(addItemButton.dataset.addOrderItem);
            return;
        }

        if (removeItemButton) {
            removeItemButton.closest('[data-order-item]')?.remove();
            return;
        }

        if (editButton) {
            try {
                editButton.disabled = true;
                const response = await fetch(
                    routeUrl(page.dataset.showUrl, editButton.dataset.id),
                    { headers: { Accept: 'application/json' } }
                );
                const data = await parseResponse(response);
                editForm?.reset();
                clearErrors(editForm);
                fillForm('edit', data.order);
                openModal('editOrderModal');
            } catch (error) {
                showToast(error.data?.message || 'Unable to load order.', 'error');
            } finally {
                editButton.disabled = false;
            }
        }

        if (deleteButton) {
            document.getElementById('delete_order_id').value = deleteButton.dataset.id;
            document.getElementById('deleteOrderInvoice').textContent = deleteButton.dataset.invoice;
            openModal('deleteOrderModal');
        }

        if (sendButton) {
            if (sendButton.disabled) {
                showToast('This order must pass QC before sending to Steadfast.', 'error');
                return;
            }

            document.getElementById('send_steadfast_order_id').value = sendButton.dataset.id;
            document.getElementById('sendSteadfastInvoice').textContent = sendButton.dataset.invoice;
            openModal('sendSteadfastModal');
        }

        if (qcPassedButton) {
            try {
                qcPassedButton.disabled = true;
                const response = await fetch(
                    routeUrl(page.dataset.qcPassedUrl, qcPassedButton.dataset.id),
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            Accept: 'application/json',
                        },
                    }
                );
                const data = await parseResponse(response);
                syncRow(data.order);
                if (document.getElementById('edit_order_id')?.value === String(data.order.id)) {
                    fillEditQcPanel(data.order);
                }
                showToast(data.message);
            } catch (error) {
                showToast(error.data?.message || 'Unable to pass QC.', 'error');
            } finally {
                qcPassedButton.disabled = false;
            }
        }

        if (qcIssueButton) {
            document.getElementById('qc_issue_order_id').value = qcIssueButton.dataset.id;
            document.getElementById('qcIssueInvoice').textContent = qcIssueButton.dataset.invoice;
            document.getElementById('qcIssueNote').value = '';
            openModal('qcIssueModal');
        }

        if (resolveQcButton) {
            document.getElementById('resolve_qc_order_id').value = resolveQcButton.dataset.id;
            document.getElementById('resolveQcInvoice').textContent = resolveQcButton.dataset.invoice;
            document.getElementById('resolveQcNote').value = '';
            openModal('resolveQcModal');
        }

        if (activityButton) {
            try {
                activityButton.disabled = true;
                const response = await fetch(
                    routeUrl(page.dataset.showUrl, activityButton.dataset.id),
                    { headers: { Accept: 'application/json' } }
                );
                const data = await parseResponse(response);
                renderOrderActivity(data.order);
                openModal('orderActivityModal');
            } catch (error) {
                showToast(error.data?.message || 'Unable to load order activity.', 'error');
            } finally {
                activityButton.disabled = false;
            }
        }

        if (sentButton) {
            try {
                sentButton.disabled = true;
                const response = await fetch(
                    routeUrl(page.dataset.steadfastUrl, sentButton.dataset.id),
                    { headers: { Accept: 'application/json' } }
                );
                const data = await parseResponse(response);
                if (data.order) {
                    syncRow(data.order);
                }
                renderSteadfastDetails(data.details);
                openModal('steadfastDetailsModal');
            } catch (error) {
                showToast(error.data?.message || 'Unable to load Steadfast details.', 'error');
            } finally {
                sentButton.disabled = false;
            }
        }
    });

    page.addEventListener('change', (event) => {
        const productSelect = event.target.closest('[data-order-product]');
        const deliverySelect = event.target.closest('[id$="_delivery_area"]');

        if (productSelect) {
            const selected = productSelect.selectedOptions[0];
            const row = productSelect.closest('[data-order-item]');
            const priceInput = row?.querySelector('[data-order-unit-price]');

            if (selected?.dataset.price && priceInput) {
                priceInput.value = selected.dataset.price;
            }
        }

        if (deliverySelect) {
            handleDeliveryArea(deliverySelect.id.startsWith('add_') ? 'add' : 'edit');
        }
    });

    page.addEventListener('input', (event) => {
        const addressInput = event.target.closest(
            '[id$="_house_no"], [id$="_road_no"], [id$="_area_thana"], [id$="_district"]'
        );

        if (addressInput) {
            syncAddress(addressInput.id.startsWith('add_') ? 'add' : 'edit');
        }
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
                    Accept: 'application/json',
                },
                body: buildFormData(addForm, 'add'),
            });

            const data = await parseResponse(response);
            syncRow(data.order);
            closeModal('addOrderModal');
            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(addForm, error.data.errors || {});
            } else {
                showToast(error.data?.message || 'Unable to add order.', 'error');
            }
        } finally {
            setLoading(addSubmit, false);
        }
    });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(editForm);
        const id = document.getElementById('edit_order_id').value;
        setLoading(editSubmit, true, 'Updating...');

        try {
            const response = await fetch(routeUrl(page.dataset.updateUrl, id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: buildFormData(editForm, 'edit'),
            });

            const data = await parseResponse(response);
            syncRow(data.order);
            closeModal('editOrderModal');
            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(editForm, error.data.errors || {});
            } else {
                showToast(error.data?.message || 'Unable to update order.', 'error');
            }
        } finally {
            setLoading(editSubmit, false);
        }
    });

    deleteSubmit?.addEventListener('click', async () => {
        const id = document.getElementById('delete_order_id').value;
        setLoading(deleteSubmit, true, 'Deleting...');

        try {
            const response = await fetch(routeUrl(page.dataset.deleteUrl, id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });
            const data = await parseResponse(response);
            document.getElementById(`orderRow${id}`)?.remove();
            closeModal('deleteOrderModal');
            showToast(data.message);
        } catch (error) {
            showToast(error.data?.message || 'Unable to delete order.', 'error');
        } finally {
            setLoading(deleteSubmit, false);
        }
    });

    sendSubmit?.addEventListener('click', async () => {
        const id = document.getElementById('send_steadfast_order_id').value;
        setLoading(sendSubmit, true, 'Sending...');

        try {
            const response = await fetch(routeUrl(page.dataset.sendUrl, id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });
            const data = await parseResponse(response);
            syncRow(data.order);
            closeModal('sendSteadfastModal');
            showToast(data.message);
        } catch (error) {
            showToast(error.data?.message || 'Unable to send order.', 'error');
        } finally {
            setLoading(sendSubmit, false);
        }
    });

    qcIssueSubmit?.addEventListener('click', async () => {
        const id = document.getElementById('qc_issue_order_id').value;
        const note = document.getElementById('qcIssueNote').value.trim();

        if (!note) {
            showToast('QC issue note is required.', 'error');
            return;
        }

        setLoading(qcIssueSubmit, true, 'Saving...');

        try {
            const response = await fetch(routeUrl(page.dataset.qcIssueUrl, id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ note }),
            });
            const data = await parseResponse(response);
            syncRow(data.order);
            if (document.getElementById('edit_order_id')?.value === String(data.order.id)) {
                fillEditQcPanel(data.order);
            }
            closeModal('qcIssueModal');
            showToast(data.message);
        } catch (error) {
            showToast(error.data?.message || 'Unable to mark QC issue.', 'error');
        } finally {
            setLoading(qcIssueSubmit, false);
        }
    });

    resolveQcSubmit?.addEventListener('click', async () => {
        const id = document.getElementById('resolve_qc_order_id').value;
        const note = document.getElementById('resolveQcNote').value.trim();

        setLoading(resolveQcSubmit, true, 'Resolving...');

        try {
            const response = await fetch(routeUrl(page.dataset.resolveQcUrl, id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ note }),
            });
            const data = await parseResponse(response);
            syncRow(data.order);
            if (document.getElementById('edit_order_id')?.value === String(data.order.id)) {
                fillEditQcPanel(data.order);
            }
            closeModal('resolveQcModal');
            showToast(data.message);
        } catch (error) {
            showToast(error.data?.message || 'Unable to resolve QC issue.', 'error');
        } finally {
            setLoading(resolveQcSubmit, false);
        }
    });

    document.getElementById('closeOrderToast')?.addEventListener('click', () => {
        document.getElementById('orderToast')?.classList.remove('show');
    });

    const focusOrderId = new URLSearchParams(window.location.search)
        .get('focus_order');

    if (focusOrderId) {
        const row = document.getElementById(`orderRow${focusOrderId}`);

        row?.classList.add('admin-row-focused');
        row?.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    }
});

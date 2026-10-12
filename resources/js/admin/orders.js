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
    const products = JSON.parse(document.getElementById('orderProductData')?.textContent || '[]');
    const vatEnabled = page.dataset.vatEnabled === '1';
    const vatPercent = Number(page.dataset.vatPercent || 0);
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

        form?.querySelectorAll('[data-client-validation-error]').forEach((item) => item.remove());

        form?.querySelectorAll('input, select, textarea').forEach((item) => {
            item.classList.remove('brand-input-invalid');
        });
    }

    function displayErrors(form, errors) {
        let firstInvalid = null;

        Object.entries(errors).forEach(([field, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;

            if (field.startsWith('items')) {
                const match = field.match(/^items\.(\d+)\.(\w+)/);
                const row = match
                    ? form.querySelectorAll('[data-order-item]')[Number(match[1])]
                    : null;
                const target = row?.querySelector(`[data-order-${match?.[2] || 'product'}]`)
                    || row?.querySelector('[data-order-product-search]');

                setValidationError(target, message);
                firstInvalid ||= target;
                return;
            }

            const errorElement = form.querySelector(`.${field}_error`);
            const input = form.querySelector(`[name="${field}"]`);

            if (errorElement) {
                errorElement.textContent = message;
            }

            input?.classList.add('brand-input-invalid');
            firstInvalid ||= input;
        });

        scrollToInvalid(form, firstInvalid);
    }

    function setValidationError(field, message) {
        if (!field) return;

        const container = field.closest('.brand-form-field') || field.closest('[data-order-item]');
        field.classList.add('brand-input-invalid');

        let error = container?.querySelector('[data-client-validation-error]');
        if (!error && container) {
            error = document.createElement('small');
            error.className = 'brand-field-error';
            error.dataset.clientValidationError = 'true';
            container.appendChild(error);
        }
        if (error) error.textContent = message;
    }

    function clearValidationError(field) {
        const container = field?.closest('.brand-form-field') || field?.closest('[data-order-item]');
        field?.classList.remove('brand-input-invalid');
        const error = container?.querySelector('[data-client-validation-error]');
        if (error) error.remove();
    }

    function scrollToInvalid(form, field) {
        if (!field) return;

        const modal = form?.closest('.brand-modal');
        const body = modal?.querySelector('.brand-modal-body');
        const header = modal?.querySelector('.brand-modal-header');

        if (body) {
            const bodyRect = body.getBoundingClientRect();
            const fieldRect = field.getBoundingClientRect();
            const headerOffset = (header?.getBoundingClientRect().height || 0) + 24;
            const targetTop = body.scrollTop
                + (fieldRect.top - bodyRect.top)
                - headerOffset;

            body.scrollTo({
                top: Math.max(targetTop, 0),
                behavior: 'smooth',
            });
        }

        window.setTimeout(() => field.focus?.({ preventScroll: true }), 220);
    }

    function validateOrderForm(form, prefix) {
        let firstInvalid = null;
        const requiredMessages = {
            customer_name: 'Please enter customer name.',
            phone: 'Please enter phone number.',
            brand_id: 'Please select a brand.',
            district: 'Please enter district.',
            area_thana: 'Please enter area / thana.',
            road_no: 'Please enter road name / number / block.',
            house_no: 'Please enter house name / number.',
            full_address: 'Please enter full address.',
        };
        const invalidate = (field, message) => {
            setValidationError(field, message);
            firstInvalid ||= field;
        };

        form.querySelectorAll('[required]').forEach((field) => {
            if (!field.value?.trim()) {
                invalidate(field, requiredMessages[field.name] || 'Please complete this field.');
            }
        });

        form.querySelectorAll('[data-order-item]').forEach((row) => {
            const product = productById(row.querySelector('[data-order-product]')?.value);
            const productSearch = row.querySelector('[data-order-product-search]');
            const quantity = row.querySelector('[data-order-quantity]');
            const color = row.querySelector('[data-order-color]');
            const size = row.querySelector('[data-order-size]');
            const variants = product?.variants || [];

            if (!product) invalidate(productSearch, 'Please select a product.');
            if (variants.length && !color?.value) invalidate(color, 'Please select a color.');
            if (variants.length && !size?.value) invalidate(size, 'Please select a size.');
            if (!quantity?.value || Number(quantity.value) < 1) invalidate(quantity, 'Quantity must be at least 1.');

            const packagingInput = row.querySelector('[data-order-packaging-type]');
            if (product?.packaging_type_enabled && !['normal', 'premium'].includes(packagingInput?.value)) {
                invalidate(row.querySelector('[data-order-packaging-field]'), 'Please select a packaging option.');
            }
        });

        scrollToInvalid(form, firstInvalid);
        return !firstInvalid;
    }

    function itemList(prefix) {
        return document.getElementById(`${prefix}_order_items`);
    }

    function uniqueValues(values) {
        return [...new Set(values.filter(Boolean))];
    }

    function productById(id) {
        return products.find((product) => String(product.id) === String(id));
    }

    function productLabel(product) {
        return `${product.brand_name || 'Brand'} - ${product.name}${product.code ? ` (${product.code})` : ''}`;
    }

    function fillSelect(select, values, placeholder) {
        if (!select) return;

        select.innerHTML = `<option value="">${placeholder}</option>`;

        values.forEach((value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        });
    }

    function reservedStockForSelection(row, color, size) {
        const reservedColor = row.dataset.reservedColor || '';
        const reservedSize = row.dataset.reservedSize || '';

        if (color && size && color === reservedColor && size === reservedSize) {
            return Number(row.dataset.reservedQuantity || 0);
        }

        return 0;
    }

    function variantAvailableStock(row, variant) {
        return Number(variant?.stock || 0)
            + reservedStockForSelection(row, variant?.color || '', variant?.size || '');
    }

    function populateVariantFields(row, product, selectedColor = '', selectedSize = '') {
        const colorSelect = row.querySelector('[data-order-color]');
        const sizeSelect = row.querySelector('[data-order-size]');
        const colors = uniqueValues((product?.variants || []).map((variant) => variant.color));

        fillSelect(colorSelect, colors, 'Select color');

        if (selectedColor && colors.includes(selectedColor)) {
            colorSelect.value = selectedColor;
        }

        const sizes = uniqueValues(
            (product?.variants || [])
                .filter((variant) => !colorSelect.value || variant.color === colorSelect.value)
                .map((variant) => {
                    const stock = variantAvailableStock(row, variant);

                    return `${variant.size}${stock > 0 ? ` (${stock} pcs)` : ' (0 pcs)'}`;
                })
        );

        fillSelect(sizeSelect, sizes, 'Select size');

        if (selectedSize) {
            const option = Array.from(sizeSelect.options).find((item) => item.value.replace(/\s+\(\d+\s+pcs\)$/, '') === selectedSize);

            if (option) {
                sizeSelect.value = option.value;
            }
        }
    }

    function selectedSizeValue(row) {
        return row.querySelector('[data-order-size]')?.value.replace(/\s+\(\d+\s+pcs\)$/, '') || '';
    }

    function selectedVariantStock(row) {
        const product = productById(row.querySelector('[data-order-product]')?.value);
        const color = row.querySelector('[data-order-color]')?.value || '';
        const size = selectedSizeValue(row);

        if (!product || !color || !size) {
            return null;
        }

        const variant = (product.variants || []).find((item) => item.color === color && item.size === size);

        return variant ? variantAvailableStock(row, variant) : null;
    }

    function enforceQuantityStock(row) {
        const input = row.querySelector('[data-order-quantity]');
        const error = row.querySelector('[data-order-quantity-error]');
        const stock = selectedVariantStock(row);

        if (!input) {
            return;
        }

        input.removeAttribute('max');

        if (error) {
            error.textContent = '';
        }

        if (stock === null) {
            return;
        }

        input.max = stock;

        if (Number(input.value || 0) > stock) {
            input.value = stock;

            if (error) {
                error.textContent = `Only ${stock} pcs available for this size. Quantity adjusted.`;
            }
        }
    }

    function setRowProduct(row, product, item = {}) {
        row.querySelector('[data-order-product]').value = product?.id || '';
        row.querySelector('[data-order-product-search]').value = product ? productLabel(product) : '';
        row.querySelector('[data-order-unit-price]').value = item.unit_price || product?.price || 0;
        const packagingField = row.querySelector('[data-order-packaging-field]');
        const packagingInput = row.querySelector('[data-order-packaging-type]');
        const packagingEnabled = Boolean(product?.packaging_type_enabled);

        if (packagingField) packagingField.hidden = !packagingEnabled;
        if (packagingInput) {
            packagingInput.value = packagingEnabled && item.packaging_type === 'premium'
                ? 'premium'
                : 'normal';
        }
        row.querySelectorAll('[data-order-packaging]').forEach((button) => {
            button.classList.toggle('is-selected', button.dataset.orderPackaging === packagingInput?.value);
        });
        const premiumLabel = row.querySelector('[data-order-premium-label]');
        if (premiumLabel) premiumLabel.textContent = product?.packaging_tiers?.length ? 'Choose tier' : `+${money(product?.premium_packaging_charge || 0)}`;
        const tierInput = row.querySelector('[data-order-packaging-tier-id]');
        if (tierInput) tierInput.value = item.packaging_tier_id || '';
        renderOrderPackagingTiers(row, product, item.packaging_tier_id);
        populateVariantFields(row, product, item.color || '', item.size || '');
        if (row.closest('[id$="_order_items"]')) {
            const prefix = rowPrefix(row);
            const brandSelect = document.getElementById(`${prefix}_brand_id`);

            if (product?.brand_id && brandSelect && !brandSelect.value) {
                brandSelect.value = product.brand_id;
            }

            updateOrderBill(prefix);
        }
    }

    function renderOrderPackagingTiers(row, product, selectedId = '') {
        const container = row.querySelector('[data-order-packaging-tiers]');
        if (!container) return;
        const tiers = Array.isArray(product?.packaging_tiers) ? product.packaging_tiers : [];
        const quantity = Number(row.querySelector('[data-order-quantity]')?.value || 0);
        const recommended = tiers.find((tier) => quantity >= Number(tier.min_pieces) && quantity <= Number(tier.max_pieces));
        const activeId = selectedId || recommended?.id || '';
        const tierInput = row.querySelector('[data-order-packaging-tier-id]');
        if (tierInput && row.querySelector('[data-order-packaging-type]')?.value === 'premium') {
            tierInput.value = activeId;
        }
        container.hidden = !tiers.length || row.querySelector('[data-order-packaging-type]')?.value !== 'premium';
        container.innerHTML = tiers.map((tier) => {
            const valid = quantity >= Number(tier.min_pieces) && quantity <= Number(tier.max_pieces);
            return `<button type="button" class="order-packaging-tier ${String(tier.id) === String(activeId) ? 'is-selected' : ''}" data-order-packaging-tier="${tier.id}" ${valid ? '' : 'disabled'}><strong>${escapeHtml(tier.name)}</strong><small>${tier.min_pieces}–${tier.max_pieces} pcs · ${money(tier.price)}${tier.id === recommended?.id ? ' · Recommended' : ''}</small></button>`;
        }).join('');
    }

    function rowPrefix(row) {
        return row.closest('[id$="_order_items"]')?.id.startsWith('add_') ? 'add' : 'edit';
    }

    function renderProductResults(row, query) {
        const results = row.querySelector('[data-order-product-results]');
        const term = query.trim().toLowerCase();
        const selectedBrand = document.getElementById(`${rowPrefix(row)}_brand_id`)?.value;

        if (!results) return;

        if (!term) {
            results.hidden = true;
            results.innerHTML = '';
            return;
        }

        const matches = products
            .filter((product) => !selectedBrand || String(product.brand_id) === String(selectedBrand))
            .filter((product) => {
                const haystack = `${product.name} ${product.code || ''} ${product.brand_name || ''}`.toLowerCase();
                return haystack.includes(term);
            })
            .slice(0, 8);

        results.innerHTML = matches.length
            ? matches.map((product) => `
                <button type="button" data-select-order-product="${product.id}">
                    <strong>${escapeHtml(product.name)}</strong>
                    <span>${escapeHtml(product.brand_name || '-')} · ${escapeHtml(product.code || 'No code')} · ${money(product.price)}</span>
                </button>
            `).join('')
            : '<p>No product found</p>';
        results.hidden = false;
        highlightProductResult(results, 0);
    }

    function productResultButtons(results) {
        return Array.from(results?.querySelectorAll('[data-select-order-product]') || []);
    }

    function highlightProductResult(results, index) {
        const buttons = productResultButtons(results);

        buttons.forEach((button, buttonIndex) => {
            const active = buttonIndex === index;
            button.classList.toggle('is-highlighted', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        results.dataset.highlightedIndex = String(index);
        buttons[index]?.scrollIntoView({
            block: 'nearest',
        });
    }

    function addItemRow(prefix, item = {}) {
        const list = itemList(prefix);

        if (!list || !itemTemplate) {
            return;
        }

        const fragment = itemTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-order-item]');
        const product = item.product_id ? productById(item.product_id) : null;

        row.dataset.reservedColor = item.color || '';
        row.dataset.reservedSize = item.size || '';
        row.dataset.reservedQuantity = item.quantity || 0;
        setRowProduct(row, product, item);
        row.querySelector('[data-order-quantity]').value = item.quantity || 1;
        row.querySelector('[data-order-unit-price]').value = item.unit_price || product?.price || 0;

        list.appendChild(fragment);
        updateOrderBill(prefix);
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
                size: selectedSizeValue(row),
                quantity: row.querySelector('[data-order-quantity]')?.value || '1',
                unit_price: row.querySelector('[data-order-unit-price]')?.value || '0',
                packaging_type: row.querySelector('[data-order-packaging-type]')?.value || 'normal',
                packaging_tier_id: row.querySelector('[data-order-packaging-tier-id]')?.value || '',
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

    function deliveryCharge(prefix) {
        const paymentStatus = document.getElementById(`${prefix}_payment_status`)?.value;
        const paidInput = document.getElementById(`${prefix}_delivery_charge_paid`);

        if (paymentStatus === 'Delivery Charge Paid') {
            return Number(paidInput?.value || 0);
        }

        return document.getElementById(`${prefix}_delivery_area`)?.value === 'outside_dhaka' ? 130 : 80;
    }

    function paidAmount(prefix, grandTotal) {
        const paymentStatus = document.getElementById(`${prefix}_payment_status`)?.value;

        if (paymentStatus === 'Paid') {
            return grandTotal;
        }

        if (paymentStatus === 'Delivery Charge Paid') {
            return Math.min(deliveryCharge(prefix), grandTotal);
        }

        return 0;
    }

    function updateOrderBill(prefix) {
        const rows = Array.from(itemList(prefix)?.querySelectorAll('[data-order-item]') || []);
        const preview = document.getElementById(`${prefix}_order_preview_items`);
        const manualDiscount = Number(document.getElementById(`${prefix}_manual_discount_amount`)?.value || 0);
        let packagingTotal = 0;
        const subtotal = rows.reduce((total, row) => {
            enforceQuantityStock(row);
            const quantity = Number(row.querySelector('[data-order-quantity]')?.value || 0);
            const unitPrice = Number(row.querySelector('[data-order-unit-price]')?.value || 0);
            const lineTotal = quantity * unitPrice;
            const product = productById(row.querySelector('[data-order-product]')?.value);
            const packagingType = row.querySelector('[data-order-packaging-type]')?.value || 'normal';

            if (packagingType === 'premium' && product?.packaging_type_enabled) {
                const tier = (product.packaging_tiers || []).find((item) => String(item.id) === String(row.querySelector('[data-order-packaging-tier-id]')?.value));
                packagingTotal += Number(tier?.price || 0);
            }
            const lineTotalInput = row.querySelector('[data-order-line-total]');

            if (lineTotalInput) {
                lineTotalInput.value = money(lineTotal);
            }

            return total + lineTotal;
        }, 0);
        const delivery = deliveryCharge(prefix);
        const taxable = Math.max(subtotal + packagingTotal + delivery - manualDiscount, 0);
        const vat = vatEnabled ? taxable * vatPercent / 100 : 0;
        const grandTotal = Math.max(taxable + vat, 0);
        const paid = paidAmount(prefix, grandTotal);
        const due = Math.max(grandTotal - paid, 0);

        if (preview) {
            const selected = readItemRows(prefix);

            preview.innerHTML = selected.length
                ? selected.map((item) => {
                    const product = productById(item.product_id);
                    const total = Number(item.quantity || 0) * Number(item.unit_price || 0);
                    const tier = (product?.packaging_tiers || []).find((candidate) => String(candidate.id) === String(item.packaging_tier_id));
                    const packagingCharge = item.packaging_type === 'premium' ? Number(tier?.price || 0) : 0;

                    return `
                        <div class="order-preview-item">
                            <span>${escapeHtml(product ? productLabel(product) : 'Product')}</span>
                            <small>${escapeHtml(item.color || '-')} / ${escapeHtml(item.size || '-')} × ${escapeHtml(item.quantity)}${item.packaging_type === 'premium' ? ` · Premium Packaging · ${escapeHtml(tier?.name || 'Selected tier')} · ${money(packagingCharge)}` : ''}</small>
                            <strong>${money(total)}</strong>
                        </div>
                    `;
                }).join('')
                : '<p class="order-preview-empty">No items selected yet.</p>';
        }

        const values = {
            subtotal,
            packaging: packagingTotal,
            delivery,
            discount: manualDiscount,
            vat,
            total: grandTotal,
            paid,
            due,
        };

        Object.entries(values).forEach(([key, value]) => {
            const target = document.getElementById(`${prefix}_bill_${key}`);
            if (target) {
                target.textContent = `${key === 'discount' ? '-' : ''}${money(value)}`;
            }
        });
    }

    function syncDeliveryChargePaidField(prefix) {
        const paymentStatus = document.getElementById(`${prefix}_payment_status`)?.value;
        const wrap = document.getElementById(`${prefix}_delivery_charge_paid_wrap`);
        const input = document.getElementById(`${prefix}_delivery_charge_paid`);
        const shouldShow = paymentStatus === 'Delivery Charge Paid';

        if (wrap) {
            wrap.hidden = !shouldShow;
        }

        if (input) {
            input.required = shouldShow;

            if (!shouldShow) {
                input.value = '';
            }
        }
    }

    function syncAddress(prefix) {
        const house = document.getElementById(`${prefix}_house_no`)?.value.trim();
        const road = document.getElementById(`${prefix}_road_no`)?.value.trim();
        const area = document.getElementById(`${prefix}_area_thana`)?.value.trim();
        const district = document.getElementById(`${prefix}_district`)?.value.trim();
        const output = document.getElementById(`${prefix}_full_address`);
        const parts = [];

        if (house) parts.push(`House- ${house}`);
        if (road) parts.push(`Road/Block- ${road}`);
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
            'delivery_charge_paid',
            'manual_discount_amount',
            'order_channel',
            'order_source',
            'source_note',
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
        syncDeliveryChargePaidField(prefix);
        updateOrderBill(prefix);

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
                        class="brand-action-button edit qc-pass qcPassedButton"
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
                    ${escapeHtml(order.order_source_label || '-')}
                    <small class="order-table-muted">
                        ${escapeHtml(order.order_channel_label || '-')}
                        ${order.added_by ? ` · ${escapeHtml(order.added_by)}` : ''}
                    </small>
                </td>
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
                                    by ${escapeHtml(order.qc_by)}
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
                        ${
                            log.issue_image_url
                                ? `
                                    <a
                                        class="order-activity-image-link"
                                        href="${escapeHtml(log.issue_image_url)}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <span>🖼️</span>
                                        View issue photo
                                    </a>
                                `
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
        syncDeliveryChargePaidField('add');
        updateOrderBill('add');
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
            updateOrderBill(addItemButton.dataset.addOrderItem);
            return;
        }

        if (removeItemButton) {
            const row = removeItemButton.closest('[data-order-item]');
            const prefix = rowPrefix(row);
            row?.remove();
            updateOrderBill(prefix);
            return;
        }

        const packagingButton = event.target.closest('[data-order-packaging]');
        if (packagingButton) {
            const row = packagingButton.closest('[data-order-item]');
            const input = row?.querySelector('[data-order-packaging-type]');

            if (input) {
                input.value = packagingButton.dataset.orderPackaging === 'premium'
                    ? 'premium'
                    : 'normal';
                if (input.value === 'normal') {
                    row.querySelector('[data-order-packaging-tier-id]').value = '';
                }
                row.querySelectorAll('[data-order-packaging]').forEach((button) => {
                    button.classList.toggle('is-selected', button === packagingButton);
                });
                renderOrderPackagingTiers(row, productById(row.querySelector('[data-order-product]')?.value));
                updateOrderBill(rowPrefix(row));
            }
            return;
        }

        const tierButton = event.target.closest('[data-order-packaging-tier]');
        if (tierButton) {
            const row = tierButton.closest('[data-order-item]');
            row.querySelector('[data-order-packaging-tier-id]').value = tierButton.dataset.orderPackagingTier;
            row.querySelectorAll('[data-order-packaging-tier]').forEach((button) => button.classList.toggle('is-selected', button === tierButton));
            updateOrderBill(rowPrefix(row));
            return;
        }

        const productResult = event.target.closest('[data-select-order-product]');

        if (productResult) {
            const row = productResult.closest('[data-order-item]');
            const product = productById(productResult.dataset.selectOrderProduct);
            setRowProduct(row, product);
            row.querySelector('[data-order-product-results]').hidden = true;
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
            document.getElementById('qcIssueImage').value = '';
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
        clearValidationError(event.target);
        const deliverySelect = event.target.closest('[id$="_delivery_area"]');
        const paymentStatusSelect = event.target.closest('[id$="_payment_status"]');
        const colorSelect = event.target.closest('[data-order-color]');
        const sizeSelect = event.target.closest('[data-order-size]');

        if (deliverySelect) {
            const prefix = deliverySelect.id.startsWith('add_') ? 'add' : 'edit';
            handleDeliveryArea(prefix);
            updateOrderBill(prefix);
        }

        if (paymentStatusSelect) {
            const prefix = paymentStatusSelect.id.startsWith('add_') ? 'add' : 'edit';
            syncDeliveryChargePaidField(prefix);
            updateOrderBill(prefix);
        }

        if (colorSelect) {
            const row = colorSelect.closest('[data-order-item]');
            populateVariantFields(row, productById(row.querySelector('[data-order-product]')?.value), colorSelect.value);
            updateOrderBill(rowPrefix(row));
        }

        if (sizeSelect) {
            updateOrderBill(rowPrefix(sizeSelect.closest('[data-order-item]')));
        }
    });

    page.addEventListener('input', (event) => {
        clearValidationError(event.target);
        const productSearch = event.target.closest('[data-order-product-search]');
        const quantityOrPrice = event.target.closest('[data-order-quantity], [data-order-unit-price]');
        const billInput = event.target.closest('[id$="_manual_discount_amount"], [id$="_delivery_charge_paid"]');
        const addressInput = event.target.closest(
            '[id$="_house_no"], [id$="_road_no"], [id$="_area_thana"], [id$="_district"]'
        );

        if (productSearch) {
            const row = productSearch.closest('[data-order-item]');
            row.querySelector('[data-order-product]').value = '';
            renderProductResults(row, productSearch.value);
            updateOrderBill(rowPrefix(row));
        }

        if (quantityOrPrice) {
            const row = quantityOrPrice.closest('[data-order-item]');
            renderOrderPackagingTiers(row, productById(row.querySelector('[data-order-product]')?.value), row.querySelector('[data-order-packaging-tier-id]')?.value);
            updateOrderBill(rowPrefix(quantityOrPrice.closest('[data-order-item]')));
        }

        if (billInput) {
            updateOrderBill(billInput.id.startsWith('add_') ? 'add' : 'edit');
        }

        if (addressInput) {
            syncAddress(addressInput.id.startsWith('add_') ? 'add' : 'edit');
        }
    });

    page.addEventListener('keydown', (event) => {
        const productSearch = event.target.closest('[data-order-product-search]');

        if (!productSearch) {
            return;
        }

        const row = productSearch.closest('[data-order-item]');
        const results = row.querySelector('[data-order-product-results]');
        const buttons = productResultButtons(results);

        if (results.hidden || !buttons.length) {
            return;
        }

        if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const currentIndex = Number(results.dataset.highlightedIndex || 0);

        if (event.key === 'ArrowDown') {
            highlightProductResult(results, Math.min(currentIndex + 1, buttons.length - 1));
        }

        if (event.key === 'ArrowUp') {
            highlightProductResult(results, Math.max(currentIndex - 1, 0));
        }

        if (event.key === 'Escape') {
            results.hidden = true;
        }

        if (event.key === 'Enter') {
            const selected = buttons[Number(results.dataset.highlightedIndex || 0)] || buttons[0];
            selected?.click();
        }
    });

    addForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(addForm);
        if (!validateOrderForm(addForm, 'add')) {
            return;
        }
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
        if (!validateOrderForm(editForm, 'edit')) {
            return;
        }
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
        const issueImage = document.getElementById('qcIssueImage')?.files?.[0];

        if (!note) {
            showToast('QC issue note is required.', 'error');
            return;
        }

        setLoading(qcIssueSubmit, true, 'Saving...');

        try {
            const body = new FormData();
            body.append('note', note);

            if (issueImage) {
                body.append('issue_image', issueImage);
            }

            const response = await fetch(routeUrl(page.dataset.qcIssueUrl, id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body,
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

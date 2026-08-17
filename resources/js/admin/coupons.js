document.addEventListener(
    'DOMContentLoaded',
    function () {
        const page =
            document.getElementById(
                'couponCrudPage'
            );

        if (!page) {
            return;
        }

        const csrfToken =
            document
                .querySelector(
                    'meta[name="csrf-token"]'
                )
                ?.getAttribute('content')
            || '';

        const addForm =
            document.getElementById(
                'addCouponForm'
            );

        const editForm =
            document.getElementById(
                'editCouponForm'
            );

        const addSubmit =
            document.getElementById(
                'addCouponSubmitButton'
            );

        const editSubmit =
            document.getElementById(
                'editCouponSubmitButton'
            );

        const deleteSubmit =
            document.getElementById(
                'confirmDeleteCouponButton'
            );

        let deleteId = null;
        let toastTimer = null;

        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        function routeUrl(
            template,
            id
        ) {
            return String(template || '')
                .replace(
                    '__ID__',
                    String(id || '')
                );
        }

        function openModal(id) {
            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.add('open');

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'brand-modal-open'
            );
        }

        function closeModal(id) {
            const modal =
                document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove('open');

            modal.setAttribute(
                'aria-hidden',
                'true'
            );

            if (
                !document.querySelector(
                    '.brand-modal.open'
                )
            ) {
                document.body.classList.remove(
                    'brand-modal-open'
                );
            }
        }

        function escapeHtml(
            value = ''
        ) {
            const div =
                document.createElement('div');

            div.textContent =
                String(value ?? '');

            return div.innerHTML;
        }

        function money(value) {
            return Number(
                value || 0
            ).toLocaleString(
                'en-BD',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            );
        }

        function setLoading(
            button,
            state,
            loadingText = ''
        ) {
            if (!button) {
                return;
            }

            if (state) {
                if (
                    !button.dataset
                        .originalText
                ) {
                    button.dataset.originalText =
                        button.textContent.trim();
                }

                button.textContent =
                    loadingText;

                button.disabled = true;

                return;
            }

            button.textContent =
                button.dataset.originalText
                || button.textContent;

            button.disabled = false;
        }

        async function parseResponse(
            response
        ) {
            const data =
                await response
                    .json()
                    .catch(function () {
                        return {
                            message:
                                'Invalid server response.',
                        };
                    });

            if (!response.ok) {
                throw {
                    status:
                        response.status,

                    data,
                };
            }

            return data;
        }

        function showToast(
            message,
            type = 'success'
        ) {
            const toast =
                document.getElementById(
                    'couponToast'
                );

            const title =
                document.getElementById(
                    'couponToastTitle'
                );

            const body =
                document.getElementById(
                    'couponToastMessage'
                );

            const icon =
                document.getElementById(
                    'couponToastIcon'
                );

            if (!toast) {
                return;
            }

            toast.classList.toggle(
                'error',
                type === 'error'
            );

            if (title) {
                title.textContent =
                    type === 'error'
                        ? 'Error'
                        : 'Success';
            }

            if (body) {
                body.textContent =
                    message || '';
            }

            if (icon) {
                icon.textContent =
                    type === 'error'
                        ? '!'
                        : '✓';
            }

            toast.classList.add('show');

            window.clearTimeout(
                toastTimer
            );

            toastTimer =
                window.setTimeout(
                    function () {
                        toast.classList.remove(
                            'show'
                        );
                    },
                    3500
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Form Helpers
        |--------------------------------------------------------------------------
        */

        function getNamedInputs(
            form,
            field
        ) {
            if (!form) {
                return [];
            }

            return Array.from(
                form.querySelectorAll(
                    `[name="${field}"]`
                )
            );
        }

        function setFormFieldValue(
            form,
            field,
            value
        ) {
            const inputs =
                getNamedInputs(
                    form,
                    field
                );

            if (!inputs.length) {
                return;
            }

            const checkbox =
                inputs.find(
                    function (input) {
                        return (
                            input.type
                            === 'checkbox'
                        );
                    }
                );

            if (checkbox) {
                checkbox.checked =
                    value === true
                    || value === 1
                    || value === '1'
                    || value === 'true';

                return;
            }

            const radioInputs =
                inputs.filter(
                    function (input) {
                        return (
                            input.type
                            === 'radio'
                        );
                    }
                );

            if (radioInputs.length) {
                radioInputs.forEach(
                    function (radio) {
                        radio.checked =
                            String(
                                radio.value
                            )
                            === String(
                                value ?? ''
                            );
                    }
                );

                return;
            }

            const input =
                inputs.find(
                    function (item) {
                        return (
                            item.type
                            !== 'hidden'
                        );
                    }
                )
                || inputs[0];

            input.value =
                value ?? '';
        }

        function resetPopupFields(
            form
        ) {
            if (!form) {
                return;
            }

            setFormFieldValue(
                form,
                'new_customer_only',
                false
            );

            setFormFieldValue(
                form,
                'show_as_popup',
                false
            );

            setFormFieldValue(
                form,
                'popup_badge',
                ''
            );

            setFormFieldValue(
                form,
                'popup_title',
                ''
            );

            setFormFieldValue(
                form,
                'popup_description',
                ''
            );

            setFormFieldValue(
                form,
                'popup_button_text',
                ''
            );

            setFormFieldValue(
                form,
                'popup_scroll_pixels',
                120
            );

            [
                'topbar_text',
                'topbar_applied_text',
                'topbar_button_text',
                'popup_apply_loading_text',
                'popup_applied_text',
            ].forEach(
                function (field) {
                    setFormFieldValue(
                        form,
                        field,
                        ''
                    );
                }
            );
        }

        function populateForm(
            form,
            coupon
        ) {
            if (!form || !coupon) {
                return;
            }

            const normalFields = [
                'brand_id',
                'code',
                'title',
                'discount_type',
                'discount_value',
                'max_discount_amount',
                'min_order_amount',
                'usage_limit',
                'starts_at',
                'expires_at',
                'status',
                'popup_badge',
                'popup_title',
                'popup_description',
                'popup_button_text',
                'popup_scroll_pixels',
                'topbar_text',
                'topbar_applied_text',
                'topbar_button_text',
                'popup_apply_loading_text',
                'popup_applied_text',
            ];

            normalFields.forEach(
                function (field) {
                    setFormFieldValue(
                        form,
                        field,
                        coupon[field] ?? ''
                    );
                }
            );

            setFormFieldValue(
                form,
                'new_customer_only',
                Boolean(
                    coupon
                        .new_customer_only
                )
            );

            setFormFieldValue(
                form,
                'show_as_popup',
                Boolean(
                    coupon.show_as_popup
                )
            );

            syncPopupToggleState(
                form
            );
        }

        function clearErrors(form) {
            if (!form) {
                return;
            }

            form
                .querySelectorAll(
                    '.brand-field-error'
                )
                .forEach(
                    function (item) {
                        item.textContent = '';
                    }
                );

            form
                .querySelectorAll(
                    'input, select, textarea'
                )
                .forEach(
                    function (item) {
                        item.classList.remove(
                            'brand-input-invalid'
                        );
                    }
                );
        }

        function findErrorOutput(
            form,
            field
        ) {
            if (!form) {
                return null;
            }

            return (
                form.querySelector(
                    `[data-error="${field}"]`
                )
                ||
                form.querySelector(
                    `.${field}_error`
                )
            );
        }

        function findVisibleField(
            form,
            field
        ) {
            const fields =
                getNamedInputs(
                    form,
                    field
                );

            return (
                fields.find(
                    function (input) {
                        return (
                            input.type
                            !== 'hidden'
                        );
                    }
                )
                || fields[0]
                || null
            );
        }

        function displayErrors(
            form,
            errors
        ) {
            if (!form) {
                return;
            }

            Object.entries(
                errors || {}
            ).forEach(
                function (
                    [field, messages]
                ) {
                    const errorOutput =
                        findErrorOutput(
                            form,
                            field
                        );

                    const input =
                        findVisibleField(
                            form,
                            field
                        );

                    const message =
                        Array.isArray(
                            messages
                        )
                            ? messages[0]
                            : messages;

                    if (errorOutput) {
                        errorOutput.textContent =
                            message || '';
                    }

                    input?.classList.add(
                        'brand-input-invalid'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Popup Toggle Logic
        |--------------------------------------------------------------------------
        */

        function syncPopupToggleState(
            form
        ) {
            if (!form) {
                return;
            }

            const newCustomerCheckbox =
                form.querySelector(
                    'input[type="checkbox"][name="new_customer_only"]'
                );

            const popupCheckbox =
                form.querySelector(
                    'input[type="checkbox"][name="show_as_popup"]'
                );

            if (
                popupCheckbox?.checked
                && newCustomerCheckbox
            ) {
                newCustomerCheckbox.checked =
                    true;
            }

            const popupFieldNames = [
                'popup_badge',
                'popup_title',
                'popup_description',
                'popup_button_text',
                'popup_scroll_pixels',
                'topbar_text',
                'topbar_applied_text',
                'topbar_button_text',
                'popup_apply_loading_text',
                'popup_applied_text',
            ];

            popupFieldNames.forEach(
                function (field) {
                    const input =
                        findVisibleField(
                            form,
                            field
                        );

                    if (!input) {
                        return;
                    }

                    input.disabled =
                        !popupCheckbox?.checked;

                    const wrapper =
                        input.closest(
                            '.brand-form-field'
                        );

                    wrapper?.classList.toggle(
                        'coupon-popup-field-disabled',
                        !popupCheckbox?.checked
                    );
                }
            );
        }

        function registerPopupToggleEvents(
            form
        ) {
            if (!form) {
                return;
            }

            const newCustomerCheckbox =
                form.querySelector(
                    'input[type="checkbox"][name="new_customer_only"]'
                );

            const popupCheckbox =
                form.querySelector(
                    'input[type="checkbox"][name="show_as_popup"]'
                );

            popupCheckbox?.addEventListener(
                'change',
                function () {
                    if (
                        popupCheckbox.checked
                        && newCustomerCheckbox
                    ) {
                        newCustomerCheckbox.checked =
                            true;
                    }

                    syncPopupToggleState(
                        form
                    );
                }
            );

            newCustomerCheckbox
                ?.addEventListener(
                    'change',
                    function () {
                        if (
                            !newCustomerCheckbox
                                .checked
                            && popupCheckbox
                                ?.checked
                        ) {
                            popupCheckbox.checked =
                                false;
                        }

                        syncPopupToggleState(
                            form
                        );
                    }
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Coupon Table
        |--------------------------------------------------------------------------
        */

        function discountLabel(
            coupon
        ) {
            if (
                coupon.discount_type
                === 'percentage'
            ) {
                const value =
                    Number(
                        coupon.discount_value
                        || 0
                    ).toLocaleString(
                        'en-BD',
                        {
                            maximumFractionDigits:
                                2,
                        }
                    );

                const cap =
                    coupon
                        .max_discount_amount
                        ? `
                            <small>
                                Max ৳${money(
                                    coupon
                                        .max_discount_amount
                                )}
                            </small>
                        `
                        : '';

                return `
                    ${value}%
                    ${cap}
                `;
            }

            return `
                ৳${money(
                    coupon.discount_value
                )}
            `;
        }

        function couponTypeBadges(
            coupon
        ) {
            const badges = [];

            if (
                coupon.new_customer_only
            ) {
                badges.push(`
                    <span class="coupon-rule-badge new-customer">
                        New Customer
                    </span>
                `);
            }

            if (
                coupon.show_as_popup
            ) {
                badges.push(`
                    <span class="coupon-rule-badge popup">
                        Popup
                    </span>
                `);
            }

            return badges.join('');
        }

        function createRow(
            coupon
        ) {
            const statusClass =
                coupon.status === 'Active'
                    ? 'active'
                    : 'inactive';

            const validity = `
                ${escapeHtml(
                    coupon.starts_at_label
                    || 'Anytime'
                )}
                -
                ${escapeHtml(
                    coupon.expires_at_label
                    || 'No expiry'
                )}
            `;

            return `
                <tr id="couponRow${coupon.id}">
                    <td>
                        <span class="brand-id">
                            #${coupon.id}
                        </span>
                    </td>

                    <td>
                        <div class="coupon-name-cell">
                            <strong>
                                ${escapeHtml(
                                    coupon.code
                                )}
                            </strong>

                            <small>
                                ${escapeHtml(
                                    coupon.title
                                    || 'Untitled coupon'
                                )}
                            </small>

                            <div class="coupon-rule-badges">
                                ${couponTypeBadges(
                                    coupon
                                )}
                            </div>
                        </div>
                    </td>

                    <td>
                        ${escapeHtml(
                            coupon.brand_name
                            || 'All Brands'
                        )}
                    </td>

                    <td>
                        ${discountLabel(
                            coupon
                        )}
                    </td>

                    <td>
                        ৳${money(
                            coupon
                                .min_order_amount
                        )}
                    </td>

                    <td>
                        ${Number(
                            coupon.used_count
                            || 0
                        )}
                        /
                        ${escapeHtml(
                            coupon.usage_limit
                            || 'Unlimited'
                        )}
                    </td>

                    <td>
                        <span
                            class="brand-status-badge ${statusClass}"
                        >
                            ${escapeHtml(
                                coupon.status
                            )}
                        </span>
                    </td>

                    <td>
                        <small>
                            ${validity}
                        </small>
                    </td>

                    <td>
                        <div class="brand-table-actions">
                            <button
                                type="button"
                                class="brand-action-button edit editCouponButton"
                                data-id="${coupon.id}"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="brand-action-button delete deleteCouponButton"
                                data-id="${coupon.id}"
                                data-code="${escapeHtml(
                                    coupon.code
                                )}"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function syncRow(coupon) {
            const existing =
                document.getElementById(
                    `couponRow${coupon.id}`
                );

            const html =
                createRow(coupon);

            if (existing) {
                existing.outerHTML =
                    html;

                return;
            }

            document
                .getElementById(
                    'emptyCouponRow'
                )
                ?.remove();

            document
                .getElementById(
                    'couponTableBody'
                )
                ?.insertAdjacentHTML(
                    'afterbegin',
                    html
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Submit Add/Edit
        |--------------------------------------------------------------------------
        */

        async function submitForm(
            form,
            url,
            button,
            loadingText,
            modalId
        ) {
            if (
                !form
                || !url
            ) {
                return;
            }

            clearErrors(form);

            setLoading(
                button,
                true,
                loadingText
            );

            try {
                const response =
                    await fetch(
                        url,
                        {
                            method: 'POST',

                            credentials:
                                'same-origin',

                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,

                                Accept:
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },

                            body:
                                new FormData(
                                    form
                                ),
                        }
                    );

                const data =
                    await parseResponse(
                        response
                    );

                syncRow(
                    data.coupon
                );

                closeModal(
                    modalId
                );

                form.reset();

                resetPopupFields(
                    form
                );

                syncPopupToggleState(
                    form
                );

                showToast(
                    data.message
                    || 'Coupon saved successfully.'
                );
            } catch (error) {
                if (
                    error.status === 422
                    && error.data?.errors
                ) {
                    displayErrors(
                        form,
                        error.data.errors
                    );

                    return;
                }

                if (
                    error.status === 419
                ) {
                    showToast(
                        'Your session has expired. Refresh the page and try again.',
                        'error'
                    );

                    return;
                }

                showToast(
                    error.data?.message
                    || 'Something went wrong.',
                    'error'
                );
            } finally {
                setLoading(
                    button,
                    false
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Add Coupon
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'openAddCouponModal'
            )
            ?.addEventListener(
                'click',
                function () {
                    addForm?.reset();

                    clearErrors(
                        addForm
                    );

                    resetPopupFields(
                        addForm
                    );

                    populateForm(
                        addForm,
                        {
                            brand_id: '',
                            code: '',
                            title: '',
                            discount_type:
                                'fixed',
                            discount_value:
                                '',
                            max_discount_amount:
                                '',
                            min_order_amount:
                                0,
                            usage_limit:
                                '',
                            starts_at:
                                '',
                            expires_at:
                                '',
                            status:
                                'Active',
                            new_customer_only:
                                false,
                            show_as_popup:
                                false,
                            popup_badge:
                                '',
                            popup_title:
                                '',
                            popup_description:
                                '',
                            popup_button_text:
                                '',
                            popup_scroll_pixels:
                                120,
                            topbar_text:
                                '',
                            topbar_applied_text:
                                '',
                            topbar_button_text:
                                '',
                            popup_apply_loading_text:
                                '',
                            popup_applied_text:
                                '',
                        }
                    );

                    openModal(
                        'addCouponModal'
                    );
                }
            );

        addForm?.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();

                submitForm(
                    addForm,
                    page.dataset.storeUrl,
                    addSubmit,
                    'Adding...',
                    'addCouponModal'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Edit Coupon
        |--------------------------------------------------------------------------
        */

        editForm?.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();

                const id =
                    document
                        .getElementById(
                            'edit_coupon_id'
                        )
                        ?.value;

                if (!id) {
                    showToast(
                        'Coupon ID is missing.',
                        'error'
                    );

                    return;
                }

                submitForm(
                    editForm,
                    routeUrl(
                        page.dataset
                            .updateUrl,
                        id
                    ),
                    editSubmit,
                    'Updating...',
                    'editCouponModal'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Edit/Delete/Open/Close Delegated Events
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            async function (event) {
                const editButton =
                    event.target.closest(
                        '.editCouponButton'
                    );

                const deleteButton =
                    event.target.closest(
                        '.deleteCouponButton'
                    );

                const closeButton =
                    event.target.closest(
                        '[data-close-modal]'
                    );

                if (closeButton) {
                    closeModal(
                        closeButton.dataset
                            .closeModal
                    );

                    return;
                }

                if (editButton) {
                    clearErrors(
                        editForm
                    );

                    setLoading(
                        editSubmit,
                        false
                    );

                    try {
                        const response =
                            await fetch(
                                routeUrl(
                                    page.dataset
                                        .showUrl,
                                    editButton
                                        .dataset.id
                                ),
                                {
                                    credentials:
                                        'same-origin',

                                    headers: {
                                        Accept:
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },
                                }
                            );

                        const data =
                            await parseResponse(
                                response
                            );

                        const idInput =
                            document
                                .getElementById(
                                    'edit_coupon_id'
                                );

                        if (idInput) {
                            idInput.value =
                                data.coupon.id;
                        }

                        populateForm(
                            editForm,
                            data.coupon
                        );

                        openModal(
                            'editCouponModal'
                        );
                    } catch (error) {
                        showToast(
                            error.data?.message
                            || 'Unable to load coupon.',
                            'error'
                        );
                    }

                    return;
                }

                if (deleteButton) {
                    deleteId =
                        deleteButton.dataset.id;

                    const text =
                        document
                            .getElementById(
                                'deleteCouponText'
                            );

                    if (text) {
                        text.textContent =
                            `Coupon ${deleteButton.dataset.code} will be removed permanently.`;
                    }

                    openModal(
                        'deleteCouponModal'
                    );
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Delete Coupon
        |--------------------------------------------------------------------------
        */

        deleteSubmit?.addEventListener(
            'click',
            async function () {
                if (!deleteId) {
                    return;
                }

                setLoading(
                    deleteSubmit,
                    true,
                    'Deleting...'
                );

                try {
                    const response =
                        await fetch(
                            routeUrl(
                                page.dataset
                                    .deleteUrl,
                                deleteId
                            ),
                            {
                                method:
                                    'DELETE',

                                credentials:
                                    'same-origin',

                                headers: {
                                    'X-CSRF-TOKEN':
                                        csrfToken,

                                    Accept:
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest',
                                },
                            }
                        );

                    const data =
                        await parseResponse(
                            response
                        );

                    document
                        .getElementById(
                            `couponRow${deleteId}`
                        )
                        ?.remove();

                    closeModal(
                        'deleteCouponModal'
                    );

                    showToast(
                        data.message
                        || 'Coupon deleted successfully.'
                    );

                    deleteId = null;
                } catch (error) {
                    if (
                        error.status
                        === 419
                    ) {
                        showToast(
                            'Your session has expired. Refresh the page and try again.',
                            'error'
                        );

                        return;
                    }

                    showToast(
                        error.data?.message
                        || 'Unable to delete coupon.',
                        'error'
                    );
                } finally {
                    setLoading(
                        deleteSubmit,
                        false
                    );
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Escape Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key
                    !== 'Escape'
                ) {
                    return;
                }

                document
                    .querySelectorAll(
                        '.brand-modal.open'
                    )
                    .forEach(
                        function (modal) {
                            closeModal(
                                modal.id
                            );
                        }
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Initial Setup
        |--------------------------------------------------------------------------
        */

        registerPopupToggleEvents(
            addForm
        );

        registerPopupToggleEvents(
            editForm
        );

        syncPopupToggleState(
            addForm
        );

        syncPopupToggleState(
            editForm
        );
    }
);

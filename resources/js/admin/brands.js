document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('brandCrudPage');

    if (!page) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const addForm = document.getElementById('addBrandForm');
    const editForm = document.getElementById('editBrandForm');

    const addSubmitButton = document.getElementById(
        'addBrandSubmitButton'
    );

    const editSubmitButton = document.getElementById(
        'editBrandSubmitButton'
    );

    const deleteButton = document.getElementById(
        'confirmDeleteBrandButton'
    );

    let toastTimer = null;

    function routeUrl(template, id) {
        return template.replace('__ID__', id);
    }

    function openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('brand-modal-open');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.brand-modal.open')) {
            document.body.classList.remove('brand-modal-open');
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('brandToast');
        const title = document.getElementById('brandToastTitle');
        const body = document.getElementById('brandToastMessage');
        const icon = document.getElementById('brandToastIcon');

        if (!toast || !title || !body || !icon) {
            return;
        }

        toast.classList.toggle('error', type === 'error');

        title.textContent = type === 'error' ? 'Error' : 'Success';
        icon.textContent = type === 'error' ? '!' : '✓';
        body.textContent = message;

        toast.classList.add('show');

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    function clearErrors(form) {
        form.querySelectorAll('.brand-field-error').forEach((element) => {
            element.textContent = '';
        });

        form.querySelectorAll(
            'input, select, textarea'
        ).forEach((element) => {
            element.classList.remove('brand-input-invalid');
        });
    }

    function displayErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const errorField = field.startsWith('offer_banners.')
                ? 'offer_banners'
                : field;

            const errorElement = form.querySelector(
                `.${errorField}_error`
            );

            const fieldElement = form.querySelector(
                `[name="${field}"], [name="${errorField}[]"]`
            );

            if (errorElement) {
                errorElement.textContent = Array.isArray(messages)
                    ? messages[0]
                    : messages;
            }

            fieldElement?.classList.add('brand-input-invalid');
        });
    }

    function setButtonLoading(button, loading, loadingText) {
        if (!button) {
            return;
        }

        if (loading) {
            button.dataset.originalText = button.textContent;
            button.textContent = loadingText;
            button.disabled = true;
            return;
        }

        button.textContent =
            button.dataset.originalText || button.textContent;

        button.disabled = false;
    }

    async function parseResponse(response) {
        const data = await response.json().catch(() => ({
            status: 'error',
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

    function escapeHtml(value = '') {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function createBrandRow(brand) {
        const logo = brand.logo_url
            ? `
                <img
                    src="${escapeHtml(brand.logo_url)}"
                    alt="${escapeHtml(brand.name)}"
                >
            `
            : `
                <span>
                    ${escapeHtml(brand.name.charAt(0).toUpperCase())}
                </span>
            `;

        const statusClass = brand.is_active
            ? 'active'
            : 'inactive';

        const statusText = brand.is_active
            ? 'Active'
            : 'Inactive';

        return `
            <tr id="brandRow${brand.id}">
                <td>
                    <span class="brand-id">#${brand.id}</span>
                </td>

                <td>
                    <div class="brand-name-cell">
                        <div class="brand-table-logo">
                            ${logo}
                        </div>

                        <div>
                            <strong class="brand-row-name">
                                ${escapeHtml(brand.name)}
                            </strong>

                            <small class="brand-row-email">
                                ${escapeHtml(
                                    brand.email || 'No email added'
                                )}
                            </small>
                        </div>
                    </div>
                </td>

                <td>
                    <code class="brand-slug">
                        ${escapeHtml(brand.slug)}
                    </code>
                </td>

                <td>
                    <div class="brand-color-cell">
                        <span
                            class="brand-color-dot"
                            style="background: ${escapeHtml(
                                brand.primary_color
                            )}"
                        ></span>

                        <span class="brand-row-color">
                            ${escapeHtml(brand.primary_color)}
                        </span>
                    </div>
                </td>

                <td>
                    <span class="brand-row-contact">
                        ${escapeHtml(
                            brand.contact_number || 'Not added'
                        )}
                    </span>
                </td>

                <td>
                    <span class="brand-status-badge ${statusClass}">
                        ${statusText}
                    </span>
                </td>

                <td>
                    <div class="brand-table-actions">
                        <a
                            href="${escapeHtml(brand.store_url)}"
                            target="_blank"
                            class="brand-action-button view"
                        >
                            View
                        </a>

                        <button
                            type="button"
                            class="brand-action-button edit editBrandButton"
                            data-id="${brand.id}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="brand-action-button delete deleteBrandButton"
                            data-id="${brand.id}"
                            data-name="${escapeHtml(brand.name)}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncBrandRow(brand) {
        const row = document.getElementById(
            `brandRow${brand.id}`
        );

        const html = createBrandRow(brand);

        if (row) {
            row.outerHTML = html;
            return;
        }

        document.getElementById('emptyBrandRow')?.remove();

        document
            .getElementById('brandTableBody')
            ?.insertAdjacentHTML('afterbegin', html);
    }

    function resetPreview(prefix) {
        ['logo', 'mobile_logo', 'favicon', 'offer_banners'].forEach((field) => {
            const preview = document.getElementById(
                `${prefix}_${field}_preview`
            );

            if (preview) {
                preview.innerHTML = '';
            }
        });
    }

    function showExistingImage(prefix, field, url) {
        const preview = document.getElementById(
            `${prefix}_${field}_preview`
        );

        if (!preview) {
            return;
        }

        preview.innerHTML = url
            ? `<img src="${escapeHtml(url)}" alt="${field}">`
            : '';
    }

    function showExistingBanners(prefix, urls = []) {
        const preview = document.getElementById(
            `${prefix}_offer_banners_preview`
        );

        if (!preview) {
            return;
        }

        preview.innerHTML = urls.length
            ? urls
                .map(
                    (url) => `
                        <img
                            src="${escapeHtml(url)}"
                            alt="Offer banner"
                        >
                    `
                )
                .join('')
            : '';
    }

    function populateEditForm(brand) {
        const fields = [
            'name',
            'slug',
            'primary_color',
            'secondary_color',
            'background_color',
            'button_color',
            'text_color',
            'font_family',
            'header_style',
            'footer_style',
            'contact_number',
            'email',
            'facebook_link',
            'instagram_link',
            'whatsapp_link',
            'address',
            'meta_title',
            'meta_description',
        ];

        fields.forEach((field) => {
            const element = document.getElementById(
                `edit_${field}`
            );

            if (!element) {
                return;
            }

            element.value = brand[field] ?? '';

            if (field.endsWith('_color')) {
                const textInput = document.querySelector(
                    `[data-color-target="edit_${field}"]`
                );

                if (textInput) {
                    textInput.value = brand[field] ?? '';
                }
            }
        });

        document.getElementById('edit_brand_id').value = brand.id;

        document.getElementById('edit_is_active').checked =
            Boolean(brand.is_active);

        showExistingImage('edit', 'logo', brand.logo_url);
        showExistingImage(
            'edit',
            'mobile_logo',
            brand.mobile_logo_url
        );
        showExistingImage(
            'edit',
            'favicon',
            brand.favicon_url
        );
        showExistingBanners(
            'edit',
            brand.offer_banner_urls || []
        );
    }

    document
        .getElementById('openAddBrandModal')
        ?.addEventListener('click', () => {
            addForm.reset();
            clearErrors(addForm);
            resetPreview('add');

            document.getElementById('add_is_active').checked = true;

            document
                .querySelectorAll(
                    '#addBrandForm .brand-color-text'
                )
                .forEach((input) => {
                    const target = document.getElementById(
                        input.dataset.colorTarget
                    );

                    input.value = target?.value || '';
                });

            openModal('addBrandModal');
        });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            closeModal(button.dataset.closeModal);
        });
    });

    document
        .getElementById('closeBrandToast')
        ?.addEventListener('click', () => {
            document
                .getElementById('brandToast')
                ?.classList.remove('show');
        });

    addForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearErrors(addForm);
        setButtonLoading(addSubmitButton, true, 'Adding...');

        try {
            const formData = new FormData(addForm);

            const response = await fetch(page.dataset.storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await parseResponse(response);

            syncBrandRow(data.brand);
            closeModal('addBrandModal');
            addForm.reset();

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    addForm,
                    error.data.errors || {}
                );

                return;
            }

            showToast(
                error.data?.message || 'Unable to add brand.',
                'error'
            );
        } finally {
            setButtonLoading(addSubmitButton, false);
        }
    });

    document
        .getElementById('brandTableBody')
        ?.addEventListener('click', async (event) => {
            const editButton = event.target.closest(
                '.editBrandButton'
            );

            const deleteBrandButton = event.target.closest(
                '.deleteBrandButton'
            );

            if (editButton) {
                clearErrors(editForm);
                editForm.reset();
                resetPreview('edit');

                const id = editButton.dataset.id;

                try {
                    editButton.disabled = true;

                    const response = await fetch(
                        routeUrl(page.dataset.showUrl, id),
                        {
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    );

                    const data = await parseResponse(response);

                    populateEditForm(data.brand);
                    openModal('editBrandModal');
                } catch (error) {
                    showToast(
                        error.data?.message ||
                            'Unable to load brand.',
                        'error'
                    );
                } finally {
                    editButton.disabled = false;
                }
            }

            if (deleteBrandButton) {
                document.getElementById(
                    'delete_brand_id'
                ).value = deleteBrandButton.dataset.id;

                document.getElementById(
                    'deleteBrandName'
                ).textContent = deleteBrandButton.dataset.name;

                openModal('deleteBrandModal');
            }
        });

    editForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearErrors(editForm);

        const id = document.getElementById(
            'edit_brand_id'
        ).value;

        if (!id) {
            showToast('Invalid brand selected.', 'error');
            return;
        }

        setButtonLoading(editSubmitButton, true, 'Updating...');

        try {
            const formData = new FormData(editForm);

            const response = await fetch(
                routeUrl(page.dataset.updateUrl, id),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                }
            );

            const data = await parseResponse(response);

            syncBrandRow(data.brand);
            closeModal('editBrandModal');

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    editForm,
                    error.data.errors || {}
                );

                return;
            }

            showToast(
                error.data?.message || 'Unable to update brand.',
                'error'
            );
        } finally {
            setButtonLoading(editSubmitButton, false);
        }
    });

    deleteButton.addEventListener('click', async () => {
        const id = document.getElementById(
            'delete_brand_id'
        ).value;

        if (!id) {
            showToast('Invalid brand selected.', 'error');
            return;
        }

        setButtonLoading(deleteButton, true, 'Deleting...');

        try {
            const response = await fetch(
                routeUrl(page.dataset.deleteUrl, id),
                {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                }
            );

            const data = await parseResponse(response);

            document.getElementById(`brandRow${id}`)?.remove();

            closeModal('deleteBrandModal');

            if (
                !document.querySelector(
                    '#brandTableBody tr'
                )
            ) {
                document.getElementById(
                    'brandTableBody'
                ).innerHTML = `
                    <tr id="emptyBrandRow">
                        <td colspan="7">
                            <div class="brand-empty-state">
                                <strong>No brands found</strong>
                                <span>
                                    Add your first brand to get started.
                                </span>
                            </div>
                        </td>
                    </tr>
                `;
            }

            showToast(data.message);
        } catch (error) {
            showToast(
                error.data?.message || 'Unable to delete brand.',
                'error'
            );
        } finally {
            setButtonLoading(deleteButton, false);
        }
    });

    document
        .querySelectorAll('.brand-color-input input[type="color"]')
        .forEach((colorInput) => {
            colorInput.addEventListener('input', () => {
                const textInput = document.querySelector(
                    `[data-color-target="${colorInput.id}"]`
                );

                if (textInput) {
                    textInput.value = colorInput.value;
                }
            });
        });

    document
        .querySelectorAll('.brand-color-text')
        .forEach((textInput) => {
            textInput.addEventListener('input', () => {
                const colorInput = document.getElementById(
                    textInput.dataset.colorTarget
                );

                if (
                    colorInput &&
                    /^#[0-9a-fA-F]{6}$/.test(textInput.value)
                ) {
                    colorInput.value = textInput.value;
                }
            });
        });

    document
        .querySelectorAll(
            '#addBrandForm input, #addBrandForm textarea, #addBrandForm select, ' +
            '#editBrandForm input, #editBrandForm textarea, #editBrandForm select'
        )
        .forEach((field) => {
            field.addEventListener('input', () => {
                field.classList.remove('brand-input-invalid');

                const form = field.closest('form');
                const error = form?.querySelector(
                    `.${field.name}_error`
                );

                if (error) {
                    error.textContent = '';
                }
            });
        });

    document
        .getElementById('add_name')
        ?.addEventListener('input', (event) => {
            const slugInput = document.getElementById('add_slug');

            if (!slugInput.dataset.manuallyEdited) {
                slugInput.value = event.target.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

    document
        .getElementById('add_slug')
        ?.addEventListener('input', (event) => {
            event.target.dataset.manuallyEdited = 'true';
        });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.brand-modal.open')
            .forEach((modal) => {
                closeModal(modal.id);
            });
    });
});

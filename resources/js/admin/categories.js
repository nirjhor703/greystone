document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('categoryCrudPage');

    if (!page) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const addForm = document.getElementById('addCategoryForm');
    const editForm = document.getElementById('editCategoryForm');

    const addSubmit = document.getElementById(
        'addCategorySubmitButton'
    );

    const editSubmit = document.getElementById(
        'editCategorySubmitButton'
    );

    const deleteSubmit = document.getElementById(
        'confirmDeleteCategoryButton'
    );

    let toastTimer;

    const routeUrl = (template, id) =>
        template.replace('__ID__', id);

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
        if (!form) {
            return;
        }

        form.querySelectorAll('.brand-field-error').forEach((item) => {
            item.textContent = '';
        });

        form.querySelectorAll(
            'input, select, textarea'
        ).forEach((item) => {
            item.classList.remove('brand-input-invalid');
        });
    }

    function displayErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const error = form.querySelector(
                `.${field}_error`
            );

            const input = form.querySelector(
                `[name="${field}"]`
            );

            if (error) {
                error.textContent = Array.isArray(messages)
                    ? messages[0]
                    : messages;
            }

            input?.classList.add('brand-input-invalid');
        });
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('categoryToast');
        const title = document.getElementById('categoryToastTitle');
        const body = document.getElementById('categoryToastMessage');
        const icon = document.getElementById('categoryToastIcon');

        toast.classList.toggle('error', type === 'error');
        title.textContent = type === 'error' ? 'Error' : 'Success';
        body.textContent = message;
        icon.textContent = type === 'error' ? '!' : '✓';

        toast.classList.add('show');

        clearTimeout(toastTimer);

        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    function setLoading(button, state, text) {
        if (!button) {
            return;
        }

        if (state) {
            button.dataset.originalText = button.textContent;
            button.textContent = text;
            button.disabled = true;
        } else {
            button.textContent =
                button.dataset.originalText || button.textContent;

            button.disabled = false;
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

    function escapeHtml(value = '') {
        const div = document.createElement('div');
        div.textContent = value ?? '';

        return div.innerHTML;
    }

    function createRow(category) {
        const image = category.image_url
            ? `<img src="${escapeHtml(category.image_url)}"
                    alt="${escapeHtml(category.name)}">`
            : `<span>${escapeHtml(
                category.name.charAt(0).toUpperCase()
            )}</span>`;

        const statusClass =
            category.status === 'Active'
                ? 'active'
                : 'inactive';

        return `
            <tr id="categoryRow${category.id}">
                <td>
                    <span class="brand-id">#${category.id}</span>
                </td>

                <td>
                    <div class="brand-name-cell">
                        <div class="brand-table-logo">
                            ${image}
                        </div>

                        <div>
                            <strong>${escapeHtml(category.name)}</strong>
                            <small>/${escapeHtml(category.slug)}</small>
                        </div>
                    </div>
                </td>

                <td>
                    ${escapeHtml(category.brand_name || '-')}
                </td>

                <td>
                    <code class="brand-slug">
                        ${escapeHtml(category.prefix)}
                    </code>
                </td>

                <td>
                    <span class="brand-status-badge ${statusClass}">
                        ${escapeHtml(category.status)}
                    </span>
                </td>

                <td>
                    ${escapeHtml(category.description || '-')}
                </td>

                <td>
                    <div class="brand-table-actions">
                        <button
                            type="button"
                            class="brand-action-button edit editCategoryButton"
                            data-id="${category.id}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="brand-action-button delete deleteCategoryButton"
                            data-id="${category.id}"
                            data-name="${escapeHtml(category.name)}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncRow(category) {
        const existing = document.getElementById(
            `categoryRow${category.id}`
        );

        const html = createRow(category);

        if (existing) {
            existing.outerHTML = html;
            return;
        }

        document.getElementById('emptyCategoryRow')?.remove();

        document
            .getElementById('categoryTableBody')
            ?.insertAdjacentHTML('afterbegin', html);
    }

    function setPreview(prefix, url) {
        const preview = document.getElementById(
            `${prefix}_image_preview`
        );

        if (!preview) {
            return;
        }

        preview.innerHTML = url
            ? `<img src="${escapeHtml(url)}" alt="Category image">`
            : '';
    }

    function populateEditForm(category) {
        [
            'brand_id',
            'name',
            'slug',
            'prefix',
            'status',
            'description',
        ].forEach((field) => {
            const input = document.getElementById(
                `edit_${field}`
            );

            if (input) {
                input.value = category[field] ?? '';
            }
        });

        document.getElementById('edit_category_id').value =
            category.id;

        setPreview('edit', category.image_url);
    }

    document
        .getElementById('openAddCategoryModal')
        ?.addEventListener('click', () => {
            addForm?.reset();
            clearErrors(addForm);

            document.getElementById('add_status').value =
                'Active';

            document.getElementById('add_slug')
                .dataset.manuallyEdited = '';

            setPreview('add', null);
            openModal('addCategoryModal');
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
            const response = await fetch(
                page.dataset.storeUrl,
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(addForm),
                }
            );

            const data = await parseResponse(response);

            syncRow(data.category);
            closeModal('addCategoryModal');
            addForm.reset();

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    addForm,
                    error.data.errors || {}
                );
            } else {
                showToast(
                    error.data?.message ||
                        'Unable to add category.',
                    'error'
                );
            }
        } finally {
            setLoading(addSubmit, false);
        }
    });

    document
        .getElementById('categoryTableBody')
        ?.addEventListener('click', async (event) => {
            const editButton = event.target.closest(
                '.editCategoryButton'
            );

            const deleteButton = event.target.closest(
                '.deleteCategoryButton'
            );

            if (editButton) {
                try {
                    editButton.disabled = true;

                    const response = await fetch(
                        routeUrl(
                            page.dataset.showUrl,
                            editButton.dataset.id
                        ),
                        {
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    );

                    const data = await parseResponse(response);

                    editForm?.reset();
                    clearErrors(editForm);
                    populateEditForm(data.category);

                    openModal('editCategoryModal');
                } catch (error) {
                    showToast(
                        error.data?.message ||
                            'Unable to load category.',
                        'error'
                    );
                } finally {
                    editButton.disabled = false;
                }
            }

            if (deleteButton) {
                document.getElementById(
                    'delete_category_id'
                ).value = deleteButton.dataset.id;

                document.getElementById(
                    'deleteCategoryName'
                ).textContent = deleteButton.dataset.name;

                openModal('deleteCategoryModal');
            }
        });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearErrors(editForm);

        const id = document.getElementById(
            'edit_category_id'
        ).value;

        setLoading(editSubmit, true, 'Updating...');

        try {
            const response = await fetch(
                routeUrl(page.dataset.updateUrl, id),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(editForm),
                }
            );

            const data = await parseResponse(response);

            syncRow(data.category);
            closeModal('editCategoryModal');

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    editForm,
                    error.data.errors || {}
                );
            } else {
                showToast(
                    error.data?.message ||
                        'Unable to update category.',
                    'error'
                );
            }
        } finally {
            setLoading(editSubmit, false);
        }
    });

    deleteSubmit?.addEventListener('click', async () => {
        const id = document.getElementById(
            'delete_category_id'
        ).value;

        setLoading(deleteSubmit, true, 'Deleting...');

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

            document
                .getElementById(`categoryRow${id}`)
                ?.remove();

            closeModal('deleteCategoryModal');
            showToast(data.message);
        } catch (error) {
            showToast(
                error.data?.message ||
                    'Unable to delete category.',
                'error'
            );
        } finally {
            setLoading(deleteSubmit, false);
        }
    });

    ['add', 'edit'].forEach((prefix) => {
        const name = document.getElementById(`${prefix}_name`);
        const slug = document.getElementById(`${prefix}_slug`);
        const code = document.getElementById(`${prefix}_prefix`);

        name?.addEventListener('input', (event) => {
            if (!slug.dataset.manuallyEdited) {
                slug.value = event.target.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        slug?.addEventListener('input', () => {
            slug.dataset.manuallyEdited = 'true';
        });

        code?.addEventListener('input', () => {
            code.value = code.value
                .replace(/[^a-zA-Z]/g, '')
                .toUpperCase()
                .slice(0, 5);
        });
    });

    document
        .getElementById('closeCategoryToast')
        ?.addEventListener('click', () => {
            document
                .getElementById('categoryToast')
                ?.classList.remove('show');
        });
});
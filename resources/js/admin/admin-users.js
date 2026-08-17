document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('adminUsersPage');

    if (!page) {
        return;
    }

    const currentUserIsRoot =
        page.dataset.currentRoot === '1';

    const rootPasscodeVerifyUrl =
        page.dataset.rootPasscodeVerifyUrl || '';

    let pendingRootForm = null;
    let pendingRootToggle = null;
    let pendingRootPasscodeAction = null;

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

    function fillForm(form, data) {
        if (!form) {
            return;
        }

        form
            .querySelectorAll('input, select, textarea, button')
            .forEach((input) => {
                input.disabled = false;
            });

        form.querySelector('[name="name"]').value = data.name || '';
        form.querySelector('[name="email"]').value = data.email || '';
        form.querySelector('[name="personal_phone"]').value =
            data.personalPhone || '';
        form.querySelector('[name="optional_phone"]').value =
            data.optionalPhone || '';
        form.querySelector('[name="role"]').value = data.role || 'admin';

        const passwordInput = form.querySelector('[name="password"]');
        const passwordConfirmationInput = form.querySelector(
            '[name="password_confirmation"]'
        );

        if (passwordInput) {
            passwordInput.value = '';
        }

        if (passwordConfirmationInput) {
            passwordConfirmationInput.value = '';
        }

        const activeInput = form.querySelector(
            'input[type="checkbox"][name="is_active"]'
        );

        if (activeInput) {
            activeInput.checked = data.active !== '0';
        }

        const rootInput = form.querySelector(
            'input[type="checkbox"][name="is_root_admin"]'
        );

        if (rootInput) {
            rootInput.checked = data.root === '1';
        }

        const passcodeInput = form.querySelector(
            '[data-root-passcode-input]'
        );

        if (passcodeInput) {
            passcodeInput.value = '';
        }

        form
            .querySelectorAll('input[name="permissions[]"]')
            .forEach((input) => {
                input.checked = (data.permissions || []).includes(
                    input.value
                );
            });

        syncFullAccessState(form);
        syncModuleSelectAllButtons(form);
        syncRootAdminState(form);
        applyPermissionLock(form, data);
    }

    function permissionInputs(form) {
        return Array.from(
            form?.querySelectorAll('input[name="permissions[]"]')
            || []
        );
    }

    function syncFullAccessState(form) {
        const fullAccess = form?.querySelector(
            '[data-admin-full-access]'
        );

        if (!fullAccess) {
            return;
        }

        const inputs = permissionInputs(form);

        fullAccess.checked =
            inputs.length > 0
            && inputs.every((input) => input.checked);
    }

    function syncModuleSelectAllButtons(form) {
        form
            ?.querySelectorAll('[data-admin-module-select-all]')
            .forEach((button) => {
                const module = button.dataset.adminModuleSelectAll;
                const inputs = permissionInputs(form).filter((input) =>
                    input.value.startsWith(`${module}.`)
                );

                const allChecked =
                    inputs.length > 0
                    && inputs.every((input) => input.checked);

                button.textContent = allChecked
                    ? 'Clear'
                    : 'Select All';
            });
    }

    function applyPermissionLock(form, data) {
        const isSelfPermissionLocked =
            data.self === '1'
            && data.root !== '1';

        const isRootTargetLocked =
            data.root === '1'
            && !currentUserIsRoot;

        const notice = form?.querySelector(
            '[data-admin-lock-notice]'
        );

        if (notice) {
            const rootMessage =
                'You cannot change anything in root user.';

            const message = isRootTargetLocked
                ? rootMessage
                : data.lockMessage || '';

            notice.hidden = !message;
            notice.textContent = message;
        }

        if (isRootTargetLocked) {
            form
                ?.querySelectorAll('input, select, textarea, button[type="submit"], [data-admin-module-select-all]')
                .forEach((input) => {
                    input.disabled = true;
                });

            form
                ?.querySelectorAll('[data-close-admin-modal]')
                .forEach((button) => {
                    button.disabled = false;
                });

            return;
        }

        [
            'input[type="checkbox"][name="is_active"]',
            '[data-admin-full-access]',
            '[data-admin-module-select-all]',
            'input[name="permissions[]"]',
        ].forEach((selector) => {
            form
                ?.querySelectorAll(selector)
                .forEach((input) => {
                    input.disabled = isSelfPermissionLocked;
                });
        });

        form
            ?.querySelectorAll('[name="name"], [name="email"], [name="password"], [name="password_confirmation"], [name="role"], [data-admin-root-toggle], button[type="submit"]')
            .forEach((input) => {
                input.disabled = false;
            });
    }

    function syncRootAdminState(form) {
        const rootInput = form?.querySelector(
            '[data-admin-root-toggle]'
        );

        if (!rootInput) {
            return;
        }

        const isRoot = rootInput.checked;
        const roleSelect = form.querySelector('[name="role"]');
        const activeInput = form.querySelector(
            'input[type="checkbox"][name="is_active"]'
        );

        if (isRoot) {
            if (roleSelect) {
                roleSelect.value = 'super_admin';
            }

            if (activeInput) {
                activeInput.checked = true;
            }

            permissionInputs(form).forEach((input) => {
                input.checked = false;
            });
        }

        [
            '[data-admin-full-access]',
            '[data-admin-module-select-all]',
            'input[name="permissions[]"]',
        ].forEach((selector) => {
            form
                .querySelectorAll(selector)
                .forEach((input) => {
                    input.disabled = isRoot;
                });
        });

        if (activeInput) {
            activeInput.disabled = isRoot;
        }

        syncFullAccessState(form);
        syncModuleSelectAllButtons(form);
    }

    function setRootPasscodeModalText(action) {
        const title = document.getElementById('rootPasscodeTitle');
        const description = document.getElementById(
            'rootPasscodeDescription'
        );
        const confirmButton = document.getElementById(
            'confirmRootPasscodeButton'
        );

        if (title) {
            title.textContent =
                action?.title || 'Root Admin Passcode';
        }

        if (description) {
            description.textContent =
                action?.description
                || 'Enter the root passcode before giving permanent root access.';
        }

        if (confirmButton) {
            confirmButton.textContent =
                action?.confirmText || 'OK';
        }
    }

    function openRootPasscodeModal(form, rootToggle, action = {}) {
        pendingRootForm = form;
        pendingRootToggle = rootToggle;
        pendingRootPasscodeAction = action;

        const input = document.getElementById('rootPasscodeInput');
        const error = document.getElementById('rootPasscodeError');

        if (input) {
            input.value = '';
        }

        if (error) {
            error.hidden = true;
        }

        setRootPasscodeModalText(action);
        openModal('rootPasscodeModal');

        window.setTimeout(() => {
            input?.focus();
        }, 80);
    }

    function closeRootPasscodeModal() {
        if (pendingRootToggle) {
            pendingRootToggle.checked = false;
        }

        const hiddenInput =
            pendingRootForm?.querySelector(
                '[data-root-passcode-input]'
            );

        if (hiddenInput) {
            hiddenInput.value = '';
        }

        syncRootAdminState(pendingRootForm);
        closeModal('rootPasscodeModal');

        pendingRootForm = null;
        pendingRootToggle = null;
        pendingRootPasscodeAction = null;
    }

    function clearRootPasscodePendingState() {
        if (pendingRootToggle) {
            pendingRootToggle.checked = false;
        }

        const hiddenInput =
            pendingRootForm?.querySelector(
                '[data-root-passcode-input]'
            );

        if (hiddenInput) {
            hiddenInput.value = '';
        }

        syncRootAdminState(pendingRootForm);

        pendingRootForm = null;
        pendingRootToggle = null;
        pendingRootPasscodeAction = null;
    }

    function acceptRootPasscodeModal() {
        closeModal('rootPasscodeModal');

        pendingRootForm = null;
        pendingRootToggle = null;
        pendingRootPasscodeAction = null;
    }

    document
        .querySelectorAll('[data-open-admin-modal]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('addAdminForm');

                fillForm(form, {
                    role: 'admin',
                    active: '1',
                    personalPhone: '',
                    optionalPhone: '',
                    self: '0',
                    root: '0',
                    lockMessage: '',
                    permissions: [],
                });

                openModal(button.dataset.openAdminModal);
            });
        });

    document
        .querySelectorAll('[data-close-admin-modal]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(button.dataset.closeAdminModal);
            });
        });

    document
        .querySelectorAll('[data-admin-user-form]')
        .forEach((form) => {
            form
                .querySelector('[data-admin-full-access]')
                ?.addEventListener('change', (event) => {
                    permissionInputs(form).forEach((input) => {
                        input.checked = event.target.checked;
                    });

                    syncModuleSelectAllButtons(form);
                });

            form
                .querySelectorAll('[data-admin-module-select-all]')
                .forEach((button) => {
                    button.addEventListener('click', () => {
                        const module =
                            button.dataset.adminModuleSelectAll;

                        const inputs = permissionInputs(form).filter(
                            (input) =>
                                input.value.startsWith(`${module}.`)
                        );

                        const shouldCheck =
                            !inputs.every((input) => input.checked);

                        inputs.forEach((input) => {
                            input.checked = shouldCheck;
                        });

                        syncFullAccessState(form);
                        syncModuleSelectAllButtons(form);
                    });
                });

            permissionInputs(form).forEach((input) => {
                input.addEventListener('change', () => {
                    syncFullAccessState(form);
                    syncModuleSelectAllButtons(form);
                });
            });

            form
                .querySelector('[data-admin-root-toggle]')
                ?.addEventListener('change', (event) => {
                    if (
                        event.target.checked
                        && currentUserIsRoot
                    ) {
                        event.target.checked = false;
                        openRootPasscodeModal(
                            form,
                            event.target,
                            {
                                title: 'Root Admin Passcode',
                                description:
                                    'Enter the root passcode before giving permanent root access.',
                                confirmText: 'OK',
                            }
                        );

                        return;
                    }

                    const passcodeInput =
                        form.querySelector(
                            '[data-root-passcode-input]'
                        );

                    if (passcodeInput) {
                        passcodeInput.value = '';
                    }

                    syncRootAdminState(form);
                });
        });

    document
        .querySelectorAll('[data-close-root-passcode-modal]')
        .forEach((button) => {
            button.addEventListener('click', closeRootPasscodeModal);
        });

    window.addEventListener(
        'admin-root-passcode-modal-closing',
        clearRootPasscodePendingState
    );

    document
        .getElementById('confirmRootPasscodeButton')
        ?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            const modalInput = document.getElementById(
                'rootPasscodeInput'
            );

            const error = document.getElementById(
                'rootPasscodeError'
            );

            const passcode =
                modalInput?.value.trim() || '';

            if (!passcode) {
                if (error) {
                    error.textContent = 'Passcode is required.';
                    error.hidden = false;
                }

                return;
            }

            if (!rootPasscodeVerifyUrl) {
                if (error) {
                    error.textContent =
                        'Passcode verification is not available.';
                    error.hidden = false;
                }

                return;
            }

            if (error) {
                error.hidden = true;
            }

            button.disabled = true;
            const originalButtonText = button.textContent;
            button.textContent = 'Checking...';

            try {
                const response = await fetch(rootPasscodeVerifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        root_admin_passcode: passcode,
                    }),
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));

                    throw new Error(
                        data.message
                        || 'Root admin passcode is incorrect.'
                    );
                }
            } catch (validationError) {
                if (pendingRootToggle) {
                    pendingRootToggle.checked = false;
                }

                const hiddenInput =
                    pendingRootForm?.querySelector(
                        '[data-root-passcode-input]'
                    );

                if (hiddenInput) {
                    hiddenInput.value = '';
                }

                syncRootAdminState(pendingRootForm);

                if (error) {
                    error.textContent = validationError.message;
                    error.hidden = false;
                }

                button.disabled = false;
                button.textContent = originalButtonText;

                return;
            }

            if (pendingRootPasscodeAction?.onSuccess) {
                pendingRootPasscodeAction.onSuccess(passcode);
                acceptRootPasscodeModal();

                button.disabled = false;
                button.textContent = originalButtonText;

                return;
            }

            const hiddenInput =
                pendingRootForm?.querySelector(
                    '[data-root-passcode-input]'
                );

            if (hiddenInput) {
                hiddenInput.value = passcode;
            }

            if (pendingRootToggle) {
                pendingRootToggle.checked = true;
            }

            syncRootAdminState(pendingRootForm);
            acceptRootPasscodeModal();

            button.disabled = false;
            button.textContent = originalButtonText;
        });

    document
        .querySelectorAll('.editAdminButton')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('editAdminForm');

                if (!form) {
                    return;
                }

                const fillAndOpenEditModal = (passcode = '') => {
                    form.action = button.dataset.updateUrl;

                    fillForm(form, {
                        name: button.dataset.name,
                        email: button.dataset.email,
                        personalPhone:
                            button.dataset.personalPhone || '',
                        optionalPhone:
                            button.dataset.optionalPhone || '',
                        role: button.dataset.role,
                        active: button.dataset.active,
                        self: button.dataset.self,
                        root: button.dataset.root,
                        lockMessage:
                            button.dataset.lockMessage || '',
                        permissions: JSON.parse(
                            button.dataset.permissions || '[]'
                        ),
                    });

                    const hiddenInput = form.querySelector(
                        '[data-root-passcode-input]'
                    );

                    if (hiddenInput) {
                        hiddenInput.value = passcode;
                    }

                    openModal('editAdminModal');
                };

                if (
                    button.dataset.root === '1'
                    && button.dataset.self === '1'
                ) {
                    openRootPasscodeModal(null, null, {
                        title: 'Edit Root Account?',
                        description:
                            'Enter the root passcode before editing your root account.',
                        confirmText: 'Continue',
                        onSuccess: fillAndOpenEditModal,
                    });

                    return;
                }

                fillAndOpenEditModal();
            });
        });

    document
        .querySelectorAll('.deleteAdminButton')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('deleteAdminForm');
                const message = document.getElementById(
                    'deleteAdminMessage'
                );
                const hiddenInput = form?.querySelector(
                    '[data-delete-root-passcode-input]'
                );

                if (!form) {
                    return;
                }

                const openDeleteModal = (passcode = '') => {
                    form.action = button.dataset.deleteUrl;

                    if (hiddenInput) {
                        hiddenInput.value = passcode;
                    }

                    if (message) {
                        message.textContent =
                            `${button.dataset.name} will lose dashboard access permanently.`;
                    }

                    openModal('deleteAdminModal');
                };

                if (
                    button.dataset.root === '1'
                    && button.dataset.self === '1'
                ) {
                    openRootPasscodeModal(null, null, {
                        title: 'Delete Root Account?',
                        description:
                            'Enter the root passcode before deleting your own root account.',
                        confirmText: 'Continue',
                        onSuccess: openDeleteModal,
                    });

                    return;
                }

                openDeleteModal();
            });
        });
});

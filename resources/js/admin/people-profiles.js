document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('peopleProfileCrudPage');

    if (!page) {
        return;
    }

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

    function fillField(prefix, name, value) {
        const field = document.getElementById(`${prefix}_${name}`);

        if (!field) {
            return;
        }

        if (field.type === 'checkbox') {
            field.checked = Boolean(value);
            return;
        }

        field.value = value ?? '';
    }

    function syncRoleFields(prefix) {
        const role = document.getElementById(`${prefix}_profile_type`)?.value;
        const adminRole = document.getElementById(`${prefix}_admin_role`);

        if (adminRole) {
            adminRole.value = role === 'super_admin' ? 'super_admin' : 'admin';
        }
    }

    function syncPermissionPanel(form) {
        const toggle = form?.querySelector('[data-permission-override-toggle]');
        const panel = form?.querySelector('[data-permission-override-panel]');

        if (panel) {
            panel.hidden = !toggle?.checked;
        }
    }

    function rolePayload(id) {
        const payload = document.getElementById(`peopleRoleData${id}`);

        if (!payload) {
            return null;
        }

        return JSON.parse(payload.textContent);
    }

    function syncRolePermissionSelects() {
        const permissions = [...document.querySelectorAll('[data-role-permission]')];
        const allToggle = document.querySelector('[data-role-select-all]');

        document.querySelectorAll('[data-role-module-select]').forEach((toggle) => {
            const modulePermissions = permissions.filter((input) => input.dataset.roleModule === toggle.dataset.roleModuleSelect);
            toggle.checked = modulePermissions.length > 0 && modulePermissions.every((input) => input.checked);
        });

        if (allToggle) {
            allToggle.checked = permissions.length > 0 && permissions.every((input) => input.checked);
        }
    }

    function fillRoleBuilder(role = null) {
        const form = document.getElementById('peopleRoleBuilderForm');
        const method = document.getElementById('peopleRoleBuilderMethod');
        const selector = document.getElementById('peopleRoleSelector');
        const saveButton = document.getElementById('savePeopleRoleButton');

        if (!form) {
            return;
        }

        const permissions = role?.default_permissions || [];

        form.action = role ? routeUrl(page.dataset.roleUpdateUrl, role.id) : page.dataset.roleStoreUrl;
        method.value = role ? 'PUT' : '';
        method.disabled = !role;
        selector.value = role?.id || '';
        document.getElementById('peopleRoleName').value = role?.name || '';
        document.getElementById('peopleRoleSortOrder').value = role?.sort_order ?? 0;
        document.getElementById('peopleRoleIsActive').checked = role ? Boolean(role.is_active) : true;
        saveButton.textContent = role ? 'Save Role' : 'Add Role';

        form.querySelectorAll('[data-role-permission]').forEach((input) => {
            input.checked = permissions.includes(input.value);
        });

        syncRolePermissionSelects();
    }

    document
        .getElementById('openAddPeopleProfileModal')
        ?.addEventListener('click', () => {
            const form = document.getElementById('addPeopleProfileForm');
            form?.reset();
            form?.querySelectorAll('input[name="admin_permissions[]"]').forEach((input) => {
                input.checked = false;
            });
            syncRoleFields('add_people_profile');
            syncPermissionPanel(form);
            openModal('addPeopleProfileModal');
        });

    document
        .getElementById('openPeopleTypeModal')
        ?.addEventListener('click', () => {
            fillRoleBuilder();
            openModal('peopleTypeModal');
        });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            closeModal(button.dataset.closeModal);
        });
    });

    document.querySelectorAll('.editPeopleProfileButton').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.profileId;
            const payload = document.getElementById(`peopleProfileData${id}`);

            if (!payload) {
                return;
            }

            const profile = JSON.parse(payload.textContent);
            const form = document.getElementById('editPeopleProfileForm');

            form.action = routeUrl(page.dataset.updateUrl, id);
            form.querySelectorAll('input[name="admin_permissions[]"]').forEach((input) => {
                input.checked = (profile.admin_permissions || []).includes(input.value);
            });
            fillField('edit_people_profile', 'admin_password', '');
            fillField('edit_people_profile', 'admin_password_confirmation', '');
            fillField('edit_people_profile', 'root_admin_passcode', '');

            [
                'profile_type',
                'name',
                'title',
                'email',
                'phone',
                'optional_phone',
                'address',
                'emergency_contact',
                'nid_or_document',
                'joining_date',
                'member_id',
                'referrer_id',
                'user_id',
                'payment_type',
                'salary_type',
                'salary_amount',
                'commission_rate',
                'gift_balance',
                'admin_role',
                'permission_override_enabled',
                'referral_enabled',
                'role_notes',
                'sort_order',
                'story',
                'tax_note',
                'is_active',
            ].forEach((field) => fillField('edit_people_profile', field, profile[field]));

            syncRoleFields('edit_people_profile');
            syncPermissionPanel(form);
            openModal('editPeopleProfileModal');
        });
    });

    ['add_people_profile', 'edit_people_profile'].forEach((prefix) => {
        const form = document.getElementById(`${prefix.startsWith('add') ? 'add' : 'edit'}PeopleProfileForm`);

        document
            .getElementById(`${prefix}_profile_type`)
            ?.addEventListener('change', () => syncRoleFields(prefix));

        form
            ?.querySelector('[data-permission-override-toggle]')
            ?.addEventListener('change', () => syncPermissionPanel(form));

        syncRoleFields(prefix);
        syncPermissionPanel(form);
    });

    document.getElementById('resetPeopleRoleBuilder')?.addEventListener('click', () => fillRoleBuilder());

    document.getElementById('peopleRoleSelector')?.addEventListener('change', (event) => {
        const role = event.target.value ? rolePayload(event.target.value) : null;
        fillRoleBuilder(role);
    });

    document.querySelectorAll('.editPeopleRoleButton').forEach((button) => {
        button.addEventListener('click', () => {
            const role = rolePayload(button.dataset.roleId);
            fillRoleBuilder(role);
            document.getElementById('peopleRoleBuilderForm')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelector('[data-role-select-all]')?.addEventListener('change', (event) => {
        document.querySelectorAll('[data-role-permission]').forEach((input) => {
            input.checked = event.target.checked;
        });
        syncRolePermissionSelects();
    });

    document.querySelectorAll('[data-role-module-select]').forEach((toggle) => {
        toggle.addEventListener('change', () => {
            document.querySelectorAll(`[data-role-permission][data-role-module="${toggle.dataset.roleModuleSelect}"]`).forEach((input) => {
                input.checked = toggle.checked;
            });
            syncRolePermissionSelects();
        });
    });

    document.querySelectorAll('[data-role-permission]').forEach((input) => {
        input.addEventListener('change', syncRolePermissionSelects);
    });
});

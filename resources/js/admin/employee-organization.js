document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.querySelector('.employee-dashboard-page');
    if (!dashboard) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const urls = {
        departments: '/admin/employee-organization/departments',
        people: '/admin/employee-organization/people',
        directory: dashboard.querySelector('[data-directory-url]')?.dataset.directoryUrl || '/admin/employee-directory',
    };
    const directoryPanel = dashboard.querySelector('[data-employee-directory-panel]');
    const organizationPanel = dashboard.querySelector('[data-employee-organization-panel]');
    const dutyModal = dashboard.querySelector('[data-employee-duty-modal]');
    let departments = [];
    let selectedPerson = null;
    let activeDesignation = null;
    let confirmAction = null;

    const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const collection = (data) => Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
    const jsonOptions = (method, body) => ({ method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });

    async function request(url, options = {}) {
        const response = await fetch(url, { ...options, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) } });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Unable to complete request.');
        return data;
    }

    function showModal(selector) { const modal = dashboard.querySelector(selector); if (modal) { modal.hidden = false; modal.querySelector('input, select, textarea, button')?.focus(); } return modal; }
    function closeModals() { dashboard.querySelectorAll('.employee-form-modal, .employee-duty-modal').forEach((modal) => { modal.hidden = true; }); }
    function closeChildModal() { dashboard.querySelectorAll('.employee-form-modal').forEach((modal) => { modal.hidden = true; }); }
    function closeTopModal() { const child = [...dashboard.querySelectorAll('.employee-form-modal')].find((modal) => !modal.hidden); if (child) { child.hidden = true; return; } if (dutyModal && !dutyModal.hidden) dutyModal.hidden = true; }
    function openPanel(panel) { directoryPanel.hidden = panel !== directoryPanel; organizationPanel.hidden = panel !== organizationPanel; }
    function setError(selector, message = '') { const node = dashboard.querySelector(selector); if (node) { node.textContent = message; node.hidden = !message; } }
    function notify(message, type = 'success') { window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } })); }

    async function loadDepartments() {
        departments = collection(await request(urls.departments));
        const departmentSelect = dashboard.querySelector('[data-directory-department]');
        if (departmentSelect) departmentSelect.innerHTML = '<option value="">All departments</option>' + departments.map((item) => `<option value="${item.id}">${escape(item.name)}</option>`).join('');
        const designationSelect = dashboard.querySelector('[data-directory-designation]');
        if (designationSelect) designationSelect.innerHTML = '<option value="">All designations</option>' + departments.flatMap((item) => item.designations || []).map((item) => `<option value="${item.id}">${escape(item.name)}</option>`).join('');
    }

    function renderPeople(people) {
        const target = dashboard.querySelector('[data-assignment-people]');
        target.innerHTML = people.length ? people.map((person) => `<button type="button" class="employee-person-option" data-person-id="${person.id}"><strong>${escape(person.name)}</strong><span>${escape(person.email || person.phone || 'No contact')}</span><small>${person.is_employee ? 'Assigned employee' : 'Not assigned'}</small></button>`).join('') : '<span class="employee-overview-empty">No matching people found.</span>';
    }

    async function loadPeople(search = '') { renderPeople(collection(await request(`${urls.people}?search=${encodeURIComponent(search)}`))); }

    function refreshDesignations(selected = '') {
        const departmentId = dashboard.querySelector('[data-assignment-department]').value;
        const designation = dashboard.querySelector('[data-assignment-designation]');
        const department = departments.find((item) => String(item.id) === String(departmentId));
        const options = (department?.designations || []).filter((item) => item.is_active !== false);
        designation.innerHTML = '<option value="">Choose designation</option>' + options.map((item) => `<option value="${item.id}">${escape(item.name)}</option>`).join('');
        designation.disabled = !departmentId;
        if (options.some((item) => String(item.id) === String(selected))) designation.value = selected;
    }

    function fillAssignmentOptions(person = null) {
        const department = dashboard.querySelector('[data-assignment-department]');
        department.innerHTML = '<option value="">Choose department</option>' + departments.filter((item) => item.is_active !== false).map((item) => `<option value="${item.id}">${escape(item.name)}</option>`).join('');
        department.value = person?.department?.id || '';
        refreshDesignations(person?.designation?.id || '');
    }

    async function openAssignment(person = null) {
        selectedPerson = person;
        await loadDepartments();
        const modal = showModal('[data-employee-assignment-modal]');
        modal.querySelector('[data-assignment-form]').reset();
        modal.querySelector('[data-assignment-person]').value = person?.id || '';
        modal.querySelector('[data-assignment-selected]').hidden = !person;
        modal.querySelector('[data-assignment-selected]').innerHTML = person ? `<strong>${escape(person.name)}</strong><span>${escape(person.email || person.phone || '')}</span>` : '';
        modal.querySelector('[data-assignment-submit]').textContent = person?.is_employee ? 'Update Assignment' : 'Assign Employee';
        modal.querySelector('[data-remove-assignment]').hidden = !person?.is_employee;
        fillAssignmentOptions(person);
        setError('[data-assignment-error]');
        if (!person) loadPeople().catch((error) => setError('[data-assignment-error]', error.message));
    }

    function renderDirectory(data) {
        const target = dashboard.querySelector('[data-directory-results]');
        const profiles = collection(data);
        const groups = profiles.reduce((map, profile) => { const key = profile.department?.id || 'unassigned'; (map[key] ||= { name: profile.department?.name || 'Unassigned', rows: [] }).rows.push(profile); return map; }, {});
        target.innerHTML = Object.values(groups).length ? Object.values(groups).map((group) => `<section class="employee-directory-group"><h4>${escape(group.name)} <small>${group.rows.length} Employees</small></h4><div class="employee-directory-grid">${group.rows.map((profile) => `<article class="employee-directory-card"><div><strong>${escape(profile.designation?.name || 'No designation')}</strong><span>${escape(profile.name)}</span><small>${escape(profile.phone || profile.email || 'No phone available')}</small></div><span class="employee-status-pill">Active</span><div class="employee-directory-actions"><button type="button" data-edit-assignment="${profile.id}">Edit Assignment</button><button type="button" data-directory-duties="${profile.designation?.id || ''}" data-directory-department-name="${escape(group.name)}">View Duties</button></div></article>`).join('')}</div></section>`).join('') : '<div class="employee-empty-state"><strong>No employees assigned yet.</strong><span>Assign an existing person to see them in the directory.</span><button type="button" class="brand-primary-button" data-open-employee-assignment>Assign Existing Employee</button></div>';
    }

    async function loadDirectory() {
        const params = new URLSearchParams();
        const search = dashboard.querySelector('[data-directory-search]')?.value.trim();
        const department = dashboard.querySelector('[data-directory-department]')?.value;
        const designation = dashboard.querySelector('[data-directory-designation]')?.value;
        if (search) params.set('search', search); if (department) params.set('department_id', department); if (designation) params.set('designation_id', designation);
        const target = dashboard.querySelector('[data-directory-results]'); target.innerHTML = '<p class="employee-overview-empty">Loading employees…</p>';
        try { renderDirectory(await request(`${urls.directory}?${params}`)); } catch (error) { target.innerHTML = `<p class="employee-overview-empty">${escape(error.message)}</p>`; }
    }

    function renderOrganization() {
        const target = dashboard.querySelector('[data-employee-organization-results]');
        const selected = dashboard.querySelector('[data-organization-department]')?.value || '';
        const query = dashboard.querySelector('[data-organization-search]')?.value.trim().toLowerCase() || '';
        const visibleDepartments = departments.filter((department) => {
            if (selected && String(department.id) !== String(selected)) return false;
            if (!query) return true;
            return department.name.toLowerCase().includes(query) || (department.designations || []).some((designation) => designation.name.toLowerCase().includes(query));
        });
        target.innerHTML = visibleDepartments.map((department) => `<section class="employee-organization-group"><div class="employee-organization-heading"><div><h4>${escape(department.name)}</h4><p>${escape(department.description || 'No description')}</p></div><button type="button" data-edit-department="${department.id}">Edit</button></div><div class="employee-designation-list">${(department.designations || []).filter((designation) => !query || department.name.toLowerCase().includes(query) || designation.name.toLowerCase().includes(query)).map((designation) => `<div class="employee-designation-row"><span><strong>${escape(designation.name)}</strong><small>${designation.is_active === false ? 'Inactive' : 'Active'}</small></span><div><button type="button" data-edit-designation="${designation.id}">Edit</button><button type="button" data-view-duties="${designation.id}" data-duty-name="${escape(designation.name)}" data-duty-department="${escape(department.name)}">View Duties</button></div></div>`).join('') || '<p class="employee-overview-empty">No designations match this search.</p>'}</div></section>`).join('') || '<p class="employee-overview-empty">No departments match this search.</p>';
    }

    async function loadOrganization() { try { await loadDepartments(); const select = dashboard.querySelector('[data-organization-department]'); if (select) select.innerHTML = '<option value="">All departments</option>' + departments.map((item) => `<option value="${item.id}">${escape(item.name)}</option>`).join(''); renderOrganization(); } catch (error) { dashboard.querySelector('[data-employee-organization-results]').innerHTML = `<p class="employee-overview-empty">${escape(error.message)}</p>`; } }

    async function loadDuties(id, name = '', department = '') {
        activeDesignation = id; dutyModal.hidden = false; dutyModal.querySelector('[data-duty-title]').textContent = name; dutyModal.querySelector('[data-duty-department]').textContent = department;
        const target = dutyModal.querySelector('[data-duty-list]'); target.innerHTML = '<p>Loading responsibilities…</p>';
        try { const data = await request(`/admin/employee-organization/designations/${id}/responsibilities`); const duties = data.responsibilities || data.data || data; target.innerHTML = Array.isArray(duties) && duties.length ? duties.map((duty, index) => `<div class="employee-duty-row"><span>${index + 1}</span><div class="employee-duty-description">${escape(duty.description)}</div><button type="button" data-edit-duty="${duty.id}" data-duty-description-value="${escape(duty.description)}" data-duty-order-value="${Number(duty.sort_order || index)}">Edit</button><button type="button" data-delete-duty="${duty.id}">Remove</button></div>`).join('') : '<p class="employee-overview-empty">No responsibilities added yet.</p>'; } catch (error) { target.innerHTML = `<p>${escape(error.message)}</p>`; }
    }

    function openOrgForm(type, item = {}) {
        const modal = showModal('[data-employee-org-modal]');
        modal.querySelector('[data-org-modal-title]').textContent = `${item.id ? 'Edit' : 'Add'} ${type}`;
        const fields = type === 'Department' ? `<label class="employee-form-field"><span>Department Name</span><input name="name" required value="${escape(item.name)}"></label><label class="employee-form-field"><span>Description</span><textarea name="description">${escape(item.description)}</textarea></label>` : `<label class="employee-form-field"><span>Department</span><select name="department_id" required>${departments.map((department) => `<option value="${department.id}" ${String(department.id) === String(item.department_id) ? 'selected' : ''}>${escape(department.name)}</option>`).join('')}</select></label><label class="employee-form-field"><span>Designation Name</span><input name="name" required value="${escape(item.name)}"></label><label class="employee-form-field"><span>Description</span><textarea name="description">${escape(item.description)}</textarea></label>`;
        modal.querySelector('[data-org-fields]').innerHTML = `${fields}<label class="employee-form-field"><span>Sort Order</span><input type="number" min="0" name="sort_order" value="${Number(item.sort_order || 0)}"></label><label class="employee-form-check"><input type="checkbox" name="is_active" ${item.is_active === false ? '' : 'checked'}> Active</label>`;
        modal.querySelector('[data-org-form]').dataset.type = type; modal.querySelector('[data-org-form]').dataset.id = item.id || '';
    }

    function askConfirmation(title, message, action, html = '') { const modal = showModal('[data-employee-confirm-modal]'); modal.querySelector('[data-confirm-eyebrow]').textContent = title.startsWith('Remove') ? 'Please Confirm' : 'Employee Management'; modal.querySelector('[data-confirm-title]').textContent = title; modal.querySelector('[data-confirm-message]').innerHTML = html || `<span>${escape(message)}</span>`; confirmAction = action; }

    dashboard.addEventListener('click', async (event) => {
        try {
            const assignment = event.target.closest('[data-open-employee-assignment]'); if (assignment) { await openAssignment(); return; }
            const removeAssignment = event.target.closest('[data-remove-assignment]'); if (removeAssignment && selectedPerson) { askConfirmation('Remove Employee Assignment', 'This keeps the person profile but removes their employee assignment.', async () => { await request(`/admin/employee-organization/people/${selectedPerson.id}/assignment`, jsonOptions('PATCH', { is_employee: false, primary_department_id: null, primary_designation_id: null })); window.location.reload(); }); return; }
            const openDirectory = event.target.closest('[data-open-employee-directory]'); if (openDirectory) { openPanel(directoryPanel); await loadDepartments(); await loadDirectory(); return; }
            const openOrganization = event.target.closest('[data-open-employee-organization]'); if (openOrganization) { openPanel(organizationPanel); await loadOrganization(); return; }
            const person = event.target.closest('[data-person-id]'); if (person) { const people = await request(`${urls.people}?search=${encodeURIComponent(person.querySelector('strong').textContent)}`); await openAssignment(collection(people).find((item) => String(item.id) === person.dataset.personId)); return; }
            const editAssignment = event.target.closest('[data-edit-assignment]'); if (editAssignment) { const people = await request(`${urls.people}?search=`); await openAssignment(collection(people).find((item) => String(item.id) === editAssignment.dataset.editAssignment)); return; }
            if (event.target.closest('[data-add-department]')) { openOrgForm('Department'); return; }
            const editDepartment = event.target.closest('[data-edit-department]'); if (editDepartment) { openOrgForm('Department', departments.find((item) => String(item.id) === editDepartment.dataset.editDepartment)); return; }
            if (event.target.closest('[data-add-designation]')) { openOrgForm('Designation', { department_id: departments[0]?.id }); return; }
            const editDesignation = event.target.closest('[data-edit-designation]'); if (editDesignation) { openOrgForm('Designation', departments.flatMap((item) => item.designations || []).find((item) => String(item.id) === editDesignation.dataset.editDesignation)); return; }
            const duty = event.target.closest('[data-view-duties], [data-directory-duties]'); if (duty) { await loadDuties(duty.dataset.viewDuties || duty.dataset.directoryDuties, duty.dataset.dutyName || '', duty.dataset.dutyDepartment || duty.dataset.directoryDepartmentName || ''); return; }
            if (event.target.closest('[data-close-employee-panel]')) { openPanel(null); return; }
            if (event.target.closest('[data-close-duty-modal]')) { dutyModal.hidden = true; return; }
            if (event.target.closest('[data-close-employee-modal]')) { closeChildModal(); return; }
            const print = event.target.closest('[data-print-duty]'); if (print && activeDesignation) { askConfirmation('Print Job Description', 'Add an optional candidate name before printing.', () => { const name = dashboard.querySelector('[data-print-name]')?.value || ''; window.open(`/admin/employee-organization/designations/${activeDesignation}/print?name=${encodeURIComponent(name)}`, '_blank', 'noopener'); }, '<label class="employee-form-field">Candidate Name (Optional)<input data-print-name></label>'); return; }
            const addDuty = event.target.closest('[data-add-duty]'); if (addDuty && activeDesignation) { askConfirmation('Add Responsibility', 'Enter the responsibility description and optional display order.', async () => { const description = dashboard.querySelector('[data-new-duty-description]')?.value || ''; if (!description.trim()) throw new Error('Responsibility description is required.'); await request(`/admin/employee-organization/designations/${activeDesignation}/responsibilities`, jsonOptions('POST', { description, sort_order: dashboard.querySelector('[data-new-duty-order]')?.value || 0, is_active: 1 })); await loadDuties(activeDesignation, dutyModal.querySelector('[data-duty-title]').textContent, dutyModal.querySelector('[data-duty-department]').textContent); }, '<label class="employee-form-field">Responsibility Description<textarea data-new-duty-description required></textarea></label><label class="employee-form-field">Display Order<input type="number" min="0" value="0" data-new-duty-order></label>'); return; }
            const editDuty = event.target.closest('[data-edit-duty]'); if (editDuty && activeDesignation) { const id = editDuty.dataset.editDuty; askConfirmation('Edit Responsibility', 'Update the responsibility and display order.', async () => { const description = dashboard.querySelector('[data-edit-duty-description]')?.value || ''; if (!description.trim()) throw new Error('Responsibility description is required.'); await request(`/admin/employee-organization/responsibilities/${id}`, jsonOptions('PUT', { description, sort_order: dashboard.querySelector('[data-edit-duty-order]')?.value || 0, is_active: 1 })); await loadDuties(activeDesignation, dutyModal.querySelector('[data-duty-title]').textContent, dutyModal.querySelector('[data-duty-department]').textContent); }, `<label class="employee-form-field">Responsibility Description<textarea data-edit-duty-description required>${escape(editDuty.dataset.dutyDescriptionValue)}</textarea></label><label class="employee-form-field">Display Order<input type="number" min="0" value="${Number(editDuty.dataset.dutyOrderValue || 0)}" data-edit-duty-order></label>`); return; }
            const remove = event.target.closest('[data-delete-duty]'); if (remove) { askConfirmation('Remove Responsibility', 'This responsibility will be deleted after confirmation.', async () => { await request(`/admin/employee-organization/responsibilities/${remove.dataset.deleteDuty}`, { method: 'DELETE' }); await loadDuties(activeDesignation, dutyModal.querySelector('[data-duty-title]').textContent, dutyModal.querySelector('[data-duty-department]').textContent); }); return; }
            const save = event.target.closest('[data-save-duty]'); if (save) { const id = save.dataset.saveDuty; await request(`/admin/employee-organization/responsibilities/${id}`, jsonOptions('PUT', { description: dutyModal.querySelector(`[data-duty-description="${id}"]`)?.value, sort_order: dutyModal.querySelector(`[data-duty-order="${id}"]`)?.value, is_active: 1 })); await loadDuties(activeDesignation, dutyModal.querySelector('[data-duty-title]').textContent, dutyModal.querySelector('[data-duty-department]').textContent); notify('Responsibility updated.'); }
        } catch (error) { notify(error.message, 'error'); }
    });

    dashboard.querySelector('[data-assignment-search]')?.addEventListener('input', (event) => loadPeople(event.target.value).catch((error) => setError('[data-assignment-error]', error.message)));
    dashboard.querySelector('[data-assignment-department]')?.addEventListener('change', () => refreshDesignations());
    dashboard.querySelector('[data-directory-apply]')?.addEventListener('click', loadDirectory);
    dashboard.querySelector('[data-organization-search]')?.addEventListener('input', renderOrganization);
    dashboard.querySelector('[data-organization-department]')?.addEventListener('change', renderOrganization);
    dashboard.querySelector('[data-assignment-form]')?.addEventListener('submit', async (event) => { event.preventDefault(); if (!selectedPerson) return setError('[data-assignment-error]', 'Select a person first.'); const department = dashboard.querySelector('[data-assignment-department]').value; const designation = dashboard.querySelector('[data-assignment-designation]').value; if (!department || !designation) return setError('[data-assignment-error]', 'Department and designation are required.'); try { await request(`/admin/employee-organization/people/${selectedPerson.id}/assignment`, jsonOptions('PATCH', { is_employee: true, primary_department_id: department, primary_designation_id: designation })); notify('Employee assignment saved.'); window.location.reload(); } catch (error) { setError('[data-assignment-error]', error.message); } });
    dashboard.querySelector('[data-org-form]')?.addEventListener('submit', async (event) => { event.preventDefault(); const form = event.currentTarget; const data = Object.fromEntries(new FormData(form)); data.is_active = form.querySelector('[name="is_active"]').checked ? 1 : 0; const type = form.dataset.type; const id = form.dataset.id; const endpoint = type === 'Department' ? urls.departments : '/admin/employee-organization/designations'; try { await request(id ? `${endpoint}/${id}` : endpoint, jsonOptions(id ? 'PUT' : 'POST', data)); closeModals(); await loadOrganization(); notify(`${type} saved.`); } catch (error) { setError('[data-org-error]', error.message); } });
    dashboard.querySelector('[data-confirm-action]')?.addEventListener('click', async () => { if (!confirmAction) return; const action = confirmAction; confirmAction = null; try { await action(); closeChildModal(); } catch (error) { notify(error.message, 'error'); } });
    dashboard.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeTopModal(); });
});

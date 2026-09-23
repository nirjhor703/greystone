document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('investmentCrudPage');

    if (!page) {
        return;
    }

    document.querySelectorAll('.alert').forEach((alert) => {
        window.setTimeout(() => alert.remove(), 3500);
        alert.addEventListener('click', () => alert.remove());
    });

    document.querySelectorAll('[data-investment-editable-help]').forEach((box) => {
        const key = box.dataset.investmentEditableHelp;
        const copy = box.querySelector('[data-investment-help-copy]');
        const editButton = box.querySelector('.investment-help-edit-button');
        const saved = window.localStorage.getItem(key);
        if (saved && copy) {
            copy.textContent = saved;
        }
        editButton?.addEventListener('click', () => {
            copy?.setAttribute('contenteditable', 'true');
            copy?.focus();
        });
        copy?.addEventListener('blur', () => {
            copy.removeAttribute('contenteditable');
            window.localStorage.setItem(key, copy.textContent.trim());
        });
        copy?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                copy.blur();
            }
        });
    });

    const routeUrl = (template, id) => template.replace('__ID__', id);
    let pendingPauseForm = null;

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

        if (field.tagName === 'SELECT' && typeof value === 'boolean') {
            field.value = value ? '1' : '0';
            return;
        }

        field.value = value ?? '';
    }

    function syncInvestorFields(form) {
        const type = form?.querySelector('[data-investor-type-select]')?.value || 'external';

        form?.querySelectorAll('[data-investor-field]').forEach((field) => {
            field.hidden = field.dataset.investorField !== type;
        });
    }

    function syncEntryInvestorField(form) {
        const type = form?.querySelector('[data-entry-type-select]')?.value;
        const investorField = form?.querySelector('[data-entry-investor-field]');
        const activeDateField = form?.querySelector('[data-entry-active-date-field]');
        const needsInvestor = ['investor_investment', 'profit_payout', 'capital_return'].includes(type);

        if (investorField) {
            investorField.hidden = !needsInvestor;
        }

        if (activeDateField) {
            activeDateField.hidden = type !== 'investor_investment';
        }
    }

    document.querySelectorAll('#openAddInvestorModal').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('addInvestorForm');
            form?.reset();
            fillField('add_investor', 'is_active', true);
            fillField('add_investor', 'term_months', 12);
            fillField('add_investor', 'investment_amount', '');
            syncInvestorFields(form);
            openModal('addInvestorModal');
        });
    });

    document.querySelectorAll('#openAddInvestmentEntryModal, #openAddInvestmentEntryModalSecond').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('addInvestmentEntryForm');
            form?.reset();
            fillField('add_entry', 'entry_date', new Date().toISOString().slice(0, 10));
            fillField('add_entry', 'active_date', new Date().toISOString().slice(0, 10));
            fillField('add_entry', 'investment_channel', 'online');
            fillField('add_entry', 'status', 'active');
            syncEntryInvestorField(form);
            openModal('addInvestmentEntryModal');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.closeModal));
    });

    document.querySelectorAll('.openSettlementPayoutModal').forEach((button) => {
        button.addEventListener('click', () => {
            const investorId = document.getElementById('settlement_investment_investor_id');
            const investorName = document.getElementById('settlementInvestorName');
            const payable = document.getElementById('settlementInvestorPayable');
            const share = document.getElementById('settlementInvestorShare');

            if (investorId) {
                investorId.value = button.dataset.investorId || '';
            }

            if (investorName) {
                investorName.textContent = button.dataset.investorName || '-';
            }

            if (payable) {
                payable.textContent = `৳${button.dataset.payable || '0.00'}`;
            }

            if (share) {
                share.textContent = `${button.dataset.share || '0'}%`;
            }

            openModal('settlementPayoutModal');
        });
    });

    document.querySelectorAll('.editInvestorButton').forEach((button) => {
        button.addEventListener('click', () => {
            const payload = document.getElementById(`investorData${button.dataset.investorId}`);

            if (!payload) {
                return;
            }

            const investor = JSON.parse(payload.textContent);
            const form = document.getElementById('editInvestorForm');
            form.action = routeUrl(page.dataset.investorUpdateUrl, investor.id);
            form.dataset.originalActive = investor.is_active ? '1' : '0';

            [
                'type',
                'name',
                'phone',
                'email',
                'member_id',
                'people_profile_id',
                'term_months',
                'agreement_note',
                'is_active',
            ].forEach((field) => fillField('edit_investor', field, investor[field]));

            const statusField = document.getElementById('edit_investor_is_active');
            if (statusField) {
                const locked = ['due_pending', 'settled'].includes(investor.lifecycle_status);
                statusField.disabled = locked;
            }

            const pauseConfirmed = document.getElementById('edit_investor_pause_confirmed');
            const pauseProfitDue = document.getElementById('edit_investor_pause_profit_due');
            const pauseNote = document.getElementById('edit_investor_pause_note');

            if (pauseConfirmed) {
                pauseConfirmed.value = '0';
            }

            if (pauseProfitDue) {
                pauseProfitDue.value = '0';
            }

            if (pauseNote) {
                pauseNote.value = '';
            }

            fillField('edit_investor', 'investment_amount', '');
            syncInvestorFields(form);
            openModal('editInvestorModal');
        });
    });

    const editInvestorForm = document.getElementById('editInvestorForm');
    editInvestorForm?.addEventListener('submit', (event) => {
        const statusField = document.getElementById('edit_investor_is_active');
        const pauseConfirmed = document.getElementById('edit_investor_pause_confirmed');

        if (
            editInvestorForm.dataset.originalActive === '1'
            && statusField?.value === '0'
            && pauseConfirmed?.value !== '1'
        ) {
            event.preventDefault();

            const name = document.getElementById('pauseInvestorName');
            const capital = document.getElementById('pauseInvestorCapital');
            const investorId = editInvestorForm.action.split('/').filter(Boolean).pop();
            const payload = document.getElementById(`investorData${investorId}`);
            const investor = payload ? JSON.parse(payload.textContent) : {};

            pendingPauseForm = editInvestorForm;

            if (name) {
                name.textContent = investor.name || '-';
            }

            if (capital) {
                capital.textContent = `৳${Number(investor.returnable_capital || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            const profitDue = document.getElementById('pauseInvestorProfitDue');
            const note = document.getElementById('pauseInvestorNote');

            if (profitDue) {
                profitDue.value = '0';
            }

            if (note) {
                note.value = '';
            }

            openModal('investorPauseModal');
        }
    });

    document.getElementById('confirmInvestorPauseButton')?.addEventListener('click', () => {
        if (!pendingPauseForm) {
            return;
        }

        const pauseConfirmed = document.getElementById('edit_investor_pause_confirmed');
        const pauseProfitDue = document.getElementById('edit_investor_pause_profit_due');
        const pauseNote = document.getElementById('edit_investor_pause_note');
        const profitDue = document.getElementById('pauseInvestorProfitDue');
        const note = document.getElementById('pauseInvestorNote');

        if (pauseConfirmed) {
            pauseConfirmed.value = '1';
        }

        if (pauseProfitDue) {
            pauseProfitDue.value = profitDue?.value || '0';
        }

        if (pauseNote) {
            pauseNote.value = note?.value || '';
        }

        pendingPauseForm.submit();
    });

    document.querySelectorAll('.editInvestmentEntryButton').forEach((button) => {
        button.addEventListener('click', () => {
            const payload = document.getElementById(`investmentEntryData${button.dataset.entryId}`);

            if (!payload) {
                return;
            }

            const entry = JSON.parse(payload.textContent);
            const form = document.getElementById('editInvestmentEntryForm');
            form.action = routeUrl(page.dataset.entryUpdateUrl, entry.id);

            [
                'entry_type',
                'investment_channel',
                'investment_investor_id',
                'entry_date',
                'active_date',
                'amount',
                'purpose',
                'note',
                'status',
            ].forEach((field) => fillField('edit_entry', field, entry[field]));

            syncEntryInvestorField(form);
            openModal('editInvestmentEntryModal');
        });
    });

    ['addInvestorForm', 'editInvestorForm'].forEach((id) => {
        const form = document.getElementById(id);

        form?.querySelector('[data-investor-type-select]')?.addEventListener('change', () => syncInvestorFields(form));
        syncInvestorFields(form);
    });

    ['addInvestmentEntryForm', 'editInvestmentEntryForm'].forEach((id) => {
        const form = document.getElementById(id);

        form?.querySelector('[data-entry-type-select]')?.addEventListener('change', () => syncEntryInvestorField(form));
        syncEntryInvestorField(form);
    });
});

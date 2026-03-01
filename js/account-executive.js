// Account Executive Customer List Script
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAll');
    const customerModalEl = document.getElementById('customerModal');
    const customerModal = customerModalEl ? new bootstrap.Modal(customerModalEl) : null;

    const modalTitle = document.getElementById('customerModalTitle');
    const overviewSection = document.getElementById('customerOverviewSection');
    const overviewBody = document.getElementById('customerOverviewBody');
    const modalForm = document.getElementById('customerModalForm');
    const editBtn = document.getElementById('customerModalEditBtn');
    const saveBtn = document.getElementById('customerModalSaveBtn');

    const idInput = document.getElementById('customerModalId');
    const nameInput = document.getElementById('customerModalName');
    const emailInput = document.getElementById('customerModalEmail');
    const phoneInput = document.getElementById('customerModalPhone');
    const progressInput = document.getElementById('customerModalProgress');
    const remarksInput = document.getElementById('customerModalRemarks');
    const refundInput = document.getElementById('customerModalRefund');

    let activeRow = null;
    let activeCustomerId = null;
    let activeCustomerName = '';

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(function (checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    document.addEventListener('change', function (evt) {
        if (!evt.target.matches('.row-checkbox')) {
            return;
        }
        updateSelectAllState();
    });

    document.addEventListener('click', function (evt) {
        const btn = evt.target.closest('.js-customer-action');
        if (!btn) {
            return;
        }

        evt.preventDefault();

        const action = btn.dataset.action || '';
        const customerId = btn.dataset.id || '';
        const customerName = btn.dataset.name || 'Customer';
        const row = btn.closest('tr');

        if (action === 'view-customer') {
            openViewMode(customerId, customerName, row);
            return;
        }

        if (action === 'edit-customer') {
            openEditMode(customerId, customerName, row);
            return;
        }

        if (action === 'delete-customer') {
            handleDeleteCustomer(customerId, row);
        }
    });

    if (editBtn) {
        editBtn.addEventListener('click', function () {
            if (!activeCustomerId) {
                return;
            }
            openEditMode(activeCustomerId, activeCustomerName, activeRow, true);
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            if (!idInput || !activeRow) {
                return;
            }

            const payload = {
                customer_id: idInput.value,
                name: nameInput ? nameInput.value.trim() : '',
                email: emailInput ? emailInput.value.trim() : '',
                phone: phoneInput ? phoneInput.value.trim() : '',
                remarks: remarksInput ? remarksInput.value.trim() : '',
                refund_flag: refundInput && refundInput.checked ? 1 : 0
            };

            const originalHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';

            try {
                const result = await postAjax('edit-customer', payload);
                if (!(result && (result.ok || result.success))) {
                    throw new Error((result && result.message) || 'Update failed');
                }
                const computedProgress = result && result.customer && result.customer.progress !== undefined
                    ? parseInt(result.customer.progress, 10)
                    : (progressInput ? parseInt(progressInput.value || '0', 10) : 0);
                if (progressInput) {
                    progressInput.value = String(computedProgress);
                }
                updateRowFromPayload(activeRow, {
                    ...payload,
                    progress: computedProgress
                });
                showNotification('success', result.message || 'Customer updated.');
                customerModal.hide();
            } catch (error) {
                showNotification('error', error.message || 'Update failed');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
            }
        });
    }

    const bulkActionBtn = document.getElementById('bulkActionBtn');
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', function () {
            const selectedIds = getSelectedCustomerIds();
            const action = document.getElementById('bulkActionSelect') ? document.getElementById('bulkActionSelect').value : '';

            if (selectedIds.length === 0) {
                showNotification('warning', 'Please select at least one customer');
                return;
            }
            if (!action) {
                showNotification('warning', 'Please select an action');
                return;
            }
            performBulkAction(action, selectedIds);
        });
    }

    function openViewMode(customerId, customerName, row) {
        if (!customerModal || !overviewSection || !overviewBody || !modalForm) {
            showNotification('error', 'Customer modal is not available.');
            return;
        }

        activeRow = row;
        activeCustomerId = customerId;
        activeCustomerName = customerName;

        if (modalTitle) {
            modalTitle.textContent = 'Customer Overview - ' + customerName;
        }

        overviewSection.classList.remove('d-none');
        modalForm.classList.add('d-none');
        if (saveBtn) {
            saveBtn.classList.add('d-none');
        }
        if (editBtn) {
            editBtn.classList.remove('d-none');
            editBtn.textContent = 'Edit';
        }

        overviewBody.innerHTML = '' +
            '<div class="text-center py-5">' +
            '  <div class="spinner-border text-primary mb-3" role="status">' +
            '    <span class="visually-hidden">Loading...</span>' +
            '  </div>' +
            '  <div class="text-muted">Loading account overview...</div>' +
            '</div>';

        customerModal.show();
        loadCustomerOverview(customerId);
    }

    function openEditMode(customerId, customerName, row, keepModalOpen) {
        if (!customerModal || !modalForm || !overviewSection) {
            showNotification('error', 'Customer modal is not available.');
            return;
        }

        activeRow = row || activeRow;
        activeCustomerId = customerId || activeCustomerId;
        activeCustomerName = customerName || activeCustomerName;

        if (modalTitle) {
            modalTitle.textContent = 'Edit Customer - ' + (activeCustomerName || 'Customer');
        }

        fillEditFormFromRow(activeCustomerId, activeCustomerName, activeRow);
        populateEditFormFromApi(activeCustomerId);
        setEditModeEnabled(true);

        overviewSection.classList.add('d-none');
        modalForm.classList.remove('d-none');

        if (editBtn) {
            editBtn.classList.add('d-none');
        }
        if (saveBtn) {
            saveBtn.classList.remove('d-none');
        }

        if (!keepModalOpen) {
            customerModal.show();
        }
    }

    function fillEditFormFromRow(customerId, customerName, row) {
        if (idInput) idInput.value = customerId || '';
        if (nameInput) nameInput.value = customerName || '';
        if (emailInput) emailInput.value = '';
        if (phoneInput) phoneInput.value = '';
        if (remarksInput) remarksInput.value = '';
        if (progressInput) progressInput.value = row ? (row.dataset.progress || 0) : 0;
        if (refundInput) refundInput.checked = row ? String(row.dataset.refund || '0') === '1' : false;
    }

    function setEditModeEnabled(enabled) {
        if (nameInput) nameInput.readOnly = !enabled;
        if (emailInput) emailInput.readOnly = !enabled;
        if (phoneInput) phoneInput.readOnly = !enabled;
        if (remarksInput) remarksInput.readOnly = !enabled;
        if (progressInput) progressInput.readOnly = true;
        if (refundInput) refundInput.disabled = !enabled;
    }

    async function loadCustomerOverview(customerId) {
        try {
            const data = await fetchCustomerOverviewData(customerId);
            renderCustomerOverview(data || {});
        } catch (error) {
            if (overviewBody) {
                overviewBody.innerHTML =
                    '<div class="alert alert-danger mb-0">' +
                    '<i class="fa fa-exclamation-triangle me-2"></i>' +
                    escapeHtml(error.message || 'Failed to load overview.') +
                    '</div>';
            }
        }
    }

    async function populateEditFormFromApi(customerId) {
        if (!customerId) {
            return;
        }
        try {
            const data = await fetchCustomerOverviewData(customerId);
            const customer = data && data.customer ? data.customer : {};

            if (nameInput) nameInput.value = customer.name || customer.full_name || nameInput.value;
            if (emailInput) emailInput.value = customer.email || '';
            if (phoneInput) phoneInput.value = customer.phone || '';
            if (remarksInput) remarksInput.value = customer.remarks || '';
            if (progressInput && customer.progress !== undefined && customer.progress !== null) progressInput.value = customer.progress;
            if (refundInput) refundInput.checked = Number(customer.refund_flag || 0) === 1;
        } catch (error) {
            // Keep row-based fallback values if API details fail to load.
        }
    }

    async function fetchCustomerOverviewData(customerId) {
        const endpoint = new URL('api/get-customer-overview.php', window.location.href);
        endpoint.searchParams.set('customer_id', customerId);

        const response = await fetch(endpoint.toString(), {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        const rawText = await response.text();
        let payload = null;
        try {
            payload = JSON.parse(rawText);
        } catch (jsonError) {
            throw new Error('Overview API returned non-JSON response (' + response.status + '). Check API path/routing.');
        }

        if (!response.ok || !payload || !payload.success) {
            throw new Error((payload && payload.message) || 'Failed to load overview');
        }

        return payload.data || {};
    }

    function renderCustomerOverview(data) {
        if (!overviewBody) {
            return;
        }

        const customer = data.customer || {};
        const crm = data.crm || {};
        const passportVisa = data.passport || data.passport_visa || {};
        const scheduleRates = data.schedule || data.schedule_rates || {};
        const facilities = data.facilities || data.facilities_reservation || {};

        overviewBody.innerHTML = '' +
            '<div class="ae-overview">' +
            '  <div class="mb-4">' +
            '    <h6 class="fw-bold border-bottom pb-2 mb-3">Basic Customer Information</h6>' +
            '    <div class="row g-3">' +
            '      <div class="col-md-4">' +
            '        <div class="text-muted small">Customer Name</div>' +
            '        <div class="fw-semibold">' + escapeHtml(customer.name || customer.full_name || 'N/A') + '</div>' +
            '      </div>' +
            '      <div class="col-md-4">' +
            '        <div class="text-muted small">Email</div>' +
            '        <div class="fw-semibold">' + escapeHtml(customer.email || 'No email') + '</div>' +
            '      </div>' +
            '      <div class="col-md-4">' +
            '        <div class="text-muted small">Overall Account Status</div>' +
            '        <div>' + badgeHtml(customer.overall_status || customer.status || 'pending') + '</div>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="row g-3">' +
            moduleCardHtml('CRM', [
                ['Tier Level', '<span class="fw-semibold">' + escapeHtml(crm.tier || crm.customer_tier_level || 'new') + '</span>'],
                ['Engagement Status', badgeHtml(crm.engagement_status || 'pending')],
                ['Total Transactions', '<span class="fw-semibold">' + escapeHtml(String(crm.total_transactions || 0)) + '</span>']
            ]) +
            moduleCardHtml('Passport & Visa', [
                ['Document Submission Status', badgeHtml(passportVisa.document_status || passportVisa.document_submission_status || 'pending')],
                ['Verification Status', badgeHtml(passportVisa.verification_status || 'pending')],
                ['Compliance Status', badgeHtml(passportVisa.compliance_status || 'pending')]
            ]) +
            moduleCardHtml('Schedule & Rates', [
                ['Schedule Inquiry Status', badgeHtml(scheduleRates.inquiry_status || scheduleRates.schedule_inquiry_status || 'pending')],
                ['Rate Quotation Status', badgeHtml(scheduleRates.quotation_status || scheduleRates.rate_quotation_status || 'pending')]
            ]) +
            moduleCardHtml('Facilities & Reservation', [
                ['Reservation Status', badgeHtml(facilities.reservation_status || 'pending')],
                ['Payment Status', badgeHtml(facilities.payment_status || 'pending')]
            ]) +
            '  </div>' +
            '</div>';
    }

    function moduleCardHtml(title, rows) {
        let tableRows = '';
        rows.forEach(function (row) {
            tableRows += '' +
                '<tr>' +
                '  <th style="width: 45%;">' + escapeHtml(row[0]) + '</th>' +
                '  <td>' + row[1] + '</td>' +
                '</tr>';
        });

        return '' +
            '<div class="col-md-6">' +
            '  <div class="card h-100 shadow-sm">' +
            '    <div class="card-header fw-bold">' + escapeHtml(title) + '</div>' +
            '    <div class="card-body p-0">' +
            '      <div class="table-responsive">' +
            '        <table class="table table-bordered table-sm align-middle mb-0">' +
            '          <thead class="table-light">' +
            '            <tr>' +
            '              <th style="width: 45%;">Field Name</th>' +
            '              <th>Value</th>' +
            '            </tr>' +
            '          </thead>' +
            '          <tbody>' + tableRows + '</tbody>' +
            '        </table>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';
    }

    function badgeHtml(value) {
        const text = String(value || 'pending');
        const tone = statusTone(text);
        return '<span class="badge rounded-pill bg-' + tone + '">' + escapeHtml(text) + '</span>';
    }

    function statusTone(value) {
        const normalized = String(value || '').trim().toLowerCase();
        const successValues = [
            'approved', 'complete', 'completed', 'paid', 'verified', 'visa issued',
            'confirmed', 'active', 'open', 'admitted', 'finished', 'submitted'
        ];
        const dangerValues = [
            'rejected', 'denied', 'failed', 'cancelled', 'overdue', 'missing', 'action required'
        ];
        if (successValues.indexOf(normalized) !== -1) {
            return 'success';
        }
        if (dangerValues.indexOf(normalized) !== -1) {
            return 'danger';
        }
        return 'warning';
    }

    async function handleDeleteCustomer(customerId, row) {
        if (!customerId) {
            showNotification('error', 'Invalid customer id.');
            return;
        }
        const confirmed = await showConfirm('Delete customer #' + customerId + '?', null, {
            title: 'Confirm Delete',
            okText: 'Delete',
            okClass: 'btn btn-danger'
        });
        if (!confirmed) {
            return;
        }

        try {
            const result = await postAjax('delete-customer', { customer_id: customerId });
            if (!(result && (result.ok || result.success))) {
                throw new Error((result && result.message) || 'Delete failed');
            }
            if (row && row.parentNode) {
                row.parentNode.removeChild(row);
            }
            showNotification('success', result.message || 'Customer deleted.');
            updateSelectAllState();
        } catch (error) {
            showNotification('error', error.message || 'Delete failed');
        }
    }

    async function postAjax(action, payload) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', action);
        const response = await fetch(url.pathname + '?' + url.searchParams.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });
        const result = await response.json();
        if (!response.ok) {
            throw new Error((result && result.message) || 'Request failed');
        }
        return result;
    }

    function updateRowFromPayload(row, payload) {
        if (!row) {
            return;
        }

        row.dataset.progress = String(payload.progress || row.dataset.progress || 0);
        row.dataset.refund = String(payload.refund_flag || 0);

        if (row.cells[2]) row.cells[2].innerText = payload.name || row.cells[2].innerText;

        if (row.cells[6]) {
            const progress = Number.isFinite(payload.progress) ? payload.progress : parseInt(payload.progress || '0', 10);
            row.cells[6].innerHTML = String(progress) + '%' +
                '<div class="progress"><div class="progress-bar bg-success" style="width:' + progress + '%"></div></div>';
        }
    }

    function getSelectedCustomerIds() {
        const ids = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(function (checkbox) {
            const row = checkbox.closest('tr');
            if (row && row.dataset.id) {
                ids.push(row.dataset.id);
            }
        });
        return ids;
    }

    function performBulkAction(action, ids) {
        showConfirm('Are you sure you want to ' + action + ' ' + ids.length + ' customer(s)?', async function () {
            fetch('/api/bulk-customer-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, ids: ids })
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        showNotification('success', 'Bulk action completed successfully');
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Bulk action failed');
                    }
                })
                .catch(function (error) {
                    showNotification('error', 'Bulk action failed: ' + (error.message || 'Unknown error'));
                });
        }, {
            title: 'Confirm Bulk Action',
            okText: 'Proceed',
            okClass: 'btn btn-primary'
        });
    }

    function showNotification(type, message) {
        if (type === 'success') {
            showSuccess(message);
            return;
        }
        if (type === 'warning') {
            showWarning(message);
            return;
        }
        if (type === 'error') {
            showError(message);
            return;
        }
        showInfo(message);
    }

    function updateSelectAllState() {
        if (!selectAllCheckbox) {
            return;
        }
        const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
        const allChecked = rowCheckboxes.length > 0 && rowCheckboxes.every(function (cb) { return cb.checked; });
        const anyChecked = rowCheckboxes.some(function (cb) { return cb.checked; });
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = !allChecked && anyChecked;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text || '');
        return div.innerHTML;
    }
});

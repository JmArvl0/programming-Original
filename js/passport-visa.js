document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.left-controls');
    const searchInput = searchForm ? searchForm.querySelector('.search-input') : null;
    const modalElement = document.getElementById('passportApplicantModal');
    const modalBody = document.getElementById('passportApplicantModalBody');
    const modalTitle = document.getElementById('passportApplicantModalTitle');
    const applicantModal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const updateModalElement = document.getElementById('passportApplicantUpdateModal');
    const updateModalTitle = document.getElementById('passportApplicantUpdateModalTitle');
    const updateModal = updateModalElement ? new bootstrap.Modal(updateModalElement) : null;
    const openUpdateBtn = document.getElementById('passportOpenUpdateBtn');
    const updateSaveBtn = document.getElementById('passportUpdateSaveBtn');
    const updateApplicantIdInput = document.getElementById('passportUpdateApplicantId');
    const updateNumberInput = document.getElementById('passportUpdateNumber');
    const updateCountryInput = document.getElementById('passportUpdateCountry');
    const updateDocumentsStatusInput = document.getElementById('passportUpdateDocumentsStatus');
    const updateApplicationStatusInput = document.getElementById('passportUpdateApplicationStatus');
    const updateSubmissionDateInput = document.getElementById('passportUpdateSubmissionDate');
    const updateRemarksInput = document.getElementById('passportUpdateRemarks');
    let activeApplicantId = '';
    let activeApplicantName = '';

    const confirmModalElement = document.getElementById('passportApplicantConfirmModal');
    const confirmModal = confirmModalElement ? new bootstrap.Modal(confirmModalElement) : null;
    const confirmOpenUpdateBtn = document.getElementById('passportConfirmOpenUpdateBtn');

    if (searchInput && searchForm) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                searchForm.submit();
            }
        });
    }

    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    }

    async function callAjaxAction(action, payload) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', action);

        const response = await fetch(url.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }
        return response.json();
    }

    document.querySelectorAll('.js-applicant-action').forEach((button) => {
        button.addEventListener('click', async function () {
            const action = this.getAttribute('data-action') || '';
            const applicantId = this.getAttribute('data-id') || '';
            const applicantName = this.getAttribute('data-name') || 'Applicant';

            if (action === 'view') {
                await openApplicantView(applicantId, applicantName);
                return;
            }
            if (action === 'update-applicant') {
                await openApplicantUpdate(applicantId, applicantName);
                return;
            }

            try {
                const result = await callAjaxAction(action, { applicantId });
                showSuccess(result.message || 'Action completed.');
            } catch (error) {
                showError('Unable to process request right now.');
            }
        });
    });

    const reminderBtn = document.getElementById('sendPaymentReminderBtn');
    if (reminderBtn) {
        reminderBtn.addEventListener('click', async function () {
            try {
                const result = await callAjaxAction('send-reminder', { scope: 'filtered-list' });
                showSuccess(result.message || 'Reminder sent.');
            } catch (error) {
                showError('Unable to send reminder right now.');
            }
        });
    }

    // The Update button on the overview modal now opens a confirmation modal first.
    // The confirm modal's button will then load and show the update form.
    if (confirmOpenUpdateBtn) {
        confirmOpenUpdateBtn.addEventListener('click', async function () {
            if (!activeApplicantId) {
                showWarning('No selected applicant to update.');
                if (confirmModal) confirmModal.hide();
                return;
            }
            if (confirmModal) confirmModal.hide();
            try {
                await openApplicantUpdate(activeApplicantId, activeApplicantName);
            } catch (err) {
                showError('Unable to open update form.');
            }
        });
    }

    if (updateSaveBtn) {
        updateSaveBtn.addEventListener('click', async function () {
            const applicantId = updateApplicantIdInput ? updateApplicantIdInput.value : '';
            if (!applicantId) {
                showError('Invalid applicant id.');
                return;
            }

            const payload = {
                applicantId: applicantId,
                passport_number: updateNumberInput ? updateNumberInput.value.trim() : '',
                country: updateCountryInput ? updateCountryInput.value.trim() : '',
                documents_status: updateDocumentsStatusInput ? updateDocumentsStatusInput.value : 'not started',
                application_status: updateApplicationStatusInput ? updateApplicationStatusInput.value : 'not started',
                submission_date: updateSubmissionDateInput ? updateSubmissionDateInput.value : '',
                remarks: updateRemarksInput ? updateRemarksInput.value.trim() : ''
            };

            const originalLabel = updateSaveBtn.innerHTML;
            updateSaveBtn.disabled = true;
            updateSaveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';

            try {
                const result = await callAjaxAction('update-applicant', payload);
                if (!(result && result.ok)) {
                    throw new Error((result && result.message) || 'Update failed.');
                }

                const updated = result.data || {};
                updateRowAfterSave(updated);
                showSuccess(result.message || 'Application updated.');
                if (updateModal) {
                    updateModal.hide();
                }
                await openApplicantView(applicantId, activeApplicantName || updated.name || 'Applicant');
            } catch (error) {
                showError(error.message || 'Unable to save changes.');
            } finally {
                updateSaveBtn.disabled = false;
                updateSaveBtn.innerHTML = originalLabel;
            }
        });
    }

    async function openApplicantView(applicantId, applicantName) {
        if (!applicantModal || !modalBody || !modalTitle) {
            showError('Applicant modal is unavailable.');
            return;
        }

        modalTitle.textContent = 'Applicant Overview - ' + applicantName;
        activeApplicantId = String(applicantId || '');
        activeApplicantName = applicantName || '';
        modalBody.innerHTML = '' +
            '<div class="text-center py-5">' +
            '  <div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div>' +
            '  <div class="text-muted">Loading applicant details...</div>' +
            '</div>';
        applicantModal.show();

        try {
            const result = await callAjaxAction('fetch-applicant-detail', { applicantId: applicantId });
            if (!(result && result.ok && result.data)) {
                throw new Error((result && result.message) || 'Failed to load applicant details.');
            }
            renderApplicantOverview(result.data);
        } catch (error) {
            modalBody.innerHTML =
                '<div class="alert alert-danger mb-0">' +
                '<i class="fa fa-exclamation-triangle me-2"></i>' +
                escapeHtml(error.message || 'Unable to load applicant details.') +
                '</div>';
        }
    }

    async function openApplicantUpdate(applicantId, applicantName) {
        if (!updateModal) {
            showError('Update modal is unavailable.');
            return;
        }

        const result = await callAjaxAction('fetch-applicant-detail', { applicantId: applicantId });
        if (!(result && result.ok && result.data)) {
            throw new Error((result && result.message) || 'Unable to load applicant data.');
        }

        const data = result.data || {};
        const passport = data.passport || {};
        const documents = (data.documents && data.documents.text) ? data.documents.text : 'Not Started';
        const application = (data.application && data.application.text) ? data.application.text : 'Not Started';

        activeApplicantId = String(applicantId || '');
        activeApplicantName = applicantName || data.name || '';

        if (updateModalTitle) {
            updateModalTitle.textContent = 'Update Application - ' + (activeApplicantName || 'Applicant');
        }
        if (updateApplicantIdInput) updateApplicantIdInput.value = String(applicantId || '');
        if (updateNumberInput) updateNumberInput.value = passport.number || '';
        if (updateCountryInput) updateCountryInput.value = data.country || '';
        if (updateDocumentsStatusInput) updateDocumentsStatusInput.value = normalizeStatusValue(documents);
        if (updateApplicationStatusInput) updateApplicationStatusInput.value = normalizeStatusValue(application);
        if (updateSubmissionDateInput) updateSubmissionDateInput.value = data.submissionDateIso || '';
        if (updateRemarksInput) updateRemarksInput.value = data.remarks || '';

        updateModal.show();
    }

    function renderApplicantOverview(data) {
        if (!modalBody) {
            return;
        }

        const name = data.name || 'N/A';
        const email = data.email || 'N/A';
        const phone = data.phone || 'N/A';
        const country = data.country || 'N/A';
        const passport = data.passport || {};
        const documents = data.documents || {};
        const application = data.application || {};
        const passportImage = data.passportImagePath || 'assets/LOGO.png';
        const oneByOneImage = data.oneByOneImagePath || 'assets/LOGO.png';
        const remarks = data.remarks || 'No remarks provided.';

        modalBody.innerHTML = '' +
            '<div class="ae-overview">' +
            '  <div class="mb-4">' +
            '    <h6 class="fw-bold border-bottom pb-2 mb-3">Basic Customer Information</h6>' +
            '    <div class="row g-3">' +
            '      <div class="col-md-4"><div class="text-muted small">Customer Name</div><div class="fw-semibold">' + escapeHtml(name) + '</div></div>' +
            '      <div class="col-md-4"><div class="text-muted small">Email</div><div class="fw-semibold">' + escapeHtml(email) + '</div></div>' +
            '      <div class="col-md-4"><div class="text-muted small">Phone</div><div class="fw-semibold">' + escapeHtml(phone) + '</div></div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="row g-3 mb-3">' +
            passportCard('Passport & Visa Details', [
                ['Passport Number', escapeHtml(passport.number || 'N/A')],
                ['Passport Status', badgeHtml(passport.desc || 'Pending')],
                ['Country', escapeHtml(country)],
                ['Issue Date', escapeHtml(passport.issueDate || 'N/A')],
                ['Expiry Date', escapeHtml(passport.expiryDate || 'N/A')],
                ['Submission Date', escapeHtml(data.submissionDate || 'N/A')]
            ]) +
            passportCard('Processing Status', [
                ['Documents', badgeHtml(documents.text || 'Not Started')],
                ['Application', badgeHtml(application.text || 'Not Started')],
                ['Remarks', escapeHtml(remarks)]
            ]) +
            '  </div>' +
            '  <div class="row g-3">' +
            imageCard('Passport Image', passportImage, 'Passport image not available') +
            imageCard('Latest 1x1 Photo', oneByOneImage, '1x1 photo not available') +
            '  </div>' +
            '</div>';
    }

    function updateRowAfterSave(updated) {
        if (!updated || !updated.id) {
            return;
        }
        const row = document.querySelector('button.js-applicant-action[data-action="view"][data-id="' + updated.id + '"]')?.closest('tr');
        if (!row) {
            return;
        }

        const passportNumber = updated.passport && updated.passport.number ? updated.passport.number : 'N/A';
        const country = updated.country || 'N/A';
        const documentsText = updated.documents && updated.documents.text ? updated.documents.text : 'Not Started';
        const applicationText = updated.application && updated.application.text ? updated.application.text : 'Not Started';

        row.dataset.country = country;
        row.dataset.passportNumber = passportNumber;
        row.dataset.documents = normalizeStatusValue(documentsText);
        row.dataset.application = normalizeStatusValue(applicationText);
        row.dataset.date = updated.submissionDateIso || row.dataset.date || '';

        if (row.cells[3]) {
            row.cells[3].innerHTML = '<span class="status-dot status-green"></span> ' + escapeHtml(passportNumber);
        }
        if (row.cells[4]) {
            row.cells[4].textContent = country;
        }
        if (row.cells[5]) {
            row.cells[5].innerHTML = '<span class="badge rounded-pill ' + badgeClass(documentsText) + '">' + escapeHtml(documentsText) + '</span>';
        }
        if (row.cells[6]) {
            row.cells[6].innerHTML = '<span class="badge rounded-pill ' + badgeClass(applicationText) + '">' + escapeHtml(applicationText) + '</span>';
        }
    }

    function normalizeStatusValue(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/_/g, ' ');
    }

    function passportCard(title, rows) {
        let rowsHtml = '';
        rows.forEach(function (row) {
            rowsHtml += '' +
                '<tr>' +
                '  <th style="width: 38%;">' + escapeHtml(row[0]) + '</th>' +
                '  <td>' + row[1] + '</td>' +
                '</tr>';
        });

        return '' +
            '<div class="col-md-6">' +
            '  <div class="card h-100 shadow-sm">' +
            '    <div class="card-header fw-semibold bg-light">' + escapeHtml(title) + '</div>' +
            '    <div class="card-body p-0">' +
            '      <div class="table-responsive">' +
            '        <table class="table table-sm table-bordered mb-0 align-middle"><tbody>' + rowsHtml + '</tbody></table>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';
    }

    function imageCard(title, src, alt) {
        return '' +
            '<div class="col-md-6">' +
            '  <div class="card h-100 shadow-sm">' +
            '    <div class="card-header fw-semibold bg-light">' + escapeHtml(title) + '</div>' +
            '    <div class="card-body text-center">' +
            '      <img src="' + escapeHtml(src) + '" alt="' + escapeHtml(alt) + '" class="img-fluid rounded border" style="max-height: 280px; object-fit: contain;">' +
            '    </div>' +
            '  </div>' +
            '</div>';
    }

    function badgeHtml(value) {
        const text = String(value || 'Pending');
        return '<span class="badge rounded-pill ' + badgeClass(text) + '">' + escapeHtml(text) + '</span>';
    }

    function badgeClass(value) {
        const normalized = String(value || '').trim().toLowerCase();
        if (['approved', 'visa issued', 'valid', 'complete', 'completed'].includes(normalized)) {
            return 'bg-success';
        }
        if (['missing', 'rejected', 'denied', 'failed', 'expired'].includes(normalized)) {
            return 'bg-danger';
        }
        if (['submitted', 'processing', 'under review'].includes(normalized)) {
            return 'bg-primary';
        }
        return 'bg-warning text-dark';
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value || '');
        return div.innerHTML;
    }
});

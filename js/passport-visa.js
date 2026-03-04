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

    function generatePassportNumber() {
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const randomLetter = () => letters[Math.floor(Math.random() * letters.length)];
        const randomDigit = () => Math.floor(Math.random() * 10);

        // Example format: AB1234C
        return (
            randomLetter() +
            randomLetter() +
            randomDigit() +
            randomDigit() +
            randomDigit() +
            randomDigit() +
            randomLetter()
        );
    }

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
        modalBody.innerHTML = '<p>Loading...</p>'; // Placeholder while loading

        try {
            const result = await callAjaxAction('fetch-applicant-detail', { applicantId });

            if (result && result.ok) {
                if (result.data) {
                    renderApplicantOverview(result.data); // Render the applicant details
                } else {
                    modalBody.innerHTML = '<p>No details available for this applicant.</p>';
                }
            } else {
                throw new Error((result && result.message) || 'Failed to load applicant details.');
            }
        } catch (error) {
            modalBody.innerHTML = '<p>Error loading applicant details: ' + error.message + '</p>';
        }

        applicantModal.show();
    }

    function renderApplicantOverview(data) {
        const name = data.full_name || 'N/A';
        const email = data.email || 'N/A';
        const phone = data.phone || 'N/A';

        const passportImage = data.passport_image || 'assets/no-image.png';
        const oneByOneImage = data.one_by_one_image || 'assets/no-image.png';

        modalBody.innerHTML = `
            <div>

                <h5 class="mb-3">Basic Customer Information</h5>
                <p><strong>Customer Name:</strong> ${escapeHtml(name)}</p>
                <p><strong>Email:</strong> ${escapeHtml(email)}</p>
                <p><strong>Phone:</strong> ${escapeHtml(phone)}</p>

                <hr class="my-4">

                <h5 class="mb-3">Passport & Visa Details</h5>

                <div class="details-table">
                    ${detailRow('Passport Number', data.passport_number)}
                    ${detailRow('Country', data.country)}
                    ${detailRow('Documents Status', badgeHtml(data.documents_status))}
                    ${detailRow('Application Status', badgeHtml(data.application_status))}
                    ${detailRow('Submission Date', formatDate(data.submission_date))}
                    ${detailRow('Remarks', data.remarks)}
                </div>

                <div class="mt-4">
                    ${imageBlock('Passport Image', passportImage)}
                    ${imageBlock('1x1 Photo', oneByOneImage)}
                </div>

            </div>
        `;
    }

    function imageCard(title, src, alt) {
        return '' +
            '<div class="col-md-4">' +
            '  <div class="card h-100 shadow-sm">' +
            '    <div class="card-header fw-semibold bg-light">' + escapeHtml(title) + '</div>' +
            '    <div class="card-body text-center">' +
            '      <img src="' + escapeHtml(src) + '" alt="' + escapeHtml(alt) + '" class="img-fluid rounded border" style="max-height: 280px; object-fit: contain;">' +
            '    </div>' +
            '  </div>' +
            '</div>';
    }

    function passportCard(title, rows) {
        let rowsHtml = '';

        rows.forEach(function (row) {
            rowsHtml += `
                <div class="detail-row">
                    <div class="detail-label">${escapeHtml(row[0])}</div>
                    <div class="detail-value">${row[1]}</div>
                </div>
            `;
        });

        return `
            <div class="col-md-4">
                <div class="passport-details-wrapper">
                    <h6 class="mb-3 fw-bold">${escapeHtml(title)}</h6>
                    ${rowsHtml}
                </div>
            </div>
        `;
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

    function formatDate(dateStr) {
        if (!dateStr || dateStr === '0000-00-00') return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = String(value || '');
            return div.innerHTML;
        }

        function detailRow(label, value) {
        return `
            <div class="detail-row-horizontal">
                <div class="detail-label">${escapeHtml(label)}</div>
                <div class="detail-value">${value ? value : 'N/A'}</div>
            </div>
        `;
    }

    function imageBlock(title, src) {
        return `
            <div class="image-block mb-4">
                <h6 class="fw-semibold mb-2">${escapeHtml(title)}</h6>
                <div class="image-wrapper text-center">
                    <img src="${escapeHtml(src)}"
                        class="img-fluid rounded border">
                </div>
            </div>
        `;
    }

});
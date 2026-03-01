<?php
$baseQuery = [
    'filter' => $selectedFilter,
    'q' => $searchTerm,
    'per_page' => $perPage
];

$buildPassportUrl = static function (array $overrides = []) use ($baseQuery): string {
    $query = array_merge($baseQuery, $overrides);
    $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');
    return '?' . http_build_query($query);
};
?>

<?php require __DIR__ . '/components/header.php'; ?>
<?php require_once __DIR__ . '/../../includes/status_helpers.php'; ?>
<?php require __DIR__ . '/components/stats.php'; ?>
<div class="table-section">
    <?php require __DIR__ . '/components/filters.php'; ?>
    <?php require __DIR__ . '/components/table.php'; ?>
    <?php require __DIR__ . '/components/pagination.php'; ?>
</div>

<div class="modal fade" id="passportApplicantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passportApplicantModalTitle">Applicant Overview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="passportApplicantModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="passportOpenUpdateBtn" data-bs-toggle="modal" data-bs-target="#passportApplicantConfirmModal">Update Application</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation modal that opens before the update modal -->
<div class="modal fade" id="passportApplicantConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passportApplicantConfirmModalLabel">Confirm Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You are about to open the Update Application form. Continue?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="passportConfirmOpenUpdateBtn">Open Update Form</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passportApplicantUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passportApplicantUpdateModalTitle">Update Passport/Visa Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passportApplicantUpdateForm" class="row g-3">
                    <input type="hidden" id="passportUpdateApplicantId">
                    <div class="col-md-6">
                        <label for="passportUpdateNumber" class="form-label">Passport Number</label>
                        <input type="text" id="passportUpdateNumber" class="form-control" maxlength="50">
                    </div>
                    <div class="col-md-6">
                        <label for="passportUpdateCountry" class="form-label">Country</label>
                        <input type="text" id="passportUpdateCountry" class="form-control" maxlength="120">
                    </div>
                    <div class="col-md-6">
                        <label for="passportUpdateDocumentsStatus" class="form-label">Documents Status</label>
                        <select id="passportUpdateDocumentsStatus" class="form-select">
                            <option value="approved">Approved</option>
                            <option value="submitted">Submitted</option>
                            <option value="missing">Missing</option>
                            <option value="rejected">Rejected</option>
                            <option value="not started">Not Started</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="passportUpdateApplicationStatus" class="form-label">Application Status</label>
                        <select id="passportUpdateApplicationStatus" class="form-select">
                            <option value="visa issued">Visa Issued</option>
                            <option value="approved">Approved</option>
                            <option value="processing">Processing</option>
                            <option value="under review">Under Review</option>
                            <option value="pending">Pending</option>
                            <option value="action required">Action Required</option>
                            <option value="not started">Not Started</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="passportUpdateSubmissionDate" class="form-label">Submission Date</label>
                        <input type="date" id="passportUpdateSubmissionDate" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="passportUpdateRemarks" class="form-label">Remarks</label>
                        <textarea id="passportUpdateRemarks" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="passportUpdateSaveBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

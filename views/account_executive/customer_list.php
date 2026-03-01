<?php
$baseQuery = [
    'tab' => $selectedTab,
    'payment' => $selectedPayment,
    'status' => $selectedStatus,
    'q' => $searchTerm,
    'per_page' => $perPage
];

$buildAccountExecutiveUrl = static function (array $overrides = []) use ($baseQuery): string {
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

    <!-- Customer modal (view / edit) -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="customerOverviewSection">
                        <div id="customerOverviewBody" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <form id="customerModalForm" class="d-none">
                        <input type="hidden" id="customerModalId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" id="customerModalName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="customerModalEmail" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="customerModalPhone" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea id="customerModalRemarks" class="form-control" rows="3" placeholder="Add remarks..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="customerModalRefund" disabled>
                                    <label class="form-check-label" for="customerModalRefund">
                                        Refund Flag
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="customerModalEditBtn">Edit</button>
                    <button type="button" class="btn btn-primary d-none" id="customerModalSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

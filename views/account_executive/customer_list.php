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
<?php require __DIR__ . '/components/stats.php'; ?>
<div class="table-section">
    <?php require __DIR__ . '/components/filters.php'; ?>
    <?php require __DIR__ . '/components/table.php'; ?>
    <?php require __DIR__ . '/components/pagination.php'; ?>
</div>

    <!-- Customer modal (view / edit) -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="customerModalForm">
                        <input type="hidden" id="customerModalId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="customerModalName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" class="form-control" id="customerModalDestination" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Status</label>
                            <select id="customerModalPayment" class="form-select" disabled>
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="overdue">Overdue</option>
                                <option value="partially paid">Partially Paid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Progress (%)</label>
                            <input type="number" id="customerModalProgress" class="form-control" min="0" max="100" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select id="customerModalStatus" class="form-select" disabled>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="finished">Finished</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
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

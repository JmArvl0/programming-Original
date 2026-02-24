<div class="section-header">
    <h2 class="section-title">Customer List</h2>
    <div class="table-controls">
        <form method="GET" action="" class="left-controls">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($selectedTab, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
            <input type="hidden" name="page" value="1">
            <input type="text" class="form-control search-input" name="q" placeholder="Search name or destination..." value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
            <select class="form-select filter-select" name="payment" aria-label="Payment filter">
                <option value="all" <?= $selectedPayment === 'all' ? 'selected' : '' ?>>All</option>
                <option value="paid" <?= $selectedPayment === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="unpaid" <?= $selectedPayment === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                <option value="overdue" <?= $selectedPayment === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                <option value="partially paid" <?= $selectedPayment === 'partially paid' ? 'selected' : '' ?>>Partially Paid</option>
            </select>
        </form>
        <button id="sendPaymentReminderBtn" class="btn btn-warning" type="button">
            <i class="fa fa-bell"></i> Send Reminder
        </button>
    </div>
</div>

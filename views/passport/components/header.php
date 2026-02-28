<div class="section-header">
    <h2 class="section-title">Applicant List</h2>
    <div class="table-controls">
        <form method="GET" action="" class="left-controls">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($selectedFilter, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon" aria-hidden="true"></i>
                <input type="text" name="q" class="form-control search-input" placeholder="Search name or destination..." value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </form>
        <button id="sendPaymentReminderBtn" class="btn btn-warning js-reminder-action" data-action="send-reminder">
            <i class="fa fa-bell"></i> Send Reminder
        </button>
    </div>
</div>

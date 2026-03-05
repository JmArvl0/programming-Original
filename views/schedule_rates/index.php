<div class="destination-filter">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="month" value="<?= htmlspecialchars($currentMonth) ?>">
        <input type="hidden" name="year" value="<?= (int) $currentYear ?>">
        <input type="hidden" name="day" value="<?= (int) $selectedDay ?>">
        <div class="destination-filter-right">
            <div class="table-header destination-inline-header">
                <h2 class="section-title">Schedules</h2>
            </div>
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon" aria-hidden="true"></i>
                <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
        </div>
    </form>
</div>

<?php require __DIR__ . '/schedule.view.php'; ?>

<div class="destination-filter">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="purpose" value="<?= htmlspecialchars($selectedPurpose, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="month" value="<?= htmlspecialchars($currentMonth) ?>">
        <input type="hidden" name="year" value="<?= (int) $currentYear ?>">
        <input type="hidden" name="day" value="<?= (int) $selectedDay ?>">
        <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?= htmlspecialchars($searchTerm) ?>">
        <div class="filter-container">
            <?php if (!$isScheduleView): ?>
            <button type="button" id="addNewTourBtn" class="btn-primary-custom">
                <i class="fas fa-plus"></i>
                Add New Tour
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($isScheduleView): ?>
    <?php require __DIR__ . '/schedule.view.php'; ?>
<?php else: ?>
    <?php require __DIR__ . '/tour_rates.view.php'; ?>
<?php endif; ?>

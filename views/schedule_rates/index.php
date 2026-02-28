<div class="destination-filter">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="purpose" value="<?= htmlspecialchars($selectedPurpose, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="month" value="<?= htmlspecialchars($currentMonth) ?>">
        <input type="hidden" name="year" value="<?= (int) $currentYear ?>">
        <input type="hidden" name="day" value="<?= (int) $selectedDay ?>">
        <div class="destination-filter-right">
            <div class="table-header destination-inline-header">
                <h2 class="section-title"><?= $isScheduleView ? 'Schedules' : 'Tour Rates &amp; Availability' ?></h2>
            </div>
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon" aria-hidden="true"></i>
                <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
        </div>
        <div class="filter-container">
            <?php if (!$isScheduleView): ?>
            
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($isScheduleView): ?>
    <?php require __DIR__ . '/schedule.view.php'; ?>
<?php else: ?>
    <?php require __DIR__ . '/tour_rates.view.php'; ?>
<?php endif; ?>

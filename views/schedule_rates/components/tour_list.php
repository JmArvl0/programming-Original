<div class="widget-title">
    Tours on <?= htmlspecialchars($selectedDateLabel) ?>
    <span class="badge"><?= count($selectedDateTours) ?> tours</span>
</div>

<?php if (!empty($selectedDateError)): ?>
<div class="no-guests-message"><?= htmlspecialchars($selectedDateError) ?></div>
<?php endif; ?>

<div class="tour-list-section">
    <h4 class="panel-subtitle">Scheduled Tours</h4>
    <?php if (!empty($selectedDateTours)): ?>
    <div class="tour-list" id="tourList">
        <?php foreach ($selectedDateTours as $index => $tour): ?>
            <?php
            $tourId = (string) ($tour['tour_id'] ?? ('tour-' . $index));
            $tourStatus = (string) ($tour['status'] ?? 'available');
            $tourName = (string) ($tour['tour_name'] ?? 'N/A');
            $tourDestination = (string) ($tour['destination'] ?? 'N/A');
            $tourDeparture = (string) ($tour['departure_time'] ?? '');
            $tourBooked = (int) ($tour['booked'] ?? 0);
            $tourCapacity = (int) ($tour['capacity'] ?? 0);
            ?>
        <button
            type="button"
            class="tour-list-item <?= htmlspecialchars($tourStatus) ?> <?= $index === 0 ? 'active' : '' ?>"
            data-tour-id="<?= htmlspecialchars($tourId) ?>">
            <span class="tour-list-name"><?= htmlspecialchars($tourName) ?></span>
            <span class="tour-list-meta"><?= htmlspecialchars($tourDestination) ?><?= $tourDeparture !== '' ? ' | ' . htmlspecialchars($tourDeparture) : '' ?></span>
            <span class="tour-list-count"><?= $tourBooked ?>/<?= $tourCapacity ?> booked</span>
        </button>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-guests-message">No tours scheduled for this date.</div>
    <?php endif; ?>
</div>

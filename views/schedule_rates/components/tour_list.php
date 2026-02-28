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
    <div class="tour-cards-grid tour-list" id="tourList">
        <?php foreach ($selectedDateTours as $index => $tour): ?>
            <?php
            $tourId = (string) ($tour['tour_id'] ?? ('tour-' . $index));
            $tourStatus = (string) ($tour['status'] ?? 'available');
            $tourName = (string) ($tour['tour_name'] ?? 'N/A');
            $tourDestination = (string) ($tour['destination'] ?? 'N/A');
            $tourDeparture = (string) ($tour['departure_time'] ?? '');
            $tourBooked = (int) ($tour['booked'] ?? 0);
            $tourCapacity = (int) ($tour['capacity'] ?? 0);
            $availabilityPercent = $tourCapacity > 0 ? round(($tourBooked / $tourCapacity) * 100, 1) : 0;
            ?>
        <div
            role="button"
            tabindex="0"
            class="tour-card tour-list-item <?= htmlspecialchars($tourStatus) ?> <?= $index === 0 ? 'active' : '' ?>"
            data-tour-id="<?= htmlspecialchars($tourId) ?>">
            <div class="tour-thumb" aria-hidden="true">
                <div class="tour-thumb-placeholder"></div>
            </div>
            <div class="card-header">
                <div class="card-title">
                    <span class="tour-list-name"><?= htmlspecialchars($tourName) ?></span>
                    <div class="card-subtitle"><span class="tour-list-meta"><?= htmlspecialchars($tourDestination) ?><?= $tourDeparture !== '' ? ' | ' . htmlspecialchars($tourDeparture) : '' ?></span></div>
                </div>
                <div class="card-rate"><span class="tour-list-count"><?= $tourBooked ?>/<?= $tourCapacity ?> booked</span></div>
            </div>
            <div class="card-body">
                <div class="card-meta">
                    <div class="meta-item">
                        <div class="meta-label">Booked</div>
                        <div class="meta-value"><strong><?= $tourBooked ?></strong> / <?= $tourCapacity ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Fill</div>
                        <div class="meta-value"><div class="progress-bar small"><div class="progress-fill" style="width: <?= 100 - $availabilityPercent ?>%;"></div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-guests-message">No tours scheduled for this date.</div>
    <?php endif; ?>
</div>

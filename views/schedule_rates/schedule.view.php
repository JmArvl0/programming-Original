<div class="schedule-view-wrap">
    <div class="table-header">
        <h2 class="section-title">Schedules</h2>
    </div>

    <div class="calendar-container">
        <?php require __DIR__ . '/components/calendar.php'; ?>

        <div class="tour-booking-panel">
            <?php require __DIR__ . '/components/tour_list.php'; ?>
            <?php require __DIR__ . '/components/booked_guests.php'; ?>
        </div>
    </div>
</div>

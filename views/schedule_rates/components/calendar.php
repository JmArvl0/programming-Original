<div class="calendar-widget">
    <div class="calendar-header">
        <div class="calendar-nav"><button id="prevMonth" type="button" aria-label="Previous month">&larr;</button></div>
        <div class="calendar-period-selectors">
            <select id="monthSelect">
                <?php foreach ($validMonths as $month): ?>
                <option value="<?= htmlspecialchars($month) ?>" <?= $currentMonth === $month ? 'selected' : '' ?>><?= htmlspecialchars($month) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="yearSelect">
                <?php foreach ($yearOptions as $year): ?>
                <option value="<?= (int) $year ?>" <?= (int) $currentYear === (int) $year ? 'selected' : '' ?>><?= (int) $year ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="calendar-nav"><button id="nextMonth" type="button" aria-label="Next month">&rarr;</button></div>
    </div>
    <div class="calendar-weekdays">
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    </div>
    <div class="calendar-grid">
        <?php foreach ($calendarDays as $day): ?>
            <?php if (empty($day['day'])): ?>
            <div class="calendar-cell calendar-day empty disabled"></div>
            <?php else: ?>
                <?php
                $statusClass = $day['available'] ?? 'available';
                $isToday = ((int) $day['day'] === $todayDay) && ($currentMonth === $todayMonth) && ((int) $currentYear === $todayYear);
                ?>
            <div
                class="calendar-cell calendar-day <?= $statusClass ?> <?= (int) $day['day'] === (int) $selectedDay ? 'selected' : '' ?> <?= $isToday ? 'today' : '' ?>"
                data-day="<?= (int) $day['day'] ?>"
                data-available="<?= htmlspecialchars($statusClass) ?>"
                data-slots="<?= (int) ($day['availableSlots'] ?? 0) ?>/<?= (int) ($day['totalSlots'] ?? 0) ?>"
                title="<?= ucfirst((string) $statusClass) ?> - <?= (int) ($day['availableSlots'] ?? 0) ?>/<?= (int) ($day['totalSlots'] ?? 0) ?> slots">
                <div class="date-number"><?= (int) $day['day'] ?></div>
                <div class="cell-footer"><span class="status-dot <?= htmlspecialchars($statusClass) ?>"></span></div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="selected-date-details">
        <div class="selected-date-title">
            <i class="fas fa-calendar-alt"></i>
            <span id="selectedDateText"><?= htmlspecialchars($currentMonth) ?> <?= (int) $selectedDay ?>, <?= (int) $currentYear ?></span>
        </div>
        <div class="date-metrics">
            <div class="metric-item">
                <div class="metric-label">Available Slots</div>
                <div class="metric-value" id="availableSlots"><?= (int) ($selectedDateData['availableSlots'] ?? 0) ?>/<?= (int) ($selectedDateData['totalSlots'] ?? 0) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Booked Slots</div>
                <div class="metric-value"><?= (int) ($selectedDateData['bookedSlots'] ?? 0) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Scheduled Tours</div>
                <div class="metric-value"><?= count($selectedDateTours) ?></div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Date Status</div>
                <div class="metric-value"><?= htmlspecialchars(ucfirst((string) ($selectedDateData['available'] ?? 'available'))) ?></div>
            </div>
        </div>
    </div>
</div>

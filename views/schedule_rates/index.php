<div class="destination-filter">
    <h2 class="section-title">Schedules</h2>
    <form method="GET" action="" class="filter-form">
        <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="filter-dropdown">
            <select name="destination" class="filter-select" onchange="this.form.submit()">
                <option value="all" <?php echo $selectedDestination === 'all' ? 'selected' : ''; ?>>All Destinations</option>
                <?php foreach ($destinations as $destination): ?>
                <option value="<?php echo htmlspecialchars($destination, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedDestination === $destination ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($destination, ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="btn-primary-custom" onclick="addNewTour()">
            <i class="fas fa-plus"></i>
            Add New Tour
        </button>
    </form>
</div>

<div class="calendar-container">
    <div class="calendar-widget">
        <div class="calendar-header">
            <div class="calendar-nav"><button id="prevMonth" type="button" aria-label="Previous month">&larr;</button></div>
            <div class="calendar-period-selectors">
                <select id="monthSelect">
                    <?php foreach ($validMonths as $month): ?>
                    <option value="<?php echo $month; ?>" <?php echo $currentMonth === $month ? 'selected' : ''; ?>><?php echo $month; ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="yearSelect">
                    <?php for ($year = 2024; $year <= 2026; $year++): ?>
                    <option value="<?php echo $year; ?>" <?php echo $currentYear == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endfor; ?>
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
                    class="calendar-cell calendar-day <?php echo $statusClass; ?> <?php echo $day['day'] == $selectedDay ? 'selected' : ''; ?> <?php echo $isToday ? 'today' : ''; ?>"
                    data-day="<?php echo $day['day']; ?>"
                    data-available="<?php echo $statusClass; ?>"
                    data-slots="<?php echo $day['availableSlots'] ?? 0; ?>/<?php echo $day['totalSlots'] ?? 0; ?>"
                    title="<?php echo ucfirst($statusClass); ?> - <?php echo $day['availableSlots'] ?? 0; ?>/<?php echo $day['totalSlots'] ?? 0; ?> slots">
                    <div class="date-number"><?php echo $day['day']; ?></div>
                    <div class="cell-footer"><span class="status-dot <?php echo $statusClass; ?>"></span></div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title">
            <?php
            $weekStart = date('M d', strtotime('this week'));
            $weekEnd = date('M d', strtotime('this week +6 days'));
            ?>
            Week of <?php echo $weekStart; ?> - <?php echo $weekEnd; ?>
            <span class="badge">This Week</span>
        </div>
        <div class="availability-calendar-week">
            <?php foreach ($weekDates as $day): ?>
            <div class="availability-day-week <?php echo $day['status']; ?> <?php echo $day['isToday'] ? 'selected' : ''; ?>"
                data-day="<?php echo $day['dayNum']; ?>"
                data-date="<?php echo $day['date']; ?>"
                data-slots="<?php echo $day['availableSlots']; ?>/<?php echo $day['totalSlots']; ?>">
                <div class="day-name"><?php echo $day['dayName']; ?></div>
                <div class="date-number"><?php echo $day['dayNum']; ?></div>
                <div class="slots-indicator">
                    <span class="slot-dots">
                        <?php
                        $dots = (int) ceil($day['availableSlots'] / 10);
                        for ($i = 0; $i < min($dots, 5); $i++) {
                            echo '<span class="dot"></span>';
                        }
                        if ($day['availableSlots'] > 50) {
                            echo '<span class="dot-more">+</span>';
                        }
                        ?>
                    </span>
                </div>
                <div class="slot-count"><?php echo $day['availableSlots']; ?> slots</div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="selected-date-details">
            <div class="selected-date-title">
                <i class="fas fa-calendar-alt"></i>
                <span id="selectedDateText"><?php echo $currentMonth; ?> <?php echo $selectedDay; ?>, <?php echo $currentYear; ?></span>
            </div>
            <div class="date-metrics">
                <div class="metric-item">
                    <div class="metric-label">Available Slots</div>
                    <div class="metric-value" id="availableSlots"><?php echo $selectedDateData['availableSlots'] ?? 0; ?>/<?php echo $selectedDateData['totalSlots'] ?? 0; ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Total Guests</div>
                    <div class="metric-value"><?php echo $stats['totalGuests']; ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Paid Guests</div>
                    <div class="metric-value"><?php echo $stats['paidGuests']; ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Avg. Rate</div>
                    <div class="metric-value">PHP<?php echo number_format($stats['avgRate'], 0); ?></div>
                </div>
            </div>
        </div>

        <div class="week-navigation">
            <button class="week-nav-btn" id="prevWeek"><i class="fas fa-chevron-left"></i> Prev Week</button>
            <button class="week-nav-btn" id="nextWeek">Next Week <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<div class="table-section guests-by-date-section">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">
            Guests for <span id="guestsSelectedDateLabel"><?php echo htmlspecialchars($selectedDateLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </h2>
        <div class="table-controls">
            <span class="status-badge badge-blue guest-day-count">
                <span class="status-dot status-blue"></span>
                <?php echo count($guestsForSelectedDate['rows']); ?> Scheduled
            </span>
        </div>
    </div>

    <?php if (!empty($guestsForSelectedDate['error'])): ?>
    <div class="alert alert-warning py-2 px-3 mb-3"><?php echo htmlspecialchars($guestsForSelectedDate['error'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (empty($guestsForSelectedDate['rows'])): ?>
    <div class="no-guests-message">No guests scheduled for this date.</div>
    <?php else: ?>
    <div class="guests-list-compact">
        <?php foreach ($guestsForSelectedDate['rows'] as $guestBooking): ?>
        <article class="guest-date-card">
            <div class="guest-date-card-header">
                <h3 class="guest-name mb-0"><?php echo htmlspecialchars($guestBooking['guest_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <span class="status-badge <?php echo $guestBooking['booking_status'] === 'Reserved' ? 'badge-yellow' : 'badge-green'; ?>">
                    <span class="status-dot <?php echo $guestBooking['booking_status'] === 'Reserved' ? 'status-yellow' : 'status-green'; ?>"></span>
                    <?php echo htmlspecialchars($guestBooking['booking_status'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
            <div class="guest-date-fields">
                <div class="guest-field">
                    <span class="guest-field-label">Tour</span>
                    <span class="guest-field-value"><?php echo htmlspecialchars($guestBooking['tour_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="guest-field">
                    <span class="guest-field-label">Destination</span>
                    <span class="guest-field-value"><?php echo htmlspecialchars($guestBooking['destination'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="guest-field">
                    <span class="guest-field-label">Seat/Slot</span>
                    <span class="guest-field-value"><?php echo htmlspecialchars($guestBooking['seat_slot'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php if (!empty($guestBooking['departure_time'])): ?>
                <div class="guest-field">
                    <span class="guest-field-label">Departure</span>
                    <span class="guest-field-value"><?php echo htmlspecialchars($guestBooking['departure_time'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($guestsForSelectedDate['tourStats'])): ?>
    <div class="tour-capacity-grid">
        <?php foreach ($guestsForSelectedDate['tourStats'] as $tourStat): ?>
        <div class="tour-capacity-item <?php echo $tourStat['is_full'] ? 'full' : ''; ?>">
            <div class="tour-capacity-title-wrap">
                <strong><?php echo htmlspecialchars($tourStat['tour_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if ($tourStat['is_full']): ?>
                <span class="status-badge badge-red"><span class="status-dot status-red"></span>FULL</span>
                <?php endif; ?>
            </div>
            <div class="tour-capacity-sub"><?php echo htmlspecialchars($tourStat['destination'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="tour-capacity-meta">
                Booked: <?php echo (int) $tourStat['booked']; ?>
                <?php if (!empty($tourStat['capacity'])): ?>/ <?php echo (int) $tourStat['capacity']; ?><?php endif; ?>
                <?php if (!empty($tourStat['departure_time'])): ?> | Departure: <?php echo htmlspecialchars($tourStat['departure_time'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<div class="table-section">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">
            Guest List
            <span class="section-subtitle"><?php echo $selectedDestination === 'all' ? 'All Destinations' : htmlspecialchars($selectedDestination, ENT_QUOTES, 'UTF-8'); ?></span>
        </h2>
        <div class="table-controls">
            <a href="#" class="see-all-link" onclick="viewAllGuests()"><span>See All</span><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Rate</th>
                    <th>Booking Date</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($guests, 0, 6) as $guest): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($guest['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td>PHP<?php echo htmlspecialchars($guest['rate'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($guest['bookingDate'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="status-badge badge-<?php echo htmlspecialchars($guest['documentColor'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="status-dot status-<?php echo htmlspecialchars($guest['documentColor'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <?php echo htmlspecialchars($guest['documentStatus'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge badge-<?php echo htmlspecialchars($guest['paymentColor'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="status-dot status-<?php echo htmlspecialchars($guest['paymentColor'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <?php echo htmlspecialchars($guest['paymentStatus'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-view" onclick="viewGuest(<?php echo (int) $guest['id']; ?>)"><i class="fas fa-eye"></i> View</button>
                            <button class="btn-edit" onclick="editGuest(<?php echo (int) $guest['id']; ?>)"><i class="fas fa-edit"></i> Edit</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-section">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">Tour Rates &amp; Availability</h2>
        <div class="table-controls">
            <a href="#" class="see-all-link" onclick="viewAllTours()"><span>See All</span><i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tour</th>
                    <th>Rate</th>
                    <th>Availability</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tours as $tour): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($tour['destination'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </td>
                    <td>PHP<?php echo htmlspecialchars($tour['rate'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="progress-container">
                            <span><?php echo (int) $tour['available']; ?>/<?php echo (int) $tour['capacity']; ?></span>
                            <div class="progress-bar" style="flex: 1; max-width: 100px; margin-left: 10px;">
                                <div class="progress-fill" style="width: <?php echo ($tour['booked'] / $tour['capacity']) * 100; ?>%; background-color: <?php echo $tour['available'] == 0 ? 'var(--danger-color)' : ($tour['available'] < 5 ? 'var(--warning-color)' : 'var(--success-color)'); ?>;"></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($tour['duration'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="status-badge badge-<?php echo htmlspecialchars($tour['statusColor'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="status-dot status-<?php echo htmlspecialchars($tour['statusColor'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <?php echo htmlspecialchars($tour['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-view" onclick="viewTour(<?php echo (int) $tour['id']; ?>)"><i class="fas fa-eye"></i> View</button>
                            <button class="btn-manage" onclick="manageTour(<?php echo (int) $tour['id']; ?>)"><i class="fas fa-cog"></i> Manage</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

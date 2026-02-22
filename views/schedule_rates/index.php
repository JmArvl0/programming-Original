<div class="destination-filter">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="month" value="<?php echo htmlspecialchars($currentMonth, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="year" value="<?php echo (int) $currentYear; ?>">
        <input type="hidden" name="day" value="<?php echo (int) $selectedDay; ?>">
        <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="filter-container">
            <p>Looking for:</p>
            <div class="filter-dropdown">
                <select name="purpose" class="filter-select" onchange="this.form.submit()">
                    <option value="schedule" <?php echo $selectedPurpose === 'schedule' ? 'selected' : ''; ?>>Schedule</option>
                    <option value="tour_rates" <?php echo $selectedPurpose === 'tour_rates' ? 'selected' : ''; ?>>Tour Rates</option>
                </select>
            </div>
            <button type="button" class="btn-primary-custom" onclick="addNewTour()">
                <i class="fas fa-plus"></i>
                Add New Tour
            </button>
        </div>
    </form>
</div>

<?php if ($selectedPurpose === 'schedule'): ?>
<div class="schedule-view-wrap">
<div class="table-header">
        <h2 class="section-title">Schedules</h2>    
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
                    <div class="metric-label">Booked Slots</div>
                    <div class="metric-value"><?php echo (int) ($selectedDateData['bookedSlots'] ?? 0); ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Scheduled Tours</div>
                    <div class="metric-value"><?php echo (int) count($selectedDateTours); ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Date Status</div>
                    <div class="metric-value"><?php echo htmlspecialchars(ucfirst((string) ($selectedDateData['available'] ?? 'available')), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="tour-booking-panel">
        <div class="widget-title">
            Tours on <?php echo htmlspecialchars($selectedDateLabel, ENT_QUOTES, 'UTF-8'); ?>
            <span class="badge"><?php echo (int) count($selectedDateTours); ?> tours</span>
        </div>

        <?php if (!empty($selectedDateError)): ?>
        <div class="no-guests-message"><?php echo htmlspecialchars($selectedDateError, ENT_QUOTES, 'UTF-8'); ?></div>
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
                    class="tour-list-item <?php echo htmlspecialchars($tourStatus, ENT_QUOTES, 'UTF-8'); ?> <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-tour-id="<?php echo htmlspecialchars($tourId, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="tour-list-name"><?php echo htmlspecialchars($tourName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="tour-list-meta"><?php echo htmlspecialchars($tourDestination, ENT_QUOTES, 'UTF-8'); ?><?php echo $tourDeparture !== '' ? ' | ' . htmlspecialchars($tourDeparture, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                    <span class="tour-list-count"><?php echo $tourBooked; ?>/<?php echo $tourCapacity; ?> booked</span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-guests-message">No tours scheduled for this date.</div>
            <?php endif; ?>
        </div>

        <div class="booked-guests-section">
            <h4 class="panel-subtitle" id="bookedGuestsTitle">Booked Guests</h4>
            <div class="booked-guest-list" id="bookedGuestList">
                <?php if (!empty($selectedDateBookings)): ?>
                    <?php foreach ($selectedDateBookings as $booking): ?>
                        <?php
                        $tourId = (string) ($booking['tour_id'] ?? '');
                        $guestName = (string) ($booking['guest_name'] ?? 'Unknown Guest');
                        $bookingStatus = (string) ($booking['booking_status'] ?? 'Confirmed');
                        $seatSlot = isset($booking['seat_slot']) && $booking['seat_slot'] !== '' ? (string) $booking['seat_slot'] : 'N/A';
                        ?>
                    <div class="booked-guest-item" data-tour-id="<?php echo htmlspecialchars($tourId, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="booked-guest-name"><?php echo htmlspecialchars($guestName, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="booked-guest-meta">
                            <span><?php echo htmlspecialchars($bookingStatus, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>Seat/Slot: <?php echo htmlspecialchars($seatSlot, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="no-guests-message" id="noBookingsMessage">No booked guests for this date yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<?php else: ?>

<div class="tour-rates-view-wrap">
    <div class="table-header">
            <h2 class="section-title" style="margin: 0;">Tour Rates &amp; Availability</h2>
    </div>
    <div class="table-section tour-rates-section">
        <div class="table-wrapper">
            <table class="tour-rates-table">
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
                                    <div class="progress-fill" style="width: <?php echo ((int) $tour['booked'] / max((int) $tour['capacity'], 1)) * 100; ?>%; background-color: <?php echo (int) $tour['available'] === 0 ? 'var(--danger-color)' : ((int) $tour['available'] < 5 ? 'var(--warning-color)' : 'var(--success-color)'); ?>;"></div>
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
        <div class="table-footer d-flex justify-content-between align-items-center">
            <div class="entries-info">
                Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?php echo count($tours); ?></span> entries
            </div>
            <div class="pagination-wrap">
                <nav aria-label="...">
                    <ul class="pagination mb-0" id="tablePagination">
                        <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item active"><a class="page-link" href="#" aria-current="page">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

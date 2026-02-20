<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "Schedule & Rates";
$pageSubtitle = "Manage tour schedules, availability, and pricing";

// Generate random tour data
function generateRandomTour($index) {
    $tourNames = [
        'Kyoto, Japan Cultural Walk',
        'Bali, Indonesia Bali Relax', 
        'Boracay, Philippines Boracay Escape',
        'Paris, France City Tour',
        'Rome, Italy Historical Journey',
        'Sydney, Australia Coastal Adventure',
        'New York, USA Urban Experience',
        'Bangkok, Thailand City Discovery',
        'Dubai, UAE Desert Expedition',
        'Cape Town, South Africa Safari'
    ];
    
    $destinations = [
        'Kyoto, Japan',
        'Bali, Indonesia',
        'Boracay, Philippines',
        'Paris, France',
        'Rome, Italy',
        'Sydney, Australia',
        'New York, USA',
        'Bangkok, Thailand',
        'Dubai, UAE',
        'Cape Town, South Africa'
    ];
    
    $tourStatuses = ['OPEN', 'CLOSED', 'FULL', 'UPCOMING'];
    $statusColors = ['green', 'red', 'yellow', 'blue'];
    
    // Generate random capacity between 10-50
    $capacity = rand(10, 50);
    // Generate random booked seats (0 to capacity)
    $booked = rand(0, $capacity);
    // Calculate available seats
    $available = $capacity - $booked;
    
    // Determine status based on availability
    if ($available === 0) {
        $status = 'FULL';
        $statusColor = 'red';
    } elseif ($available < 5) {
        $status = 'LIMITED';
        $statusColor = 'yellow';
    } else {
        $status = $tourStatuses[array_rand($tourStatuses)];
        $statusColor = $status === 'CLOSED' ? 'red' : ($status === 'FULL' ? 'red' : ($status === 'UPCOMING' ? 'blue' : 'green'));
    }
    
    // Generate random rate between 5,000 and 50,000
    $rate = number_format(rand(5000, 50000));
    
    // Generate random dates for the next 90 days
    $tourDate = date('Y-m-d', strtotime('+' . rand(1, 90) . ' days'));
    
    return [
        'id' => $index + 1,
        'name' => $tourNames[$index % count($tourNames)],
        'destination' => $destinations[$index % count($destinations)],
        'rate' => $rate,
        'capacity' => $capacity,
        'booked' => $booked,
        'available' => $available,
        'status' => $status,
        'statusColor' => $statusColor,
        'tourDate' => $tourDate,
        'duration' => rand(3, 14) . ' days',
        'waitlist' => rand(0, 10),
        'documentsReady' => rand(0, $booked)
    ];
}

// Generate random guest data
function generateRandomGuest($index) {
    $firstNames = ['Vanessa', 'Mahinay', 'Kristina', 'Maria', 'Joseph', 'Mark', 'James', 'Sarah', 'Michael', 'Jennifer'];
    $lastNames = ['Radaza', 'Silencio', 'Roses', 'Alvares', 'De Guzman', 'Villotes', 'Smith', 'Johnson', 'Williams', 'Brown'];
    
    $documentStatuses = ['Ready', 'Pending', 'Missing', 'Processing'];
    $paymentStatuses = ['Paid', 'Partial', 'Pending', 'Overdue'];
    $statusColors = ['green', 'yellow', 'red', 'blue'];
    
    $documentStatus = $documentStatuses[array_rand($documentStatuses)];
    $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
    
    return [
        'id' => $index + 1,
        'name' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
        'rate' => number_format(rand(5000, 50000)),
        'documentStatus' => $documentStatus,
        'documentColor' => $statusColors[array_rand($statusColors)],
        'paymentStatus' => $paymentStatus,
        'paymentColor' => $paymentStatus === 'Paid' ? 'green' : ($paymentStatus === 'Partial' ? 'yellow' : 'red'),
        'bookingDate' => date('m/d/Y', strtotime('-' . rand(0, 30) . ' days')),
        'tourId' => rand(1, 10)
    ];
}

// Generate data
$tours = [];
for ($i = 0; $i < 10; $i++) {
    $tours[] = generateRandomTour($i);
}

$guests = [];
for ($i = 0; $i < 20; $i++) {
    $guests[] = generateRandomGuest($i);
}

// Filter functionality
$selectedDestination = isset($_GET['destination']) ? $_GET['destination'] : 'all';
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('F');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$selectedDayParam = isset($_GET['day']) ? (int)$_GET['day'] : (int)date('j');

// Filter tours by destination
if ($selectedDestination !== 'all') {
    $tours = array_filter($tours, function($tour) use ($selectedDestination) {
        return $tour['destination'] === $selectedDestination;
    });
}

// Filter guests by search
if ($searchTerm) {
    $guests = array_filter($guests, function($guest) use ($searchTerm) {
        return stripos($guest['name'], $searchTerm) !== false;
    });
}

// Get unique destinations for filter dropdown
$destinations = array_unique(array_column($tours, 'destination'));

// Current month and year (driven by filters)
$validMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December'];
$currentMonth = in_array($selectedMonth, $validMonths, true) ? $selectedMonth : date('F');
$currentYear = (is_numeric($selectedYear) && (int)$selectedYear >= 2024 && (int)$selectedYear <= 2026)
    ? (int)$selectedYear
    : (int)date('Y');
$todayMonth = date('F');
$todayYear = (int)date('Y');
$todayDay = (int)date('j');

// Generate calendar data for current month
$daysInMonth = date('t', strtotime("$currentMonth $currentYear"));
$firstDayOfWeek = date('w', strtotime("first day of $currentMonth $currentYear"));

// Generate calendar days
$calendarDays = [];
// Add empty days for alignment
for ($i = 0; $i < $firstDayOfWeek; $i++) {
    $calendarDays[] = ['day' => '', 'available' => null];
}

// Add actual days with random availability
for ($day = 1; $day <= $daysInMonth; $day++) {
    $dateStr = "$currentMonth $day, $currentYear";
    $totalSlots = rand(20, 50);
    $bookedSlots = rand(0, $totalSlots);
    $availableSlots = $totalSlots - $bookedSlots;
    
    // Determine availability status
    if ($availableSlots === 0) {
        $status = 'full';
    } elseif ($availableSlots < 5) {
        $status = 'limited';
    } else {
        $status = 'available';
    }
    
    $calendarDays[] = [
        'day' => $day,
        'available' => $status,
        'totalSlots' => $totalSlots,
        'bookedSlots' => $bookedSlots,
        'availableSlots' => $availableSlots
    ];
}

// Keep a fixed 6-week grid (42 cells) so calendar height stays stable across months
while (count($calendarDays) < 42) {
    $calendarDays[] = ['day' => '', 'available' => null];
}

// Default selected date (query param or first day of month)
$selectedDay = max(1, min($selectedDayParam, $daysInMonth));
$selectedDateData = $calendarDays[$selectedDay + $firstDayOfWeek - 1] ?? $calendarDays[$firstDayOfWeek];

// Statistics for selected date
$stats = [
    'totalGuests' => count($guests),
    'paidGuests' => count(array_filter($guests, function($guest) {
        return $guest['paymentStatus'] === 'Paid';
    })),
    'totalRevenue' => array_sum(array_map(function($guest) {
        return str_replace(',', '', $guest['rate']);
    }, $guests)),
    'avgRate' => count($guests) > 0 ? 
        array_sum(array_map(function($guest) {
            return str_replace(',', '', $guest['rate']);
        }, $guests)) / count($guests) : 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Beyond The Map</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Your custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/schedule-rates.css">
</head>
<body>
    
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content Wrapper -->
    <div id="content-wrapper">
        <div class="content-container">
            <!-- Destination Filter -->
            <div class="destination-filter">
                <h2 class="section-title">Schedules</h2>
                <form method="GET" action="" class="filter-form">
                    <input type="text" name="search" class="search-input" placeholder="Search guests or tours..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <div class="filter-dropdown">
                        <select name="destination" class="filter-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $selectedDestination === 'all' ? 'selected' : ''; ?>>All Destinations</option>
                            <?php foreach ($destinations as $destination): ?>
                                <option value="<?php echo htmlspecialchars($destination); ?>" <?php echo $selectedDestination === $destination ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($destination); ?>
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
            <!-- Calendar Section -->
            <div class="calendar-container">
                <!-- Monthly Calendar (Left) -->
                <div class="calendar-widget">
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button id="prevMonth" type="button" aria-label="Previous month">&larr;</button>
                        </div>
                        <div class="calendar-period-selectors">
                            <select id="monthSelect">
                            <?php foreach ($validMonths as $month): ?>
                                <option value="<?php echo $month; ?>" <?php echo $currentMonth === $month ? 'selected' : ''; ?>>
                                    <?php echo $month; ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                            <select id="yearSelect">
                            <?php for ($year = 2024; $year <= 2026; $year++): ?>
                                <option value="<?php echo $year; ?>" <?php echo $currentYear == $year ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                            </select>
                        </div>
                        <div class="calendar-nav">
                            <button id="nextMonth" type="button" aria-label="Next month">&rarr;</button>
                        </div>
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
                                $isToday = ((int)$day['day'] === $todayDay) && ($currentMonth === $todayMonth) && ((int)$currentYear === $todayYear);
                                ?>
                                <div
                                    class="calendar-cell calendar-day <?php echo $statusClass; ?> <?php echo $day['day'] == $selectedDay ? 'selected' : ''; ?> <?php echo $isToday ? 'today' : ''; ?>"
                                    data-day="<?php echo $day['day']; ?>"
                                    data-available="<?php echo $statusClass; ?>"
                                    data-slots="<?php echo $day['availableSlots'] ?? 0; ?>/<?php echo $day['totalSlots'] ?? 0; ?>"
                                    title="<?php echo ucfirst($statusClass); ?> - <?php echo $day['availableSlots'] ?? 0; ?>/<?php echo $day['totalSlots'] ?? 0; ?> slots">
                                    <div class="date-number"><?php echo $day['day']; ?></div>
                                    <div class="cell-footer">
                                        <span class="status-dot <?php echo $statusClass; ?>"></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Weekly Calendar (Right) -->
                <div class="widget">
                    <div class="widget-title">
                        <?php 
                        // Get current week dates
                        $weekStart = date('M d', strtotime('this week'));
                        $weekEnd = date('M d', strtotime('this week +6 days'));
                        ?>
                        Week of <?php echo $weekStart; ?> - <?php echo $weekEnd; ?>
                        <span class="badge">This Week</span>
                    </div>
                    <div class="availability-calendar-week">
                        <?php
                        // Generate data for this week (7 days)
                        $weekDays = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                        $weekDates = [];
                        
                        for ($i = 0; $i < 7; $i++) {
                            $date = strtotime("this week +$i days");
                            $dayNum = date('j', $date);
                            $dayName = $weekDays[$i];
                            
                            // Generate random availability for each day
                            $totalSlots = rand(20, 50);
                            $bookedSlots = rand(0, $totalSlots);
                            $availableSlots = $totalSlots - $bookedSlots;
                            
                            // Determine availability status
                            if ($availableSlots === 0) {
                                $status = 'full';
                            } elseif ($availableSlots < 5) {
                                $status = 'limited';
                            } else {
                                $status = 'available';
                            }
                            
                            $weekDates[] = [
                                'dayNum' => $dayNum,
                                'dayName' => $dayName,
                                'status' => $status,
                                'availableSlots' => $availableSlots,
                                'totalSlots' => $totalSlots,
                                'date' => date('Y-m-d', $date)
                            ];
                        }
                        
                        foreach ($weekDates as $day):
                            $isToday = date('j') == $day['dayNum'] && date('F') == $currentMonth;
                        ?>
                        <div class="availability-day-week <?php echo $day['status']; ?> <?php echo $isToday ? 'selected' : ''; ?>" 
                            data-day="<?php echo $day['dayNum']; ?>"
                            data-date="<?php echo $day['date']; ?>"
                            data-slots="<?php echo $day['availableSlots']; ?>/<?php echo $day['totalSlots']; ?>">
                            <div class="day-name"><?php echo $day['dayName']; ?></div>
                            <div class="date-number"><?php echo $day['dayNum']; ?></div>
                            <div class="slots-indicator">
                                <span class="slot-dots">
                                    <?php 
                                    // Show dots based on availability
                                    $dots = ceil($day['availableSlots'] / 10); // Each dot represents ~10 slots
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

                    <!-- Selected Date Details -->
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
                                <div class="metric-value">₱<?php echo number_format($stats['avgRate'], 0); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="week-navigation">
                        <button class="week-nav-btn" id="prevWeek">
                            <i class="fas fa-chevron-left"></i> Prev Week
                        </button>
                        <button class="week-nav-btn" id="nextWeek">
                            Next Week <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>


            <!-- Guest List Section -->
            <div class="table-section">
                <div class="table-header">
                    <h2 class="section-title" style="margin: 0;">
                        Guest List
                        <span class="section-subtitle"><?php echo $selectedDestination === 'all' ? 'All Destinations' : $selectedDestination; ?></span>
                    </h2>
                    <div class="table-controls">
                        <a href="#" class="see-all-link" onclick="viewAllGuests()">
                            <span>See All</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
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
                                <td><strong><?php echo $guest['name']; ?></strong></td>
                                <td>₱<?php echo $guest['rate']; ?></td>
                                <td><?php echo $guest['bookingDate']; ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo $guest['documentColor']; ?>">
                                        <span class="status-dot status-<?php echo $guest['documentColor']; ?>"></span>
                                        <?php echo $guest['documentStatus']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo $guest['paymentColor']; ?>">
                                        <span class="status-dot status-<?php echo $guest['paymentColor']; ?>"></span>
                                        <?php echo $guest['paymentStatus']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="viewGuest(<?php echo $guest['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn-edit" onclick="editGuest(<?php echo $guest['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rates Section -->
            <div class="table-section">
                <div class="table-header">
                    <h2 class="section-title" style="margin: 0;">Tour Rates & Availability</h2>
                    <div class="table-controls">
                        <a href="#" class="see-all-link" onclick="viewAllTours()">
                            <span>See All</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
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
                                    <strong><?php echo $tour['name']; ?></strong><br>
                                    <small class="text-muted"><?php echo $tour['destination']; ?></small>
                                </td>
                                <td>₱<?php echo $tour['rate']; ?></td>
                                <td>
                                    <div class="progress-container">
                                        <span><?php echo $tour['available']; ?>/<?php echo $tour['capacity']; ?></span>
                                        <div class="progress-bar" style="flex: 1; max-width: 100px; margin-left: 10px;">
                                            <div class="progress-fill" style="width: <?php echo ($tour['booked'] / $tour['capacity']) * 100; ?>%; 
                                                background-color: <?php echo $tour['available'] == 0 ? 'var(--danger-color)' : ($tour['available'] < 5 ? 'var(--warning-color)' : 'var(--success-color)'); ?>;">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $tour['duration']; ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo $tour['statusColor']; ?>">
                                        <span class="status-dot status-<?php echo $tour['statusColor']; ?>"></span>
                                        <?php echo $tour['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="viewTour(<?php echo $tour['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn-manage" onclick="manageTour(<?php echo $tour['id']; ?>)">
                                            <i class="fas fa-cog"></i> Manage
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Your custom JS -->
    <script src="js/main.js"></script>
    <script src="js/schedule-rates.js"></script>
</body>
</html>


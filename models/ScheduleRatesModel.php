<?php

require_once __DIR__ . '/../config/database.php';

class ScheduleRatesModel
{
    private ?mysqli $conn = null;
    private array $tableColumnsCache = [];
    private array $tableExistsCache = [];

    public function __destruct()
    {
        if ($this->conn instanceof mysqli) {
            closeDBConnection($this->conn);
            $this->conn = null;
        }
    }

    private function getConnection(): ?mysqli
    {
        if ($this->conn instanceof mysqli && !$this->conn->connect_error) {
            return $this->conn;
        }
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
            return null;
        }

        try {
            $conn = @new mysqli(DB_HOST, DB_USER, defined('DB_PASS') ? DB_PASS : '', DB_NAME);
        } catch (Throwable $e) {
            return null;
        }

        if (!($conn instanceof mysqli) || $conn->connect_error) {
            return null;
        }

        $this->conn = $conn;
        return $this->conn;
    }

    public function getPageData(array $query): array
    {
        $tours = [];
        for ($i = 0; $i < 10; $i++) {
            $tours[] = $this->generateRandomTour($i);
        }

        $guests = [];
        for ($i = 0; $i < 20; $i++) {
            $guests[] = $this->generateRandomGuest($i);
        }

        $selectedPurpose = isset($query['purpose']) ? (string) $query['purpose'] : 'schedule';
        $selectedMonth = isset($query['month']) ? (string) $query['month'] : date('F');
        $selectedYear = isset($query['year']) ? (string) $query['year'] : date('Y');
        $searchTerm = isset($query['search']) ? trim((string) $query['search']) : '';
        $selectedDayParam = isset($query['day']) ? (int) $query['day'] : (int) date('j');

        $validPurposes = ['schedule', 'tour_rates'];
        if (!in_array($selectedPurpose, $validPurposes, true)) {
            $selectedPurpose = 'schedule';
        }

        if ($searchTerm !== '') {
            $guests = array_values(array_filter($guests, static function (array $guest) use ($searchTerm): bool {
                return stripos($guest['name'], $searchTerm) !== false;
            }));
        }

        $destinations = array_values(array_unique(array_column($tours, 'destination')));
        $validMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $currentMonth = in_array($selectedMonth, $validMonths, true) ? $selectedMonth : date('F');
        $currentYear = (is_numeric($selectedYear) && (int) $selectedYear >= 2024 && (int) $selectedYear <= 2026)
            ? (int) $selectedYear
            : (int) date('Y');
        $todayMonth = date('F');
        $todayYear = (int) date('Y');
        $todayDay = (int) date('j');

        $selectedMonthNumber = (int) date('n', strtotime('1 ' . $currentMonth . ' 2000'));
        $daysInMonth = (int) date('t', strtotime($currentMonth . ' ' . $currentYear));
        $firstDayOfWeek = (int) date('w', strtotime('first day of ' . $currentMonth . ' ' . $currentYear));
        $monthlyStatus = $this->fetchMonthlyScheduleStatus($currentYear, $selectedMonthNumber);
        if ($monthlyStatus === []) {
            $monthlyStatus = $this->buildFallbackMonthlyStatus($tours, $currentYear, $selectedMonthNumber);
        }

        $calendarDays = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $calendarDays[] = ['day' => '', 'available' => null];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayStatus = $monthlyStatus[$day] ?? [
                'available' => 'available',
                'totalSlots' => 0,
                'bookedSlots' => 0,
                'availableSlots' => 0
            ];
            $calendarDays[] = [
                'day' => $day,
                'available' => $dayStatus['available'],
                'totalSlots' => $dayStatus['totalSlots'],
                'bookedSlots' => $dayStatus['bookedSlots'],
                'availableSlots' => $dayStatus['availableSlots']
            ];
        }

        while (count($calendarDays) < 42) {
            $calendarDays[] = ['day' => '', 'available' => null];
        }

        $selectedDay = max(1, min($selectedDayParam, $daysInMonth));
        $selectedDateData = $calendarDays[$selectedDay + $firstDayOfWeek - 1] ?? $calendarDays[$firstDayOfWeek];
        $selectedDateValue = sprintf('%04d-%02d-%02d', $currentYear, $selectedMonthNumber, $selectedDay);
        $selectedDateLabel = date('F j, Y', strtotime($selectedDateValue));
        $selectedDateSchedule = $this->fetchGuestsForSelectedDate($selectedDateValue);
        $fallbackSelectedTours = $this->buildFallbackToursForDate($tours, $selectedDateValue);
        $selectedDateTours = $selectedDateSchedule['tourList'];
        if ($selectedDateTours === []) {
            $selectedDateTours = $fallbackSelectedTours;
        }
        $selectedDateBookings = $selectedDateSchedule['rows'];
        $selectedDateError = $selectedDateSchedule['error'];

        if ($selectedDateData['totalSlots'] === 0 && isset($selectedDateSchedule['totals'])) {
            $selectedDateData = [
                'day' => $selectedDay,
                'available' => $selectedDateSchedule['totals']['status'],
                'totalSlots' => $selectedDateSchedule['totals']['totalSlots'],
                'bookedSlots' => $selectedDateSchedule['totals']['bookedSlots'],
                'availableSlots' => $selectedDateSchedule['totals']['availableSlots']
            ];
        }

        $stats = [
            'totalGuests' => count($guests),
            'paidGuests' => count(array_filter($guests, static function (array $guest): bool {
                return $guest['paymentStatus'] === 'Paid';
            })),
            'totalRevenue' => array_sum(array_map(static function (array $guest): int {
                return (int) str_replace(',', '', (string) $guest['rate']);
            }, $guests)),
            'avgRate' => count($guests) > 0
                ? array_sum(array_map(static function (array $guest): int {
                    return (int) str_replace(',', '', (string) $guest['rate']);
                }, $guests)) / count($guests)
                : 0
        ];

        return [
            'tours' => $tours,
            'guests' => $guests,
            'selectedPurpose' => $selectedPurpose,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'searchTerm' => $searchTerm,
            'selectedDayParam' => $selectedDayParam,
            'destinations' => $destinations,
            'validMonths' => $validMonths,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'todayMonth' => $todayMonth,
            'todayYear' => $todayYear,
            'todayDay' => $todayDay,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'calendarDays' => $calendarDays,
            'selectedDay' => $selectedDay,
            'selectedDateData' => $selectedDateData,
            'selectedDateValue' => $selectedDateValue,
            'selectedDateLabel' => $selectedDateLabel,
            'selectedDateTours' => $selectedDateTours,
            'selectedDateBookings' => $selectedDateBookings,
            'selectedDateError' => $selectedDateError,
            'stats' => $stats
        ];
    }

    private function getTableColumns(mysqli $conn, string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) {
            return $this->tableColumnsCache[$table];
        }

        $columns = [];
        $escapedTable = $conn->real_escape_string($table);
        $result = $conn->query("SHOW COLUMNS FROM `{$escapedTable}`");
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $result->free();
        }
        $this->tableColumnsCache[$table] = $columns;
        return $columns;
    }

    private function pickExistingColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }
        return null;
    }

    private function tableExists(mysqli $conn, string $tableName): bool
    {
        if (isset($this->tableExistsCache[$tableName])) {
            return $this->tableExistsCache[$tableName];
        }

        $stmt = $conn->prepare('SHOW TABLES LIKE ?');
        if (!$stmt) {
            $this->tableExistsCache[$tableName] = false;
            return $this->tableExistsCache[$tableName];
        }
        $stmt->bind_param('s', $tableName);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result && $result->num_rows > 0;
        $stmt->close();
        $this->tableExistsCache[$tableName] = $exists;
        return $this->tableExistsCache[$tableName];
    }

    private function fetchGuestsForSelectedDate(string $selectedDate): array
    {
        $response = [
            'rows' => [],
            'tourStats' => [],
            'tourList' => [],
            'totals' => ['totalSlots' => 0, 'bookedSlots' => 0, 'availableSlots' => 0, 'status' => 'available'],
            'error' => null
        ];
        $conn = $this->getConnection();
        if (!($conn instanceof mysqli)) {
            $response['error'] = 'Unable to load bookings data.';
            return $response;
        }

        try {
            foreach (['bookings', 'tours', 'guests'] as $tableName) {
                if (!$this->tableExists($conn, $tableName)) {
                    $response['error'] = 'Bookings tables are not available in this environment.';
                    return $response;
                }
            }

            $bookingColumns = $this->getTableColumns($conn, 'bookings');
            $tourColumns = $this->getTableColumns($conn, 'tours');
            $guestColumns = $this->getTableColumns($conn, 'guests');

            $bookingDateCol = $this->pickExistingColumn($bookingColumns, ['booking_date', 'tour_date', 'schedule_date', 'departure_date', 'date']);
            $bookingStatusCol = $this->pickExistingColumn($bookingColumns, ['booking_status', 'status']);
            $bookingTourFkCol = $this->pickExistingColumn($bookingColumns, ['tour_id', 'schedule_id', 'trip_id']);
            $bookingGuestFkCol = $this->pickExistingColumn($bookingColumns, ['guest_id', 'customer_id', 'passenger_id']);
            $bookingSeatCol = $this->pickExistingColumn($bookingColumns, ['seat_number', 'slot_number', 'seat', 'slot']);

            $tourPkCol = $this->pickExistingColumn($tourColumns, ['id', 'tour_id']);
            $guestPkCol = $this->pickExistingColumn($guestColumns, ['id', 'guest_id', 'customer_id']);
            $tourNameCol = $this->pickExistingColumn($tourColumns, ['tour_name', 'name', 'title']);
            $destinationCol = $this->pickExistingColumn($tourColumns, ['destination', 'location']);
            $capacityCol = $this->pickExistingColumn($tourColumns, ['capacity', 'max_capacity', 'total_slots']);
            $departureCol = $this->pickExistingColumn($tourColumns, ['departure_time', 'start_time', 'time']);
            $tourDateCol = $this->pickExistingColumn($tourColumns, ['tour_date', 'schedule_date', 'departure_date', 'date', 'start_date']);

            if (!$bookingDateCol || !$bookingStatusCol || !$bookingTourFkCol || !$bookingGuestFkCol || !$tourPkCol || !$guestPkCol) {
                $response['error'] = 'Bookings schema is missing required columns for operational view.';
                return $response;
            }

            if (in_array('full_name', $guestColumns, true)) {
                $guestNameExpr = "g.`full_name`";
            } elseif (in_array('name', $guestColumns, true)) {
                $guestNameExpr = "g.`name`";
            } elseif (in_array('first_name', $guestColumns, true) && in_array('last_name', $guestColumns, true)) {
                $guestNameExpr = "TRIM(CONCAT_WS(' ', g.`first_name`, g.`last_name`))";
            } else {
                $guestNameExpr = "'Unknown Guest'";
            }

            $tourNameExpr = $tourNameCol ? "t.`{$tourNameCol}`" : "'N/A'";
            $destinationExpr = $destinationCol ? "t.`{$destinationCol}`" : "'N/A'";
            $seatExpr = $bookingSeatCol ? "b.`{$bookingSeatCol}`" : "NULL";
            $departureExpr = $departureCol ? "t.`{$departureCol}`" : "NULL";
            $capacityExpr = $capacityCol ? "CAST(t.`{$capacityCol}` AS UNSIGNED)" : "NULL";
            $tourIdExpr = "t.`{$tourPkCol}`";

            $tourListMap = [];
            if ($tourDateCol) {
                $scheduleSql = "
                    SELECT
                        {$tourIdExpr} AS tour_id,
                        {$tourNameExpr} AS tour_name,
                        {$destinationExpr} AS destination,
                        {$departureExpr} AS departure_time,
                        {$capacityExpr} AS capacity
                    FROM `tours` t
                    WHERE t.`{$tourDateCol}` >= ?
                      AND t.`{$tourDateCol}` < DATE_ADD(?, INTERVAL 1 DAY)
                    ORDER BY tour_name ASC
                ";
                $scheduleStmt = $conn->prepare($scheduleSql);
                if ($scheduleStmt) {
                    $scheduleStmt->bind_param('ss', $selectedDate, $selectedDate);
                    $scheduleStmt->execute();
                    $scheduleResult = $scheduleStmt->get_result();
                    while ($tourRow = $scheduleResult->fetch_assoc()) {
                        $tourId = isset($tourRow['tour_id']) ? (string) $tourRow['tour_id'] : '';
                        if ($tourId === '') {
                            continue;
                        }
                        $tourName = trim((string) ($tourRow['tour_name'] ?? 'N/A'));
                        $destination = trim((string) ($tourRow['destination'] ?? 'N/A'));
                        $departureTime = isset($tourRow['departure_time']) && $tourRow['departure_time'] !== ''
                            ? date('g:i A', strtotime((string) $tourRow['departure_time']))
                            : null;
                        $capacity = isset($tourRow['capacity']) ? (int) $tourRow['capacity'] : 0;
                        $tourListMap[$tourId] = [
                            'tour_id' => $tourId,
                            'tour_name' => $tourName !== '' ? $tourName : 'N/A',
                            'destination' => $destination !== '' ? $destination : 'N/A',
                            'departure_time' => $departureTime,
                            'capacity' => $capacity > 0 ? $capacity : 0,
                            'booked' => 0,
                            'available' => $capacity > 0 ? $capacity : 0,
                            'status' => 'available'
                        ];
                    }
                    $scheduleStmt->close();
                }
            }

            $sql = "
                SELECT
                    {$tourIdExpr} AS tour_id,
                    {$guestNameExpr} AS guest_name,
                    {$tourNameExpr} AS tour_name,
                    {$destinationExpr} AS destination,
                    {$seatExpr} AS seat_slot,
                    b.`{$bookingStatusCol}` AS booking_status,
                    {$departureExpr} AS departure_time,
                    {$capacityExpr} AS capacity
                FROM `bookings` b
                INNER JOIN `tours` t ON b.`{$bookingTourFkCol}` = t.`{$tourPkCol}`
                INNER JOIN `guests` g ON b.`{$bookingGuestFkCol}` = g.`{$guestPkCol}`
                WHERE b.`{$bookingDateCol}` >= ?
                  AND b.`{$bookingDateCol}` < DATE_ADD(?, INTERVAL 1 DAY)
                  AND LOWER(TRIM(b.`{$bookingStatusCol}`)) IN ('confirmed', 'reserved')
                ORDER BY tour_name ASC, guest_name ASC
            ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response['error'] = 'Unable to prepare operational guest query.';
                return $response;
            }

            $stmt->bind_param('ss', $selectedDate, $selectedDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $tourStats = [];

            while ($row = $result->fetch_assoc()) {
                $status = strtolower(trim((string) ($row['booking_status'] ?? '')));
                $statusLabel = ($status === 'reserved') ? 'Reserved' : 'Confirmed';
                $tourId = isset($row['tour_id']) ? (string) $row['tour_id'] : '';
                $guestName = trim((string) ($row['guest_name'] ?? ''));
                $tourName = trim((string) ($row['tour_name'] ?? 'N/A'));
                $destination = trim((string) ($row['destination'] ?? 'N/A'));
                $departureTime = isset($row['departure_time']) && $row['departure_time'] !== ''
                    ? date('g:i A', strtotime((string) $row['departure_time']))
                    : null;
                $seatSlot = isset($row['seat_slot']) ? trim((string) $row['seat_slot']) : '';
                $capacity = isset($row['capacity']) ? (int) $row['capacity'] : 0;

                $response['rows'][] = [
                    'tour_id' => $tourId,
                    'guest_name' => $guestName !== '' ? $guestName : 'Unknown Guest',
                    'tour_name' => $tourName !== '' ? $tourName : 'N/A',
                    'destination' => $destination !== '' ? $destination : 'N/A',
                    'seat_slot' => $seatSlot !== '' ? $seatSlot : null,
                    'booking_status' => $statusLabel,
                    'departure_time' => $departureTime,
                    'capacity' => $capacity > 0 ? $capacity : null
                ];

                $tourKey = $tourId !== '' ? $tourId : ($tourName . '|' . $destination . '|' . ($departureTime ?? ''));
                if (!isset($tourStats[$tourKey])) {
                    $tourStats[$tourKey] = [
                        'tour_id' => $tourId,
                        'tour_name' => $tourName !== '' ? $tourName : 'N/A',
                        'destination' => $destination !== '' ? $destination : 'N/A',
                        'departure_time' => $departureTime,
                        'booked' => 0,
                        'capacity' => $capacity > 0 ? $capacity : null,
                        'is_full' => false
                    ];
                }
                $tourStats[$tourKey]['booked']++;
                if ($tourStats[$tourKey]['capacity'] !== null && $tourStats[$tourKey]['booked'] >= $tourStats[$tourKey]['capacity']) {
                    $tourStats[$tourKey]['is_full'] = true;
                }

                if ($tourId !== '' && isset($tourListMap[$tourId])) {
                    $tourListMap[$tourId]['booked']++;
                } elseif ($tourId !== '') {
                    $resolvedCapacity = $capacity > 0 ? $capacity : 0;
                    $tourListMap[$tourId] = [
                        'tour_id' => $tourId,
                        'tour_name' => $tourName !== '' ? $tourName : 'N/A',
                        'destination' => $destination !== '' ? $destination : 'N/A',
                        'departure_time' => $departureTime,
                        'capacity' => $resolvedCapacity,
                        'booked' => 1,
                        'available' => max($resolvedCapacity - 1, 0),
                        'status' => $this->calculateAvailabilityStatus(1, $resolvedCapacity)
                    ];
                }
            }

            $response['tourStats'] = array_values($tourStats);
            foreach ($tourListMap as $tourId => $tourItem) {
                $capacity = (int) ($tourItem['capacity'] ?? 0);
                $booked = (int) ($tourItem['booked'] ?? 0);
                $available = max($capacity - $booked, 0);
                $tourListMap[$tourId]['available'] = $available;
                $tourListMap[$tourId]['status'] = $this->calculateAvailabilityStatus($booked, $capacity);
            }
            $response['tourList'] = array_values($tourListMap);

            $totalSlots = 0;
            $bookedSlots = 0;
            foreach ($response['tourList'] as $tourItem) {
                $totalSlots += (int) ($tourItem['capacity'] ?? 0);
                $bookedSlots += (int) ($tourItem['booked'] ?? 0);
            }
            $response['totals'] = [
                'totalSlots' => $totalSlots,
                'bookedSlots' => $bookedSlots,
                'availableSlots' => max($totalSlots - $bookedSlots, 0),
                'status' => $this->calculateAvailabilityStatus($bookedSlots, $totalSlots)
            ];
            $stmt->close();
        } catch (Throwable $e) {
            $response['error'] = 'Unable to fetch guests for the selected date.';
        }

        return $response;
    }

    private function fetchMonthlyScheduleStatus(int $year, int $month): array
    {
        $statusByDay = [];
        $conn = $this->getConnection();
        if (!($conn instanceof mysqli)) {
            return $statusByDay;
        }

        try {
            if (!$this->tableExists($conn, 'tours')) {
                return $statusByDay;
            }

            $tourColumns = $this->getTableColumns($conn, 'tours');
            $tourPkCol = $this->pickExistingColumn($tourColumns, ['id', 'tour_id']);
            $tourDateCol = $this->pickExistingColumn($tourColumns, ['tour_date', 'schedule_date', 'departure_date', 'date', 'start_date']);
            $capacityCol = $this->pickExistingColumn($tourColumns, ['capacity', 'max_capacity', 'total_slots']);
            if (!$tourPkCol || !$tourDateCol) {
                return $statusByDay;
            }

            $monthStart = sprintf('%04d-%02d-01', $year, $month);
            $monthEnd = date('Y-m-d', strtotime($monthStart . ' +1 month'));

            $capacityExpr = $capacityCol ? "CAST(t.`{$capacityCol}` AS UNSIGNED)" : "0";
            $tourSql = "
                SELECT
                    DAY(t.`{$tourDateCol}`) AS day_num,
                    t.`{$tourPkCol}` AS tour_id,
                    {$capacityExpr} AS capacity
                FROM `tours` t
                WHERE t.`{$tourDateCol}` >= ?
                  AND t.`{$tourDateCol}` < ?
            ";
            $tourStmt = $conn->prepare($tourSql);
            if (!$tourStmt) {
                return $statusByDay;
            }
            $tourStmt->bind_param('ss', $monthStart, $monthEnd);
            $tourStmt->execute();
            $tourResult = $tourStmt->get_result();

            $tourCapacities = [];
            while ($row = $tourResult->fetch_assoc()) {
                $dayNum = (int) ($row['day_num'] ?? 0);
                $tourId = isset($row['tour_id']) ? (string) $row['tour_id'] : '';
                if ($dayNum < 1 || $tourId === '') {
                    continue;
                }
                $capacity = max((int) ($row['capacity'] ?? 0), 0);
                if (!isset($tourCapacities[$dayNum])) {
                    $tourCapacities[$dayNum] = [];
                }
                $tourCapacities[$dayNum][$tourId] = $capacity;
            }
            $tourStmt->close();

            $tourBookings = [];
            if ($this->tableExists($conn, 'bookings')) {
                $bookingColumns = $this->getTableColumns($conn, 'bookings');
                $bookingDateCol = $this->pickExistingColumn($bookingColumns, ['booking_date', 'tour_date', 'schedule_date', 'departure_date', 'date']);
                $bookingStatusCol = $this->pickExistingColumn($bookingColumns, ['booking_status', 'status']);
                $bookingTourFkCol = $this->pickExistingColumn($bookingColumns, ['tour_id', 'schedule_id', 'trip_id']);

                if ($bookingDateCol && $bookingStatusCol && $bookingTourFkCol) {
                    $bookingSql = "
                        SELECT
                            DAY(b.`{$bookingDateCol}`) AS day_num,
                            b.`{$bookingTourFkCol}` AS tour_id,
                            COUNT(*) AS booked_count
                        FROM `bookings` b
                        WHERE b.`{$bookingDateCol}` >= ?
                          AND b.`{$bookingDateCol}` < ?
                          AND LOWER(TRIM(b.`{$bookingStatusCol}`)) IN ('confirmed', 'reserved')
                        GROUP BY DAY(b.`{$bookingDateCol}`), b.`{$bookingTourFkCol}`
                    ";
                    $bookingStmt = $conn->prepare($bookingSql);
                    if ($bookingStmt) {
                        $bookingStmt->bind_param('ss', $monthStart, $monthEnd);
                        $bookingStmt->execute();
                        $bookingResult = $bookingStmt->get_result();
                        while ($row = $bookingResult->fetch_assoc()) {
                            $dayNum = (int) ($row['day_num'] ?? 0);
                            $tourId = isset($row['tour_id']) ? (string) $row['tour_id'] : '';
                            if ($dayNum < 1 || $tourId === '') {
                                continue;
                            }
                            $bookedCount = max((int) ($row['booked_count'] ?? 0), 0);
                            if (!isset($tourBookings[$dayNum])) {
                                $tourBookings[$dayNum] = [];
                            }
                            $tourBookings[$dayNum][$tourId] = $bookedCount;
                        }
                        $bookingStmt->close();
                    }
                }
            }

            $allDays = array_values(array_unique(array_merge(array_keys($tourCapacities), array_keys($tourBookings))));
            foreach ($allDays as $dayNum) {
                $dayNum = (int) $dayNum;
                $totalSlots = 0;
                $bookedSlots = 0;
                $dayTourCaps = $tourCapacities[$dayNum] ?? [];
                $dayTourBookings = $tourBookings[$dayNum] ?? [];
                $tourIds = array_values(array_unique(array_merge(array_keys($dayTourCaps), array_keys($dayTourBookings))));
                foreach ($tourIds as $tourId) {
                    $capacity = (int) ($dayTourCaps[$tourId] ?? 0);
                    $booked = (int) ($dayTourBookings[$tourId] ?? 0);
                    if ($capacity === 0 && $booked > 0) {
                        $capacity = $booked;
                    }
                    $totalSlots += $capacity;
                    $bookedSlots += $booked;
                }
                $statusByDay[$dayNum] = [
                    'available' => $this->calculateAvailabilityStatus($bookedSlots, $totalSlots),
                    'totalSlots' => $totalSlots,
                    'bookedSlots' => $bookedSlots,
                    'availableSlots' => max($totalSlots - $bookedSlots, 0)
                ];
            }
        } catch (Throwable $e) {
            $statusByDay = [];
        }

        return $statusByDay;
    }

    private function buildFallbackMonthlyStatus(array $tours, int $year, int $month): array
    {
        $statusByDay = [];
        foreach ($tours as $tour) {
            $tourDate = isset($tour['tourDate']) ? strtotime((string) $tour['tourDate']) : false;
            if (!$tourDate) {
                continue;
            }
            if ((int) date('Y', $tourDate) !== $year || (int) date('n', $tourDate) !== $month) {
                continue;
            }
            $dayNum = (int) date('j', $tourDate);
            if (!isset($statusByDay[$dayNum])) {
                $statusByDay[$dayNum] = ['totalSlots' => 0, 'bookedSlots' => 0];
            }
            $statusByDay[$dayNum]['totalSlots'] += max((int) ($tour['capacity'] ?? 0), 0);
            $statusByDay[$dayNum]['bookedSlots'] += max((int) ($tour['booked'] ?? 0), 0);
        }

        foreach ($statusByDay as $dayNum => $summary) {
            $totalSlots = (int) $summary['totalSlots'];
            $bookedSlots = (int) $summary['bookedSlots'];
            $statusByDay[$dayNum] = [
                'available' => $this->calculateAvailabilityStatus($bookedSlots, $totalSlots),
                'totalSlots' => $totalSlots,
                'bookedSlots' => $bookedSlots,
                'availableSlots' => max($totalSlots - $bookedSlots, 0)
            ];
        }

        return $statusByDay;
    }

    private function buildFallbackToursForDate(array $tours, string $selectedDate): array
    {
        $tourList = [];
        foreach ($tours as $tour) {
            if (($tour['tourDate'] ?? '') !== $selectedDate) {
                continue;
            }
            $capacity = max((int) ($tour['capacity'] ?? 0), 0);
            $booked = max((int) ($tour['booked'] ?? 0), 0);
            $tourList[] = [
                'tour_id' => (string) ($tour['id'] ?? ''),
                'tour_name' => (string) ($tour['name'] ?? 'N/A'),
                'destination' => (string) ($tour['destination'] ?? 'N/A'),
                'departure_time' => null,
                'capacity' => $capacity,
                'booked' => $booked,
                'available' => max($capacity - $booked, 0),
                'status' => $this->calculateAvailabilityStatus($booked, $capacity)
            ];
        }
        return $tourList;
    }

    private function calculateAvailabilityStatus(int $bookedSlots, int $totalSlots): string
    {
        if ($totalSlots <= 0) {
            return 'available';
        }
        if ($bookedSlots >= $totalSlots) {
            return 'full';
        }
        $utilization = $bookedSlots / $totalSlots;
        if ($utilization >= 0.70) {
            return 'limited';
        }
        return 'available';
    }

    private function buildWeekDates(string $currentMonth): array
    {
        $weekDays = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $date = strtotime('this week +' . $i . ' days');
            $totalSlots = rand(20, 50);
            $bookedSlots = rand(0, $totalSlots);
            $availableSlots = $totalSlots - $bookedSlots;
            if ($availableSlots === 0) {
                $status = 'full';
            } elseif ($availableSlots < 5) {
                $status = 'limited';
            } else {
                $status = 'available';
            }
            $weekDates[] = [
                'dayNum' => (int) date('j', $date),
                'dayName' => $weekDays[$i],
                'status' => $status,
                'availableSlots' => $availableSlots,
                'totalSlots' => $totalSlots,
                'date' => date('Y-m-d', $date),
                'isToday' => date('j') == date('j', $date) && date('F') == $currentMonth
            ];
        }
        return $weekDates;
    }

    private function generateRandomTour(int $index): array
    {
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
            'Kyoto, Japan', 'Bali, Indonesia', 'Boracay, Philippines', 'Paris, France', 'Rome, Italy',
            'Sydney, Australia', 'New York, USA', 'Bangkok, Thailand', 'Dubai, UAE', 'Cape Town, South Africa'
        ];
        $tourStatuses = ['OPEN', 'CLOSED', 'FULL', 'UPCOMING'];
        $capacity = rand(10, 50);
        $booked = rand(0, $capacity);
        $available = $capacity - $booked;
        if ($available === 0) {
            $status = 'FULL';
            $statusColor = 'red';
        } elseif ($available < 5) {
            $status = 'LIMITED';
            $statusColor = 'yellow';
        } else {
            $status = $tourStatuses[array_rand($tourStatuses)];
            $statusColor = $status === 'CLOSED' ? 'red' : ($status === 'UPCOMING' ? 'blue' : 'green');
        }
        return [
            'id' => $index + 1,
            'name' => $tourNames[$index % count($tourNames)],
            'destination' => $destinations[$index % count($destinations)],
            'rate' => number_format(rand(5000, 50000)),
            'capacity' => $capacity,
            'booked' => $booked,
            'available' => $available,
            'status' => $status,
            'statusColor' => $statusColor,
            'tourDate' => date('Y-m-d', strtotime('+' . rand(1, 90) . ' days')),
            'duration' => rand(3, 14) . ' days',
            'waitlist' => rand(0, 10),
            'documentsReady' => rand(0, $booked)
        ];
    }

    private function generateRandomGuest(int $index): array
    {
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
}

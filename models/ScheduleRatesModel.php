<?php

require_once __DIR__ . '/../config/database.php';

class ScheduleRatesModel
{
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

        $selectedDestination = isset($query['destination']) ? (string) $query['destination'] : 'all';
        $selectedMonth = isset($query['month']) ? (string) $query['month'] : date('F');
        $selectedYear = isset($query['year']) ? (string) $query['year'] : date('Y');
        $searchTerm = isset($query['search']) ? trim((string) $query['search']) : '';
        $selectedDayParam = isset($query['day']) ? (int) $query['day'] : (int) date('j');

        if ($selectedDestination !== 'all') {
            $tours = array_values(array_filter($tours, static function (array $tour) use ($selectedDestination): bool {
                return $tour['destination'] === $selectedDestination;
            }));
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

        $daysInMonth = (int) date('t', strtotime($currentMonth . ' ' . $currentYear));
        $firstDayOfWeek = (int) date('w', strtotime('first day of ' . $currentMonth . ' ' . $currentYear));
        $calendarDays = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $calendarDays[] = ['day' => '', 'available' => null];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
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
            $calendarDays[] = [
                'day' => $day,
                'available' => $status,
                'totalSlots' => $totalSlots,
                'bookedSlots' => $bookedSlots,
                'availableSlots' => $availableSlots
            ];
        }

        while (count($calendarDays) < 42) {
            $calendarDays[] = ['day' => '', 'available' => null];
        }

        $selectedDay = max(1, min($selectedDayParam, $daysInMonth));
        $selectedDateData = $calendarDays[$selectedDay + $firstDayOfWeek - 1] ?? $calendarDays[$firstDayOfWeek];
        $selectedMonthNumber = (int) date('n', strtotime('1 ' . $currentMonth . ' 2000'));
        $selectedDateValue = sprintf('%04d-%02d-%02d', $currentYear, $selectedMonthNumber, $selectedDay);
        $selectedDateLabel = date('F j, Y', strtotime($selectedDateValue));
        $guestsForSelectedDate = $this->fetchGuestsForSelectedDate($selectedDateValue);
        $weekDates = $this->buildWeekDates($currentMonth);

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
            'selectedDestination' => $selectedDestination,
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
            'guestsForSelectedDate' => $guestsForSelectedDate,
            'weekDates' => $weekDates,
            'stats' => $stats
        ];
    }

    private function getTableColumns(mysqli $conn, string $table): array
    {
        $columns = [];
        $escapedTable = $conn->real_escape_string($table);
        $result = $conn->query("SHOW COLUMNS FROM `{$escapedTable}`");
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $result->free();
        }
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
        $stmt = $conn->prepare('SHOW TABLES LIKE ?');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $tableName);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result && $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function fetchGuestsForSelectedDate(string $selectedDate): array
    {
        $response = ['rows' => [], 'tourStats' => [], 'error' => null];
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
            $response['error'] = 'Unable to load bookings data.';
            return $response;
        }

        try {
            $conn = @new mysqli(DB_HOST, DB_USER, defined('DB_PASS') ? DB_PASS : '', DB_NAME);
        } catch (Throwable $e) {
            $response['error'] = 'Unable to load bookings data.';
            return $response;
        }
        if (!($conn instanceof mysqli) || $conn->connect_error) {
            $response['error'] = 'Unable to load bookings data.';
            return $response;
        }

        try {
            foreach (['bookings', 'tours', 'guests'] as $tableName) {
                if (!$this->tableExists($conn, $tableName)) {
                    $response['error'] = 'Bookings tables are not available in this environment.';
                    closeDBConnection($conn);
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

            if (!$bookingDateCol || !$bookingStatusCol || !$bookingTourFkCol || !$bookingGuestFkCol || !$tourPkCol || !$guestPkCol) {
                $response['error'] = 'Bookings schema is missing required columns for operational view.';
                closeDBConnection($conn);
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

            $sql = "
                SELECT
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
                WHERE DATE(b.`{$bookingDateCol}`) = ?
                  AND LOWER(TRIM(b.`{$bookingStatusCol}`)) IN ('confirmed', 'reserved')
                ORDER BY tour_name ASC, guest_name ASC
            ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $response['error'] = 'Unable to prepare operational guest query.';
                closeDBConnection($conn);
                return $response;
            }

            $stmt->bind_param('s', $selectedDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $tourStats = [];

            while ($row = $result->fetch_assoc()) {
                $status = strtolower(trim((string) ($row['booking_status'] ?? '')));
                $statusLabel = ($status === 'reserved') ? 'Reserved' : 'Confirmed';
                $guestName = trim((string) ($row['guest_name'] ?? ''));
                $tourName = trim((string) ($row['tour_name'] ?? 'N/A'));
                $destination = trim((string) ($row['destination'] ?? 'N/A'));
                $departureTime = isset($row['departure_time']) && $row['departure_time'] !== ''
                    ? date('g:i A', strtotime((string) $row['departure_time']))
                    : null;
                $seatSlot = isset($row['seat_slot']) ? trim((string) $row['seat_slot']) : '';
                $capacity = isset($row['capacity']) ? (int) $row['capacity'] : 0;

                $response['rows'][] = [
                    'guest_name' => $guestName !== '' ? $guestName : 'Unknown Guest',
                    'tour_name' => $tourName !== '' ? $tourName : 'N/A',
                    'destination' => $destination !== '' ? $destination : 'N/A',
                    'seat_slot' => $seatSlot !== '' ? $seatSlot : null,
                    'booking_status' => $statusLabel,
                    'departure_time' => $departureTime,
                    'capacity' => $capacity > 0 ? $capacity : null
                ];

                $tourKey = $tourName . '|' . $destination . '|' . ($departureTime ?? '');
                if (!isset($tourStats[$tourKey])) {
                    $tourStats[$tourKey] = [
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
            }

            $response['tourStats'] = array_values($tourStats);
            $stmt->close();
        } catch (Throwable $e) {
            $response['error'] = 'Unable to fetch guests for the selected date.';
        }

        closeDBConnection($conn);
        return $response;
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

<?php

class FacilitiesModel
{
    public function getPageData(array $query): array
    {
        $allowedViews = ['reservation_requests', 'availability_overview', 'coordination_status'];
        $selectedView = isset($query['view']) ? (string) $query['view'] : 'reservation_requests';
        $page = isset($query['page']) ? (int) $query['page'] : 1;
        $perPage = 10;
        if (!in_array($selectedView, $allowedViews, true)) {
            $selectedView = 'reservation_requests';
        }

        $reservationRequests = [];
        for ($i = 0; $i < 22; $i++) {
            $reservationRequests[] = $this->generateReservationRequest($i + 1);
        }

        $facilityAvailability = [];
        for ($i = 0; $i < 12; $i++) {
            $facilityAvailability[] = $this->generateFacilityAvailability($i + 1);
        }

        $coordinationStatuses = [];
        for ($i = 0; $i < 16; $i++) {
            $coordinationStatuses[] = $this->generateCoordinationStatus($i + 1);
        }

        $filterType = isset($query['type']) ? (string) $query['type'] : 'all';
        $searchTerm = isset($query['search']) ? trim((string) $query['search']) : '';

        if ($filterType !== 'all') {
            $reservationRequests = array_values(array_filter(
                $reservationRequests,
                static function (array $request) use ($filterType): bool {
                    return $request['facilityType'] === $filterType;
                }
            ));

            $facilityAvailability = array_values(array_filter(
                $facilityAvailability,
                static function (array $facility) use ($filterType): bool {
                    return $facility['type'] === $filterType;
                }
            ));

            $coordinationStatuses = array_values(array_filter(
                $coordinationStatuses,
                static function (array $coordination) use ($filterType): bool {
                    return $coordination['facilityType'] === $filterType;
                }
            ));
        }

        if ($searchTerm !== '') {
            $reservationRequests = array_values(array_filter(
                $reservationRequests,
                static function (array $request) use ($searchTerm): bool {
                    return stripos($request['customerName'], $searchTerm) !== false
                        || stripos($request['bookingReference'], $searchTerm) !== false
                        || stripos($request['facilityType'], $searchTerm) !== false;
                }
            ));

            $facilityAvailability = array_values(array_filter(
                $facilityAvailability,
                static function (array $facility) use ($searchTerm): bool {
                    return stripos($facility['facilityName'], $searchTerm) !== false
                        || stripos($facility['type'], $searchTerm) !== false;
                }
            ));

            $coordinationStatuses = array_values(array_filter(
                $coordinationStatuses,
                static function (array $coordination) use ($searchTerm): bool {
                    return stripos($coordination['customerName'], $searchTerm) !== false
                        || stripos($coordination['facility'], $searchTerm) !== false
                        || stripos($coordination['assignedStaff'], $searchTerm) !== false;
                }
            ));
        }

        $facilityTypes = $this->extractFacilityTypes($reservationRequests, $facilityAvailability);
        $stats = $this->buildStats($reservationRequests);
        $overviewCards = $this->buildOverviewCards(
            $selectedView,
            $reservationRequests,
            $facilityAvailability,
            $coordinationStatuses
        );

        $activeItemsCount = count($reservationRequests);
        if ($selectedView === 'availability_overview') {
            $activeItemsCount = count($facilityAvailability);
        } elseif ($selectedView === 'coordination_status') {
            $activeItemsCount = count($coordinationStatuses);
        }

        $totalPages = max(1, (int) ceil($activeItemsCount / $perPage));
        $safePage = max(1, min($page, $totalPages));
        $offset = ($safePage - 1) * $perPage;

        if ($selectedView === 'availability_overview') {
            $facilityAvailability = array_slice($facilityAvailability, $offset, $perPage);
        } elseif ($selectedView === 'coordination_status') {
            $coordinationStatuses = array_slice($coordinationStatuses, $offset, $perPage);
        } else {
            $reservationRequests = array_slice($reservationRequests, $offset, $perPage);
        }

        $start = $activeItemsCount > 0 ? $offset + 1 : 0;
        $end = $activeItemsCount > 0 ? min($offset + $perPage, $activeItemsCount) : 0;

        return [
            'reservationRequests' => $reservationRequests,
            'facilityAvailability' => $facilityAvailability,
            'coordinationStatuses' => $coordinationStatuses,
            'selectedView' => $selectedView,
            'filterType' => $filterType,
            'searchTerm' => $searchTerm,
            'facilityTypes' => $facilityTypes,
            'stats' => $stats,
            'overviewCards' => $overviewCards,
            'pagination' => [
                'page' => $safePage,
                'perPage' => $perPage,
                'totalItems' => $activeItemsCount,
                'totalPages' => $totalPages,
                'start' => $start,
                'end' => $end
            ]
        ];
    }

    private function buildStats(array $reservationRequests): array
    {
        $stats = [
            'totalReservationsToday' => count($reservationRequests),
            'pendingRequests' => 0,
            'approved' => 0,
            'inProgress' => 0,
            'completed' => 0
        ];

        foreach ($reservationRequests as $request) {
            switch ($request['status']) {
                case 'Requested':
                    $stats['pendingRequests']++;
                    break;
                case 'Approved':
                    $stats['approved']++;
                    break;
                case 'In Progress':
                    $stats['inProgress']++;
                    break;
                case 'Completed':
                    $stats['completed']++;
                    break;
            }
        }

        return $stats;
    }

    private function buildOverviewCards(
        string $selectedView,
        array $reservationRequests,
        array $facilityAvailability,
        array $coordinationStatuses
    ): array {
        if ($selectedView === 'availability_overview') {
            $totalFacilities = count($facilityAvailability);
            $available = 0;
            $limited = 0;
            $full = 0;
            $totalCapacity = 0;
            $totalReserved = 0;

            foreach ($facilityAvailability as $facility) {
                $statusName = strtolower((string) ($facility['status']['name'] ?? ''));
                $totalCapacity += (int) ($facility['capacity'] ?? 0);
                $totalReserved += (int) ($facility['reservedToday'] ?? 0);
                if ($statusName === 'available') {
                    $available++;
                } elseif ($statusName === 'limited') {
                    $limited++;
                } elseif ($statusName === 'full') {
                    $full++;
                }
            }

            $totalSlotsLeft = max(0, $totalCapacity - $totalReserved);

            return [
                ['label' => 'Total Facilities', 'value' => $totalFacilities, 'metaLeft' => 'Overview', 'metaRight' => 'Today', 'icon' => 'fa-building'],
                ['label' => 'Available', 'value' => $available, 'metaLeft' => 'Status', 'metaRight' => 'Available', 'icon' => 'fa-check-circle'],
                ['label' => 'Limited', 'value' => $limited, 'metaLeft' => 'Status', 'metaRight' => 'Limited', 'icon' => 'fa-exclamation-triangle'],
                ['label' => 'Full', 'value' => $full, 'metaLeft' => 'Status', 'metaRight' => 'Full', 'icon' => 'fa-ban'],
                ['label' => 'Open Slots', 'value' => $totalSlotsLeft, 'metaLeft' => 'Capacity', 'metaRight' => 'Remaining', 'icon' => 'fa-layer-group']
            ];
        }

        if ($selectedView === 'coordination_status') {
            $statusCounts = [
                'queued' => 0,
                'dispatched' => 0,
                'en route' => 0,
                'arrived' => 0,
                'completed' => 0
            ];

            foreach ($coordinationStatuses as $coordination) {
                $status = strtolower(trim((string) ($coordination['logisticsStatus'] ?? '')));
                if (array_key_exists($status, $statusCounts)) {
                    $statusCounts[$status]++;
                }
            }

            $activeOps = $statusCounts['queued'] + $statusCounts['dispatched'] + $statusCounts['en route'];

            return [
                ['label' => 'Total Coordination', 'value' => count($coordinationStatuses), 'metaLeft' => 'Logistics', 'metaRight' => 'Feed', 'icon' => 'fa-link'],
                ['label' => 'Active Ops', 'value' => $activeOps, 'metaLeft' => 'Status', 'metaRight' => 'In Motion', 'icon' => 'fa-truck-fast'],
                ['label' => 'Arrived', 'value' => $statusCounts['arrived'], 'metaLeft' => 'Status', 'metaRight' => 'Arrived', 'icon' => 'fa-map-pin'],
                ['label' => 'Completed', 'value' => $statusCounts['completed'], 'metaLeft' => 'Status', 'metaRight' => 'Completed', 'icon' => 'fa-flag-checkered'],
                ['label' => 'Queued', 'value' => $statusCounts['queued'], 'metaLeft' => 'Status', 'metaRight' => 'Queued', 'icon' => 'fa-clock']
            ];
        }

        $reservationStats = $this->buildStats($reservationRequests);

        return [
            ['label' => 'Total Reservations Today', 'value' => (int) $reservationStats['totalReservationsToday'], 'metaLeft' => 'Core 2 service scope', 'metaRight' => 'Today', 'icon' => 'fa-calendar-check'],
            ['label' => 'Pending Requests', 'value' => (int) $reservationStats['pendingRequests'], 'metaLeft' => 'Status', 'metaRight' => 'Requested', 'icon' => 'fa-hourglass-half'],
            ['label' => 'Approved', 'value' => (int) $reservationStats['approved'], 'metaLeft' => 'Status', 'metaRight' => 'Approved', 'icon' => 'fa-check-circle'],
            ['label' => 'In Progress', 'value' => (int) $reservationStats['inProgress'], 'metaLeft' => 'Status', 'metaRight' => 'In Progress', 'icon' => 'fa-spinner'],
            ['label' => 'Completed', 'value' => (int) $reservationStats['completed'], 'metaLeft' => 'Status', 'metaRight' => 'Completed', 'icon' => 'fa-flag-checkered']
        ];
    }

    private function extractFacilityTypes(array $reservationRequests, array $facilityAvailability): array
    {
        $types = [];

        foreach ($reservationRequests as $request) {
            $types[] = $request['facilityType'];
        }

        foreach ($facilityAvailability as $facility) {
            $types[] = $facility['type'];
        }

        $types = array_values(array_unique($types));
        sort($types);

        return $types;
    }

    private function generateReservationRequest(int $id): array
    {
        $customers = [
            'Vanessa Radaza', 'Erick Taguba', 'Rens Solano', 'Maria Alvares', 'Joseph De Guzman',
            'Mark Villotes', 'James Cruz', 'Sarah Dela Cruz', 'Michael Santos', 'Jennifer Reyes'
        ];

        $facilityTypes = ['Lounge', 'Assistance', 'VIP', 'Meet and Greet', 'Wheelchair', 'Porter Service'];
        $priorities = ['Low', 'Normal', 'High'];
        $statuses = ['Requested', 'Approved', 'Assigned', 'In Progress', 'Completed'];

        $facilityType = $facilityTypes[array_rand($facilityTypes)];
        $status = $statuses[array_rand($statuses)];

        return [
            'id' => $id,
            'customerName' => $customers[array_rand($customers)],
            'bookingReference' => 'BK-' . date('ym') . '-' . str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            'facilityType' => $facilityType,
            'date' => date('Y-m-d', strtotime('+' . rand(0, 4) . ' days')),
            'priority' => $priorities[array_rand($priorities)],
            'status' => $status
        ];
    }

    private function generateFacilityAvailability(int $id): array
    {
        $types = ['Lounge', 'Assistance', 'VIP', 'Meet and Greet', 'Wheelchair', 'Porter Service'];
        $facilityByType = [
            'Lounge' => ['Business Lounge A', 'Premium Lounge B', 'Family Lounge C'],
            'Assistance' => ['Arrival Assistance Desk', 'Departure Assistance Hub'],
            'VIP' => ['VIP Holding Room 1', 'VIP Reception Suite'],
            'Meet and Greet' => ['Terminal 1 Welcome Counter', 'Terminal 3 Greeter Bay'],
            'Wheelchair' => ['Wheelchair Service Pool A', 'Wheelchair Service Pool B'],
            'Porter Service' => ['Porter Group Alpha', 'Porter Group Bravo']
        ];

        $type = $types[array_rand($types)];
        $capacity = rand(8, 60);
        $reservedToday = rand(0, $capacity);
        $availableSlots = max(0, $capacity - $reservedToday);

        if ($availableSlots === 0) {
            $status = ['name' => 'Full', 'color' => 'red'];
        } elseif ($availableSlots <= (int) round($capacity * 0.25)) {
            $status = ['name' => 'Limited', 'color' => 'yellow'];
        } else {
            $status = ['name' => 'Available', 'color' => 'green'];
        }

        return [
            'id' => $id,
            'facilityName' => $facilityByType[$type][array_rand($facilityByType[$type])],
            'type' => $type,
            'capacity' => $capacity,
            'reservedToday' => $reservedToday,
            'availableSlots' => $availableSlots,
            'status' => $status
        ];
    }

    private function generateCoordinationStatus(int $id): array
    {
        $customers = [
            'Vanessa Radaza', 'Erick Taguba', 'Rens Solano', 'Maria Alvares', 'Joseph De Guzman',
            'Mark Villotes', 'James Cruz', 'Sarah Dela Cruz', 'Michael Santos', 'Jennifer Reyes'
        ];

        $facilityEntries = [
            ['facilityType' => 'Lounge', 'facility' => 'Business Lounge A'],
            ['facilityType' => 'Assistance', 'facility' => 'Arrival Assistance Desk'],
            ['facilityType' => 'VIP', 'facility' => 'VIP Holding Room 1'],
            ['facilityType' => 'Meet and Greet', 'facility' => 'Terminal 3 Greeter Bay'],
            ['facilityType' => 'Wheelchair', 'facility' => 'Wheelchair Service Pool A'],
            ['facilityType' => 'Porter Service', 'facility' => 'Porter Group Alpha']
        ];

        $staffNames = [
            'John Smith', 'Jane Johnson', 'Robert Williams', 'Mary Jones', 'Michael Brown',
            'Sarah Davis', 'David Miller', 'Emily Wilson', 'James Moore', 'Jennifer Taylor'
        ];

        $logisticsStatuses = ['Queued', 'Dispatched', 'En Route', 'Arrived', 'Completed'];
        $selectedFacility = $facilityEntries[array_rand($facilityEntries)];

        return [
            'id' => $id,
            'customerName' => $customers[array_rand($customers)],
            'facilityType' => $selectedFacility['facilityType'],
            'facility' => $selectedFacility['facility'],
            'assignedStaff' => $staffNames[array_rand($staffNames)],
            'logisticsStatus' => $logisticsStatuses[array_rand($logisticsStatuses)],
            'completionTime' => rand(0, 1) ? date('H:i', strtotime('+' . rand(15, 180) . ' minutes')) : 'Pending'
        ];
    }
}

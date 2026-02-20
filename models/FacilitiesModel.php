<?php

class FacilitiesModel
{
    public function getPageData(array $query): array
    {
        $assets = [];
        for ($i = 0; $i < 25; $i++) {
            $assets[] = $this->generateRandomAsset($i);
        }

        $services = [];
        for ($i = 0; $i < 20; $i++) {
            $services[] = $this->generateRandomService($i);
        }

        $filterType = isset($query['type']) ? (string) $query['type'] : 'all';
        $filterStatus = isset($query['status']) ? (string) $query['status'] : 'all';
        $searchTerm = isset($query['search']) ? trim((string) $query['search']) : '';

        if ($filterType !== 'all') {
            $assets = array_values(array_filter($assets, static function (array $asset) use ($filterType): bool {
                return $asset['type'] === $filterType;
            }));
        }

        if ($filterStatus !== 'all') {
            $assets = array_values(array_filter($assets, static function (array $asset) use ($filterStatus): bool {
                return $asset['status']['name'] === $filterStatus;
            }));
        }

        if ($searchTerm !== '') {
            $assets = array_values(array_filter($assets, static function (array $asset) use ($searchTerm): bool {
                return stripos($asset['name'], $searchTerm) !== false || stripos($asset['location'], $searchTerm) !== false;
            }));
            $services = array_values(array_filter($services, static function (array $service) use ($searchTerm): bool {
                return stripos($service['customerName'], $searchTerm) !== false || stripos($service['service'], $searchTerm) !== false;
            }));
        }

        $assetTypes = array_values(array_unique(array_column($assets, 'type')));
        $statusTypes = array_values(array_unique(array_map(static function (array $asset): string {
            return $asset['status']['name'];
        }, $assets)));

        return [
            'assets' => $assets,
            'services' => $services,
            'filterType' => $filterType,
            'filterStatus' => $filterStatus,
            'searchTerm' => $searchTerm,
            'assetTypes' => $assetTypes,
            'statusTypes' => $statusTypes,
            'stats' => $this->buildStats($assets, $services),
            'mapCells' => $this->buildMapCells()
        ];
    }

    private function buildStats(array $assets, array $services): array
    {
        $stats = [
            'totalCapacity' => 0,
            'activeShuttles' => 0,
            'totalShuttles' => 0,
            'loungeOccupancy' => 0,
            'loungeCapacity' => 0,
            'maintenanceCount' => 0,
            'totalAssets' => count($assets),
            'availableAssets' => 0,
            'inUseAssets' => 0,
            'totalServices' => count($services),
            'activeServices' => 0
        ];

        foreach ($assets as $asset) {
            if ($asset['type'] === 'Transport') {
                $stats['totalShuttles']++;
                if ($asset['status']['name'] !== 'Maintenance') {
                    $stats['activeShuttles']++;
                }
                $stats['totalCapacity'] += $asset['capacity'];
            }

            if ($asset['type'] === 'Lounge') {
                $stats['loungeCapacity'] += $asset['capacity'];
                $stats['loungeOccupancy'] += $asset['current'];
            }

            if ($asset['status']['name'] === 'Maintenance') {
                $stats['maintenanceCount']++;
            }

            if ($asset['current'] > 0) {
                $stats['inUseAssets']++;
            } else {
                $stats['availableAssets']++;
            }
        }

        foreach ($services as $service) {
            if (in_array($service['status']['name'], ['Active', 'Ongoing'], true)) {
                $stats['activeServices']++;
            }
        }

        $stats['capacityUsage'] = $stats['totalShuttles'] > 0
            ? round(($stats['activeShuttles'] / $stats['totalShuttles']) * 100)
            : 0;
        $stats['loungeUsage'] = $stats['loungeCapacity'] > 0
            ? round(($stats['loungeOccupancy'] / $stats['loungeCapacity']) * 100)
            : 0;

        return $stats;
    }

    private function buildMapCells(): array
    {
        $cells = [];
        for ($i = 0; $i < 16; $i++) {
            $rand = rand(1, 10);
            if ($rand <= 3) {
                $cells[] = ['type' => 'transport', 'icon' => 'CAR', 'count' => rand(1, 3)];
            } elseif ($rand <= 6) {
                $cells[] = ['type' => 'staff', 'icon' => 'STAFF', 'count' => rand(1, 5)];
            } elseif ($rand <= 8) {
                $cells[] = ['type' => 'lounge', 'icon' => 'LOUNGE', 'count' => rand(1, 2)];
            } else {
                $cells[] = ['type' => 'empty', 'icon' => '', 'count' => 0];
            }
        }

        return $cells;
    }

    private function generateRandomAsset(int $index): array
    {
        $assetTypes = [
            ['type' => 'Transport', 'subtypes' => ['Shuttle Van', 'Shuttle Bus', 'Luxury Van', 'Executive Car']],
            ['type' => 'Lounge', 'subtypes' => ['VIP Lounge', 'Business Lounge', 'Economy Lounge', 'Conference Room']],
            ['type' => 'Equipment', 'subtypes' => ['Luggage Cart', 'Check-in Counter', 'Security Scanner', 'Waiting Chair']],
            ['type' => 'Staff', 'subtypes' => ['Driver', 'Porter', 'Receptionist', 'Security']]
        ];

        $locations = [
            'NAIA Terminal 1', 'NAIA Terminal 2', 'NAIA Terminal 3', 'Clark International Airport',
            'Mactan-Cebu Airport', 'Davao Airport', 'Makati Office', 'BGC Office', 'Quezon City Office',
            'Caloocan City', 'Pasay City', 'Paranaque City', 'Taguig City'
        ];

        $statuses = [
            ['name' => 'Active', 'color' => 'green', 'icon' => 'O'],
            ['name' => 'In Transit', 'color' => 'blue', 'icon' => '<>'],
            ['name' => 'Loading', 'color' => 'yellow', 'icon' => '...'],
            ['name' => 'Maintenance', 'color' => 'red', 'icon' => 'M'],
            ['name' => 'Available', 'color' => 'green', 'icon' => 'OK'],
            ['name' => 'Occupied', 'color' => 'red', 'icon' => 'X'],
            ['name' => 'Reserved', 'color' => 'blue', 'icon' => 'R']
        ];

        $assetType = $assetTypes[array_rand($assetTypes)];
        $assetSubtype = $assetType['subtypes'][array_rand($assetType['subtypes'])];

        if ($assetType['type'] === 'Transport') {
            $capacity = rand(3, 20);
            $current = rand(0, $capacity);
        } elseif ($assetType['type'] === 'Lounge') {
            $capacity = rand(20, 100);
            $current = rand(0, $capacity);
        } else {
            $capacity = 1;
            $current = rand(0, 1);
        }

        $usagePercent = ($current / $capacity) * 100;
        if ($assetType['type'] === 'Transport') {
            if ($usagePercent >= 90) {
                $status = $statuses[5];
            } elseif ($usagePercent >= 50) {
                $status = $statuses[2];
            } elseif ($usagePercent > 0) {
                $status = $statuses[1];
            } elseif (rand(0, 10) === 0) {
                $status = $statuses[3];
            } else {
                $status = $statuses[0];
            }
        } else {
            if ($usagePercent >= 90) {
                $status = $statuses[5];
            } elseif ($usagePercent >= 50) {
                $status = $statuses[6];
            } elseif ($current > 0) {
                $status = $statuses[4];
            } else {
                $status = $statuses[0];
            }
        }

        return [
            'id' => $index + 1,
            'name' => $assetSubtype . ' #' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'type' => $assetType['type'],
            'subtype' => $assetSubtype,
            'capacity' => $capacity,
            'current' => $current,
            'usagePercent' => $usagePercent,
            'location' => $locations[array_rand($locations)],
            'status' => $status,
            'lastMaintenance' => date('Y-m-d', strtotime('-' . rand(0, 180) . ' days')),
            'nextMaintenance' => date('Y-m-d', strtotime('+' . rand(30, 365) . ' days')),
            'assignedStaff' => rand(1, 5),
            'fuelLevel' => $assetType['type'] === 'Transport' ? rand(20, 100) : null,
            'reservations' => rand(0, 10)
        ];
    }

    private function generateRandomService(int $index): array
    {
        $services = ['Baggage Assist', 'Priority Check In', 'Meet & Greet', 'Concierge', 'Security Escort', 'Luggage Storage'];
        $staffFirstNames = ['John', 'Jane', 'Robert', 'Mary', 'Michael', 'Sarah', 'David', 'Emily', 'James', 'Jennifer'];
        $staffLastNames = ['Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor'];
        $customerFirstNames = ['Vanessa', 'Erick', 'Rens', 'Maria', 'Joseph', 'Mark', 'James', 'Sarah', 'Michael', 'Jennifer'];
        $customerLastNames = ['Radaza', 'Taguba', 'Solano', 'Alvares', 'De Guzman', 'Villotes', 'Cruz', 'Dela Cruz', 'Santos', 'Reyes'];

        $serviceStatuses = [
            ['name' => 'Active', 'color' => 'green'],
            ['name' => 'Ongoing', 'color' => 'yellow'],
            ['name' => 'Waiting', 'color' => 'blue'],
            ['name' => 'Completed', 'color' => 'gray']
        ];

        return [
            'id' => $index + 1,
            'customerName' => $customerFirstNames[array_rand($customerFirstNames)] . ' ' . $customerLastNames[array_rand($customerLastNames)],
            'service' => $services[array_rand($services)],
            'staffOnDuty' => $staffFirstNames[array_rand($staffFirstNames)] . ' ' . $staffLastNames[array_rand($staffLastNames)],
            'status' => $serviceStatuses[array_rand($serviceStatuses)],
            'startTime' => date('H:i', strtotime('-' . rand(0, 120) . ' minutes')),
            'duration' => rand(15, 180) . ' min',
            'location' => rand(0, 1) ? 'Terminal ' . rand(1, 3) : 'Ground Floor',
            'priority' => rand(1, 10) > 7 ? 'High' : 'Normal'
        ];
    }
}

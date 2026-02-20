<?php

class DashboardModel
{
    private array $firstNames = [
        'John', 'Jane', 'Robert', 'Maria', 'Michael', 'Sarah', 'David', 'Lisa', 'James', 'Jennifer',
        'Mark', 'Emily', 'William', 'Jessica', 'Richard', 'Karen', 'Charles', 'Susan', 'Joseph', 'Nancy',
        'Thomas', 'Betty', 'Christopher', 'Margaret', 'Daniel', 'Sandra', 'Matthew', 'Ashley', 'Anthony', 'Kimberly',
        'Donald', 'Emily', 'Steven', 'Donna', 'Paul', 'Michelle', 'Andrew', 'Carol', 'Joshua', 'Amanda',
        'Kenneth', 'Dorothy', 'Kevin', 'Melissa', 'Brian', 'Deborah', 'George', 'Stephanie', 'Timothy', 'Rebecca'
    ];

    private array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
        'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'
    ];

    private array $actionTypes = ['Passport', 'Document', 'Payment', 'Visa', 'Medical'];

    private array $actionStatuses = [
        ['status' => 'Missing', 'color' => 'yellow'],
        ['status' => 'Unpaid', 'color' => 'red'],
        ['status' => 'Expired', 'color' => 'red'],
        ['status' => 'Pending Review', 'color' => 'yellow'],
        ['status' => 'Incomplete', 'color' => 'yellow']
    ];

    private array $staffNames = ['J. Rizz', 'M. Manuel', 'J. Paul', 'A. Rodriguez', 'S. Johnson', 'P. Martinez'];
    private array $operationTypes = ['Transport', 'Accommodation', 'Tour Guide', 'Logistics'];
    private array $departureTimes = ['08:30 am', '11:30 am', '02:45 pm', '05:15 pm', '09:00 pm'];

    private function generateRandomName(): string
    {
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];

        return $firstName . ' ' . $lastName;
    }

    public function getFirstNames(): array
    {
        return $this->firstNames;
    }

    public function getLastNames(): array
    {
        return $this->lastNames;
    }

    public function getUrgentActions(int $count = 5): array
    {
        $urgentActions = [];

        for ($i = 0; $i < $count; $i++) {
            $urgentActions[] = [
                'name' => $this->generateRandomName(),
                'type' => $this->actionTypes[array_rand($this->actionTypes)],
                'status' => $this->actionStatuses[array_rand($this->actionStatuses)],
                'deadline' => date('M. d, Y', strtotime('+' . rand(1, 10) . ' days'))
            ];
        }

        return $urgentActions;
    }

    public function getUpcomingDepartures(int $count = 2): array
    {
        $upcomingDepartures = [];

        for ($i = 0; $i < $count; $i++) {
            $upcomingDepartures[] = [
                'name' => $this->generateRandomName(),
                'departure' => date('M d, Y', strtotime('+' . rand(0, 2) . ' days')) . ' - ' . $this->departureTimes[array_rand($this->departureTimes)]
            ];
        }

        return $upcomingDepartures;
    }

    public function getOperations(int $count = 3): array
    {
        $operations = [];

        for ($i = 0; $i < $count; $i++) {
            $operations[] = [
                'name' => $this->generateRandomName(),
                'staff' => $this->staffNames[array_rand($this->staffNames)],
                'type' => $this->operationTypes[array_rand($this->operationTypes)]
            ];
        }

        return $operations;
    }

    public function getDocumentStatus(): array
    {
        $urgentDocuments = rand(25, 40);
        $approvedDocuments = rand(60, 80);

        return [
            'urgent' => $urgentDocuments,
            'approved' => $approvedDocuments,
            'ratio' => $urgentDocuments / ($urgentDocuments + $approvedDocuments)
        ];
    }

    public function getBookingData(): array
    {
        $totalSlots = 122;
        $usedSlots = rand(100, 120);

        return [
            'total_slots' => $totalSlots,
            'used_slots' => $usedSlots,
            'percentage' => round(($usedSlots / $totalSlots) * 100),
            'available' => $totalSlots - $usedSlots
        ];
    }

    public function getOperationalHealth(): int
    {
        return rand(70, 90);
    }

    public function getCustomerRating(): float
    {
        return round((rand(43, 49) / 10), 1);
    }

    public function getRatingBars(int $count = 12): array
    {
        $ratingBars = [];

        for ($i = 0; $i < $count; $i++) {
            $ratingBars[] = rand(30, 80);
        }

        return $ratingBars;
    }

    public function getCrmTrends(): array
    {
        return [
            ['label' => 'VIP', 'percentage' => rand(20, 30)],
            ['label' => 'Gold', 'percentage' => rand(25, 35)],
            ['label' => 'Silver', 'percentage' => rand(15, 25)],
            ['label' => 'New Lead', 'percentage' => rand(40, 50)]
        ];
    }

    public function getCrmTierIcons(): array
    {
        return [
            'VIP' => '&#9733;',
            'Gold' => '&#9670;',
            'Silver' => '&#9679;',
            'New Lead' => '&#10148;'
        ];
    }
}

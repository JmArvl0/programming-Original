<?php
/**
 * Dashboard Model
 * Handles data generation and business logic for dashboard
 */
class DashboardModel {
    
    private $firstNames = [
        'John', 'Jane', 'Robert', 'Maria', 'Michael', 'Sarah', 'David', 'Lisa', 'James', 'Jennifer',
        'Mark', 'Emily', 'William', 'Jessica', 'Richard', 'Karen', 'Charles', 'Susan', 'Joseph', 'Nancy',
        'Thomas', 'Betty', 'Christopher', 'Margaret', 'Daniel', 'Sandra', 'Matthew', 'Ashley', 'Anthony', 'Kimberly',
        'Donald', 'Emily', 'Steven', 'Donna', 'Paul', 'Michelle', 'Andrew', 'Carol', 'Joshua', 'Amanda',
        'Kenneth', 'Dorothy', 'Kevin', 'Melissa', 'Brian', 'Deborah', 'George', 'Stephanie', 'Timothy', 'Rebecca'
    ];
    
    private $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
        'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'
    ];
    
    private $actionTypes = ['Passport', 'Document', 'Payment', 'Visa', 'Medical'];
    
    private $actionStatuses = [
        ['status' => 'Missing', 'color' => 'yellow', 'icon' => '⚠️'],
        ['status' => 'Unpaid', 'color' => 'red', 'icon' => '💳'],
        ['status' => 'Expired', 'color' => 'red', 'icon' => '📅'],
        ['status' => 'Pending Review', 'color' => 'yellow', 'icon' => '📋'],
        ['status' => 'Incomplete', 'color' => 'yellow', 'icon' => '📝']
    ];
    
    private $staffNames = ['J. Rizz', 'M. Manuel', 'J. Paul', 'A. Rodriguez', 'S. Johnson', 'P. Martinez'];
    private $operationTypes = ['Transport', 'Accommodation', 'Tour Guide', 'Logistics'];
    private $departureTimes = ['08:30 am', '11:30 am', '02:45 pm', '05:15 pm', '09:00 pm'];
    
    /**
     * Generate a random full name
     * @return string
     */
    private function generateRandomName() {
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        return $firstName . ' ' . $lastName;
    }
    
    /**
     * Get all first names
     * @return array
     */
    public function getFirstNames() {
        return $this->firstNames;
    }
    
    /**
     * Get all last names
     * @return array
     */
    public function getLastNames() {
        return $this->lastNames;
    }
    
    /**
     * Generate urgent actions data
     * @param int $count Number of actions to generate
     * @return array
     */
    public function getUrgentActions($count = 5) {
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
    
    /**
     * Generate upcoming departures data
     * @param int $count Number of departures to generate
     * @return array
     */
    public function getUpcomingDepartures($count = 2) {
        $upcomingDepartures = [];
        
        for ($i = 0; $i < $count; $i++) {
            $upcomingDepartures[] = [
                'name' => $this->generateRandomName(),
                'departure' => date('M d, Y', strtotime('+' . rand(0, 2) . ' days')) . ' - ' . $this->departureTimes[array_rand($this->departureTimes)]
            ];
        }
        
        return $upcomingDepartures;
    }
    
    /**
     * Generate operations data
     * @param int $count Number of operations to generate
     * @return array
     */
    public function getOperations($count = 3) {
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
    
    /**
     * Get document status data
     * @return array
     */
    public function getDocumentStatus() {
        $urgentDocuments = rand(25, 40);
        $approvedDocuments = rand(60, 80);
        
        return [
            'urgent' => $urgentDocuments,
            'approved' => $approvedDocuments,
            'ratio' => $urgentDocuments / ($urgentDocuments + $approvedDocuments)
        ];
    }
    
    /**
     * Get booking data
     * @return array
     */
    public function getBookingData() {
        $totalSlots = 122;
        $usedSlots = rand(100, 120);
        
        return [
            'total_slots' => $totalSlots,
            'used_slots' => $usedSlots,
            'percentage' => round(($usedSlots / $totalSlots) * 100),
            'available' => $totalSlots - $usedSlots
        ];
    }
    
    /**
     * Get operational health
     * @return int
     */
    public function getOperationalHealth() {
        return rand(70, 90);
    }
    
    /**
     * Get customer rating
     * @return float
     */
    public function getCustomerRating() {
        return round((rand(43, 49) / 10), 1);
    }
    
    /**
     * Get rating bars data
     * @param int $count Number of bars
     * @return array
     */
    public function getRatingBars($count = 12) {
        $ratingBars = [];
        
        for ($i = 0; $i < $count; $i++) {
            $ratingBars[] = rand(30, 80);
        }
        
        return $ratingBars;
    }
    
    /**
     * Get CRM trends data
     * @return array
     */
    public function getCrmTrends() {
        return [
            ['label' => 'VIP', 'percentage' => rand(20, 30)],
            ['label' => 'Gold', 'percentage' => rand(25, 35)],
            ['label' => 'Silver', 'percentage' => rand(15, 25)],
            ['label' => 'New Lead', 'percentage' => rand(40, 50)]
        ];
    }
    
    /**
     * Get CRM tier icons
     * @return array
     */
    public function getCrmTierIcons() {
        return [
            'VIP' => '&#9733;',
            'Gold' => '&#9670;',
            'Silver' => '&#9679;',
            'New Lead' => '&#10148;'
        ];
    }
}
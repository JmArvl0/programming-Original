<?php
// API endpoint to get customer data
header('Content-Type: application/json');

// Sample data - Replace with actual database queries
$customers = [
    [
        'id' => 1,
        'name' => 'Manuel Cruz',
        'destination' => 'Japan',
        'last_contacted' => '9/10/2025 - 11:30am',
        'payment_status' => 'Unpaid',
        'status' => 40
    ],
    [
        'id' => 2,
        'name' => 'Jose Pedro',
        'destination' => 'Negros Ocidental',
        'last_contacted' => '9/14/2025 - 11:30am',
        'payment_status' => 'N/A',
        'status' => 0
    ],
    // Add more customers as needed
];

echo json_encode($customers);
?>


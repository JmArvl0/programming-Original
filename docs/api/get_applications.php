<?php
// API endpoint to get passport/visa applications
header('Content-Type: application/json');

// Sample data - Replace with actual database queries
$applications = [
    [
        'id' => 1,
        'name' => 'Manuel Cruz',
        'passport_no' => 'DAS0293P',
        'country' => 'Philippines',
        'documents' => 'Missing',
        'application_status' => 'Not Started'
    ],
    [
        'id' => 2,
        'name' => 'Jose Pedro',
        'passport_no' => '09243JD',
        'country' => 'USA',
        'documents' => 'Not Started',
        'application_status' => 'Not Started'
    ],
    // Add more applications as needed
];

echo json_encode($applications);
?>


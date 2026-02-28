<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $customerId = $_GET['id'] ?? 0;
    
    if (!$customerId) {
        throw new Exception('Customer ID is required');
    }
    
    // Get customer details
    $sql = "SELECT 
                c.*,
                CASE 
                    WHEN c.payment_status = 'paid' THEN 'bg-success'
                    WHEN c.payment_status = 'partially paid' THEN 'bg-warning'
                    WHEN c.payment_status = 'overdue' THEN 'bg-danger'
                    ELSE 'bg-secondary'
                END as payment_badge_class,
                CASE 
                    WHEN c.status = 'finished' THEN 'bg-success'
                    WHEN c.status = 'processing' THEN 'bg-primary'
                    WHEN c.status = 'pending' THEN 'bg-warning'
                    WHEN c.status = 'cancelled' THEN 'bg-danger'
                    ELSE 'bg-info'
                END as status_badge_class
            FROM customers c
            WHERE c.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        throw new Exception('Customer not found');
    }
    
    echo json_encode([
        'success' => true,
        'customer' => $customer
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
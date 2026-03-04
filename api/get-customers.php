<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $customerId = (int) ($_GET['id'] ?? 0);
    
    if (!$customerId) {
        throw new Exception('Customer ID is required');
    }
    
    $conn = getDBConnection();
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }
    
    // Get customer details  
    $sql = "SELECT 
                c.id,
                c.full_name,
                c.email,
                c.phone,
                c.tier,
                c.created_at,
                c.updated_at,
                COALESCE(b.payment_status, 'unpaid') as payment_status,
                COALESCE(b.booking_status, 'pending') as status
            FROM customers c
            LEFT JOIN (
                SELECT customer_id, payment_status, booking_status
                FROM bookings
                ORDER BY updated_at DESC, id DESC
                LIMIT 1
            ) b ON b.customer_id = c.id
            WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $customerId);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();
    closeDBConnection($conn);
    
    if (!$customer) {
        throw new Exception('Customer not found');
    }
    
    // Derive badge classes
    $paymentStatus = strtolower($customer['payment_status'] ?? 'unpaid');
    $status = strtolower($customer['status'] ?? 'pending');
    
    $paymentBadgeClass = match($paymentStatus) {
        'paid' => 'bg-success',
        'partially paid' => 'bg-warning',
        'overdue' => 'bg-danger',
        default => 'bg-secondary'
    };
    
    $statusBadgeClass = match($status) {
        'finished' => 'bg-success',
        'processing' => 'bg-primary',
        'pending' => 'bg-warning',
        'cancelled' => 'bg-danger',
        default => 'bg-info'
    };
    
    $customer['payment_badge_class'] = $paymentBadgeClass;
    $customer['status_badge_class'] = $statusBadgeClass;
    
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
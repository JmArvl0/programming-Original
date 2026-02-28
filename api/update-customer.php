<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('Customer ID is required');
    }
    
    // Prepare update query
    $sql = "UPDATE customers SET 
                full_name = :full_name,
                email = :email,
                phone = :phone,
                destination = :destination,
                status = :status,
                payment_status = :payment_status,
                admission_status = :admission_status,
                progress = :progress,
                tier = :tier,
                refund_flag = :refund_flag,
                last_contacted_at = :last_contacted_at
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    
    // Handle refund flag
    $refundFlag = isset($_POST['refund_flag']) ? 1 : 0;
    
    // Handle last contacted date
    $lastContacted = !empty($_POST['last_contacted_at']) 
        ? $_POST['last_contacted_at'] 
        : null;
    
    // Execute update
    $result = $stmt->execute([
        ':id' => $id,
        ':full_name' => $_POST['full_name'] ?? '',
        ':email' => $_POST['email'] ?? '',
        ':phone' => $_POST['phone'] ?? '',
        ':destination' => $_POST['destination'] ?? '',
        ':status' => $_POST['status'] ?? 'pending',
        ':payment_status' => $_POST['payment_status'] ?? 'unpaid',
        ':admission_status' => $_POST['admission_status'] ?? 'pending',
        ':progress' => $_POST['progress'] ?? 0,
        ':tier' => $_POST['tier'] ?? 'new',
        ':refund_flag' => $refundFlag,
        ':last_contacted_at' => $lastContacted
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Customer updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update customer');
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
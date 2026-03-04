<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $customerId = (int) ($_POST['id'] ?? 0);
    
    if (!$customerId) {
        throw new Exception('Customer ID is required');
    }
    
    $conn = getDBConnection();
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection error');
    }
    
    // Update customer info (only valid customer columns)
    $sql = "UPDATE customers SET 
                full_name = ?,
                email = ?,
                phone = ?,
                tier = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Query prepare failed: ' . $conn->error);
    }
    
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $tier = $_POST['tier'] ?? 'new';
    
    $stmt->bind_param('ssssi', $fullName, $email, $phone, $tier, $customerId);
    $result = $stmt->execute();
    $stmt->close();
    
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
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $customerId = $input['id'] ?? 0;
    
    if (!$customerId) {
        throw new Exception('Customer ID is required');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete related records
    $tables = [
        'facility_coordination_status' => "DELETE FROM facility_coordination_status WHERE facility_reservation_id IN (SELECT id FROM facility_reservations WHERE customer_id = ?)",
        'facility_reservations' => "DELETE FROM facility_reservations WHERE customer_id = ?",
        'payment_reminders' => "DELETE FROM payment_reminders WHERE payment_id IN (SELECT id FROM payments WHERE customer_id = ?)",
        'payments' => "DELETE FROM payments WHERE customer_id = ?",
        'passport_documents' => "DELETE FROM passport_documents WHERE passport_application_id IN (SELECT id FROM passport_applications WHERE customer_id = ?)",
        'passport_applications' => "DELETE FROM passport_applications WHERE customer_id = ?",
        'bookings' => "DELETE FROM bookings WHERE guest_id IN (SELECT id FROM guests WHERE customer_id = ?)",
        'guests' => "DELETE FROM guests WHERE customer_id = ?",
        'crm_interactions' => "DELETE FROM crm_interactions WHERE customer_id = ?",
        'customers' => "DELETE FROM customers WHERE id = ?"
    ];
    
    foreach($tables as $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customerId]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Customer deleted successfully'
    ]);
    
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
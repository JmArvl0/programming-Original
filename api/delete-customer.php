<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $customerId = (int) ($input['id'] ?? 0);
    
    if (!$customerId) {
        throw new Exception('Customer ID is required');
    }
    
    $conn = getDBConnection();
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Delete related records - use booking_id relationships
    $queries = [
        "DELETE FROM facility_coordination_status WHERE facility_reservation_id IN (SELECT fr.id FROM facility_reservations fr INNER JOIN bookings b ON fr.booking_id = b.id WHERE b.customer_id = ?)",
        "DELETE FROM facility_reservations WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id = ?)",
        "DELETE FROM payment_reminders WHERE payment_id IN (SELECT p.id FROM payments p INNER JOIN bookings b ON p.booking_id = b.id WHERE b.customer_id = ?)",
        "DELETE FROM payments WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id = ?)",
        "DELETE FROM passport_documents WHERE passport_application_id IN (SELECT pa.id FROM passport_applications pa INNER JOIN bookings b ON pa.booking_id = b.id WHERE b.customer_id = ?)",
        "DELETE FROM passport_applications WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id = ?)",
        "DELETE FROM bookings WHERE customer_id = ?",
        "DELETE FROM crm_interactions WHERE customer_id = ?",
        "DELETE FROM customers WHERE id = ?"
    ];
    
    foreach($queries as $sql) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('i', $customerId);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        $stmt->close();
    }
    
    $conn->commit();
    closeDBConnection($conn);
    
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
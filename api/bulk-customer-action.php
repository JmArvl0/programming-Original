<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $ids = $input['ids'] ?? [];
    
    if (empty($action) || empty($ids)) {
        throw new Exception('Action and customer IDs are required');
    }
    
    $conn = getDBConnection();
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }
    
    switch($action) {
        case 'delete':
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Delete related records for multiple customers - use booking_id relationships
                $deleteQueries = [
                    "DELETE FROM facility_coordination_status WHERE facility_reservation_id IN (SELECT fr.id FROM facility_reservations fr INNER JOIN bookings b ON fr.booking_id = b.id WHERE b.customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM facility_reservations WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM payment_reminders WHERE payment_id IN (SELECT p.id FROM payments p INNER JOIN bookings b ON p.booking_id = b.id WHERE b.customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM payments WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM passport_documents WHERE passport_application_id IN (SELECT pa.id FROM passport_applications pa INNER JOIN bookings b ON pa.booking_id = b.id WHERE b.customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM passport_applications WHERE booking_id IN (SELECT id FROM bookings WHERE customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . "))",
                    "DELETE FROM bookings WHERE customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")",
                    "DELETE FROM crm_interactions WHERE customer_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")",
                    "DELETE FROM customers WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")"
                ];
                
                foreach($deleteQueries as $sql) {
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Prepare failed: ' . $conn->error);
                    }
                    $types = str_repeat('i', count($ids));
                    $stmt->bind_param($types, ...$ids);
                    if (!$stmt->execute()) {
                        throw new Exception('Execute failed: ' . $stmt->error);
                    }
                    $stmt->close();
                }
                
                $conn->commit();
                $message = count($ids) . ' customer(s) deleted successfully';
            } catch(Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'send-reminder':
            // Insert payment reminders
            $sql = "INSERT INTO payment_reminders (payment_id, reminder_type, reminder_status, sent_at)
                    SELECT p.id, 'email', 'sent', NOW()
                    FROM payments p
                    WHERE p.customer_id IN ($placeholders)
                    AND p.status IN ('pending', 'overdue', 'partial')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);
            $message = 'Reminders sent to ' . count($ids) . ' customer(s)';
            break;
            
        case 'mark-followup':
            // Mark for follow-up
            $sql = "UPDATE customers SET status = 'pending' WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);
            $message = count($ids) . ' customer(s) marked for follow-up';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
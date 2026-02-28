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
    
    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    switch($action) {
        case 'delete':
            // Start transaction
            $pdo->beginTransaction();
            
            try {
                // Delete related records for multiple customers
                $tables = [
                    'facility_coordination_status' => "DELETE FROM facility_coordination_status WHERE facility_reservation_id IN (SELECT id FROM facility_reservations WHERE customer_id IN ($placeholders))",
                    'facility_reservations' => "DELETE FROM facility_reservations WHERE customer_id IN ($placeholders)",
                    'payment_reminders' => "DELETE FROM payment_reminders WHERE payment_id IN (SELECT id FROM payments WHERE customer_id IN ($placeholders))",
                    'payments' => "DELETE FROM payments WHERE customer_id IN ($placeholders)",
                    'passport_documents' => "DELETE FROM passport_documents WHERE passport_application_id IN (SELECT id FROM passport_applications WHERE customer_id IN ($placeholders))",
                    'passport_applications' => "DELETE FROM passport_applications WHERE customer_id IN ($placeholders)",
                    'bookings' => "DELETE FROM bookings WHERE guest_id IN (SELECT id FROM guests WHERE customer_id IN ($placeholders))",
                    'guests' => "DELETE FROM guests WHERE customer_id IN ($placeholders)",
                    'crm_interactions' => "DELETE FROM crm_interactions WHERE customer_id IN ($placeholders)",
                    'customers' => "DELETE FROM customers WHERE id IN ($placeholders)"
                ];
                
                foreach($tables as $sql) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                }
                
                $pdo->commit();
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
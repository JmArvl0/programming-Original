<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    if (!($conn instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }
    
    // Get paid bookings count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE payment_status = 'paid'");
    if ($stmt) {
        $stmt->execute();
        $paidCount = (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
    } else {
        $paidCount = 0;
    }
    
    // Get finished bookings count (replaces admitted)
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE booking_status = 'finished'");
    if ($stmt) {
        $stmt->execute();
        $admittedCount = (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
    } else {
        $admittedCount = 0;
    }
    
    // Get pending bookings count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE booking_status = 'pending'");
    if ($stmt) {
        $stmt->execute();
        $pendingAdmissionCount = (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
    } else {
        $pendingAdmissionCount = 0;
    }
    
    // Get unpaid bookings count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE payment_status IN ('unpaid', 'overdue', 'partially paid')");
    if ($stmt) {
        $stmt->execute();
        $unpaidCount = (int) ($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
    } else {
        $unpaidCount = 0;
    }
    
    closeDBConnection($conn);
    
    echo json_encode([
        'success' => true,
        'paidCount' => (int)$paidCount,
        'admittedCount' => (int)$admittedCount,
        'pendingAdmissionCount' => (int)$pendingAdmissionCount,
        'unpaidCount' => (int)$unpaidCount
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
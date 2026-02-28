<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get paid customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status = 'paid'");
    $paidCount = $stmt->fetchColumn();
    
    // Get admitted customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'admitted'");
    $admittedCount = $stmt->fetchColumn();
    
    // Get pending admission count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'pending'");
    $pendingAdmissionCount = $stmt->fetchColumn();
    
    // Get unpaid customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status IN ('unpaid', 'overdue', 'partially paid')");
    $unpaidCount = $stmt->fetchColumn();
    
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
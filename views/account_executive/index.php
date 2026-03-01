<?php
// Delegating wrapper: prepare minimal variables and render component-based view
session_start();
require_once __DIR__ . '/../../config/database.php';

$selectedStatus = $_GET['status'] ?? 'all';
$selectedPayment = $_GET['payment'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;

// Build URL helper used by components
function buildUrl(array $params = []): string {
    $current = $_GET;
    $merged = array_merge($current, $params);
    return '?' . http_build_query($merged);
}

// Stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status = 'paid'");
    $paid = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'admitted'");
    $admitted = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'pending'");
    $pending = (int) $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status IN ('unpaid','overdue','partially paid')");
    $unpaid = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $paid = $admitted = $pending = $unpaid = 0;
}

$stats = [
    'paid' => $paid,
    'admitted' => $admitted,
    'pending' => $pending,
    'unpaid' => $unpaid,
];

// Pagination / customers
$offset = ($page - 1) * $perPage;
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM customers");
    $totalItems = (int) $countStmt->fetchColumn();

    $sql = "SELECT c.*, DATE_FORMAT(c.last_contacted_at, '%m/%d/%Y - %h:%i %p') AS last_contacted_formatted FROM customers c ORDER BY c.id ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
    $totalItems = 0;
}

// Map rows to the shape expected by components
$customers = [];
foreach ($rows as $r) {
    $payment = $r['payment_status'] ?? 'unknown';
    $status = $r['status'] ?? 'unknown';
    $paymentBadge = $payment === 'paid' ? 'bg-success' : ($payment === 'partially paid' ? 'bg-warning' : ($payment === 'overdue' ? 'bg-danger' : 'bg-secondary'));
    $statusBadge = $status === 'finished' ? 'bg-success' : ($status === 'processing' ? 'bg-primary' : ($status === 'pending' ? 'bg-warning' : ($status === 'cancelled' ? 'bg-danger' : 'bg-info')));

    $customers[] = [
        'id' => (int) $r['id'],
        'name' => $r['full_name'] ?? $r['name'] ?? '',
        'email' => $r['email'] ?? '',
        'destination' => $r['destination'] ?? '',
        'lastContacted' => $r['last_contacted_formatted'] ?? 'Never',
        'paymentStatus' => $payment,
        'paymentBadgeClass' => $paymentBadge,
        'paymentStatusNormalized' => strtolower($payment),
        'status' => $status,
        'statusBadgeClass' => $statusBadge,
        'statusNormalized' => strtolower($status),
        'progressWidth' => isset($r['progress']) ? (int)$r['progress'] : 0,
        'refund' => $r['refund_flag'] ?? 0,
        'createdDate' => $r['created_at'] ?? null,
        'lastContactedDate' => $r['last_contacted_at'] ?? null,
    ];
}

$totalPages = $perPage > 0 ? (int) ceil($totalItems / $perPage) : 1;
$pagination = [
    'page' => $page,
    'perPage' => $perPage,
    'totalItems' => $totalItems,
    'totalPages' => $totalPages,
    'offset' => $offset,
    'start' => $totalItems === 0 ? 0 : $offset + 1,
    'end' => $totalItems === 0 ? 0 : min($offset + $perPage, $totalItems),
];

// Variables used by component views
$selectedTab = $selectedStatus; // keep compatibility: components expect selectedTab
$selectedPayment = $selectedPayment;
$selectedStatus = $selectedStatus;
$searchTerm = $search;
$perPage = $perPage;

// Ensure the page loads the customer-buttons script via the layout
$scripts = ['js/customer-buttons.js'];

// Render using layout and the component view
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/customer_list.view.php';
require __DIR__ . '/../layouts/footer.php';



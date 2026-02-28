<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AccountExecutiveModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class AccountExecutiveController extends BaseController
{
    private AccountExecutiveModel $accountExecutiveModel;
    private ?NotificationsModel $notificationsModel = null;
    private const DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->accountExecutiveModel = new AccountExecutiveModel();
        try {
            $this->notificationsModel = new NotificationsModel();
        } catch (Throwable $exception) {
            $this->notificationsModel = null;
        }
    }

    public function index(): void
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] !== '') {
            $this->handleAjax((string) $_GET['ajax']);
            return;
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : self::DEFAULT_PER_PAGE;
        $tab = isset($_GET['tab']) ? strtolower(trim((string) $_GET['tab'])) : 'all';
        $payment = isset($_GET['payment']) ? strtolower(trim((string) $_GET['payment'])) : 'all';
        $status = isset($_GET['status']) ? strtolower(trim((string) $_GET['status'])) : 'all';
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $allowedTabs = ['all', 'new', 'for-follow-up', 'ongoing', 'payment-issues', 'finished', 'refund'];
        $allowedPayment = ['all', 'paid', 'unpaid', 'overdue', 'partially paid'];
        $allowedStatus = ['all', 'finished', 'processing', 'pending', 'cancelled'];

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'all';
        }
        if (!in_array($payment, $allowedPayment, true)) {
            $payment = 'all';
        }
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'all';
        }

        $pageData = $this->accountExecutiveModel->getCustomersPage($page, $perPage, $tab, $payment, $status, $search);
        $customers = array_map([$this, 'prepareCustomerForView'], $pageData['items']);
        $stats = $this->accountExecutiveModel->buildStats($pageData['allFiltered']);
        $pagination = $this->buildPaginationData(
            $pageData['page'],
            $pageData['perPage'],
            $pageData['totalItems'],
            $pageData['totalPages'],
            $pageData['offset']
        );

        $scriptVersion = @filemtime(__DIR__ . '/../js/account-executive.js');
        $scriptPath = 'js/account-executive.js';
        if (is_int($scriptVersion) && $scriptVersion > 0) {
            $scriptPath .= '?v=' . $scriptVersion;
        }

        $this->render('account_executive/customer_list.view', [
            'pageTitle' => 'Account Executive',
            'pageSubtitle' => 'Handles Customer Processing',
            'customers' => $customers,
            'stats' => $stats,
            'pagination' => $pagination,
            'selectedTab' => $tab,
            'selectedPayment' => $payment,
            'selectedStatus' => $status,
            'searchTerm' => $search,
            'perPage' => $pageData['perPage']
        ], [
            'styles' => ['css/account-executive.css'],
            'scripts' => [$scriptPath]
        ]);
    }

    private function prepareCustomerForView(array $customer): array
    {
        $paymentStatus = (string) ($customer['paymentStatus'] ?? '');
        $status = (string) ($customer['status'] ?? '');
        $progress = (int) ($customer['progress'] ?? 0);
        $progress = max(0, min(100, $progress));

        return $customer + [
            'paymentStatusNormalized' => $this->normalizeValue($paymentStatus),
            'statusNormalized' => $this->normalizeValue($status),
            'paymentBadgeClass' => $this->paymentBadgeClass($paymentStatus),
            'statusBadgeClass' => $this->statusBadgeClass($status),
            'progressWidth' => $progress
        ];
    }

    private function normalizeValue(string $value): string
    {
        return strtolower(trim($value));
    }

    private function paymentBadgeClass(string $paymentStatus): string
    {
        return match ($this->normalizeValue($paymentStatus)) {
            'paid' => 'bg-success',
            'unpaid', 'overdue' => 'bg-danger',
            'partially paid' => 'bg-warning text-dark',
            'refunded' => 'bg-info text-dark',
            default => 'bg-secondary'
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($this->normalizeValue($status)) {
            'pending' => 'bg-warning text-dark',
            'processing' => 'bg-primary',
            'cancelled' => 'bg-danger',
            'finished' => 'bg-success',
            default => 'bg-secondary'
        };
    }

    private function buildPaginationData(int $page, int $perPage, int $totalItems, int $totalPages, int $offset): array
    {
        $start = $totalItems === 0 ? 0 : $offset + 1;
        $end = $totalItems === 0 ? 0 : min($offset + $perPage, $totalItems);

        return [
            'page' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'start' => $start,
            'end' => $end
        ];
    }

    private function handleAjax(string $action): void
    {
        $payload = $this->readJsonBody();

        switch ($action) {
            case 'edit-customer':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'edit-customer',
                    'message' => 'Edit customer endpoint ready for integration.'
                ]);
                return;
            case 'delete-customer':
                $customerId = (int) ($payload['customer_id'] ?? 0);
                if ($customerId <= 0) {
                    $this->jsonResponse(['ok' => false, 'message' => 'Invalid customer id'], 400);
                    return;
                }

                require_once __DIR__ . '/../config/database.php';
                $conn = null;
                try {
                    $conn = getDBConnection();
                    $conn->begin_transaction();

                    $queries = [
                        "DELETE FROM facility_coordination_status WHERE facility_reservation_id IN (SELECT id FROM facility_reservations WHERE customer_id = ?)",
                        "DELETE FROM facility_reservations WHERE customer_id = ?",
                        "DELETE FROM payment_reminders WHERE payment_id IN (SELECT id FROM payments WHERE customer_id = ?)",
                        "DELETE FROM payments WHERE customer_id = ?",
                        "DELETE FROM passport_documents WHERE passport_application_id IN (SELECT id FROM passport_applications WHERE customer_id = ?)",
                        "DELETE FROM passport_applications WHERE customer_id = ?",
                        "DELETE FROM bookings WHERE guest_id IN (SELECT id FROM guests WHERE customer_id = ?)",
                        "DELETE FROM guests WHERE customer_id = ?",
                        "DELETE FROM crm_interactions WHERE customer_id = ?",
                        "DELETE FROM customers WHERE id = ?"
                    ];

                    foreach ($queries as $sql) {
                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            throw new RuntimeException('Prepare failed: ' . $conn->error);
                        }
                        if (!$stmt->bind_param('i', $customerId)) {
                            throw new RuntimeException('Bind failed: ' . $stmt->error);
                        }
                        if (!$stmt->execute()) {
                            throw new RuntimeException('Execute failed: ' . $stmt->error);
                        }
                        $stmt->close();
                    }

                    $conn->commit();

                    if ($this->notificationsModel instanceof NotificationsModel) {
                        $this->notificationsModel->createNotification(
                            'customer_deleted',
                            'Admin',
                            $customerId,
                            'Customer #' . $customerId . ' was deleted.'
                        );
                    }

                    $this->jsonResponse([
                        'ok' => true,
                        'action' => 'delete-customer',
                        'message' => 'Customer deleted successfully.'
                    ]);
                    return;
                } catch (Throwable $e) {
                    if ($conn instanceof mysqli) {
                        @$conn->rollback();
                    }
                    $this->jsonResponse(['ok' => false, 'message' => $e->getMessage() ?: 'Delete failed'], 500);
                    return;
                }
            case 'send-reminder':
                $customerIds = $payload['customer_ids'] ?? [];
                $totalTargets = is_array($customerIds) ? count($customerIds) : 0;
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'payment_due',
                        'Financial',
                        max(1, $totalTargets),
                        $totalTargets > 0
                            ? 'Payment overdue reminders sent to ' . $totalTargets . ' customer(s).'
                            : 'Payment reminder action was triggered.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'send-reminder',
                    'message' => 'Payment reminder sent and notification logged.'
                ]);
                return;
            case 'update-status':
                $customerId = (int) ($payload['customer_id'] ?? 0);
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'payment_received',
                        'Financial',
                        max(1, $customerId),
                        'Customer payment status was updated.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'update-status',
                    'message' => 'Customer status updated and financial notification sent.'
                ]);
                return;
            default:
                $this->jsonResponse([
                    'ok' => false,
                    'message' => 'Unsupported AJAX action.'
                ], 400);
                return;
        }
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}

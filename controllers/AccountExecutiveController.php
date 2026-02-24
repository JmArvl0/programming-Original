<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AccountExecutiveModel.php';
require_once __DIR__ . '/../includes/auth.php';

class AccountExecutiveController extends BaseController
{
    private AccountExecutiveModel $accountExecutiveModel;
    private const DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->accountExecutiveModel = new AccountExecutiveModel();
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
            'scripts' => ['js/account-executive.js']
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
        switch ($action) {
            case 'edit-customer':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'edit-customer',
                    'message' => 'Edit customer endpoint ready for integration.'
                ]);
                return;
            case 'delete-customer':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'delete-customer',
                    'message' => 'Delete customer endpoint ready for integration.'
                ]);
                return;
            case 'send-reminder':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'send-reminder',
                    'message' => 'Reminder endpoint ready for integration.'
                ]);
                return;
            case 'update-status':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'update-status',
                    'message' => 'Status update endpoint ready for integration.'
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
}

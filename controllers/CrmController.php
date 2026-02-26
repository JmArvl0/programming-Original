<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CrmModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class CrmController extends BaseController
{
    private CrmModel $crmModel;
    private ?NotificationsModel $notificationsModel = null;
    private const DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->crmModel = new CrmModel();
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
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $tier = isset($_GET['tier']) ? strtolower(trim((string) $_GET['tier'])) : 'all';
        $allowedTiers = ['all', 'new', 'silver', 'gold', 'vip'];
        if (!in_array($tier, $allowedTiers, true)) {
            $tier = 'all';
        }

        $stats = $this->crmModel->getDashboardStats();
        $customerPage = $this->crmModel->getCustomersPage($page, $perPage, $tier, $search);
        $pagination = $this->buildPaginationData(
            $customerPage['page'],
            $customerPage['perPage'],
            $customerPage['totalItems'],
            $customerPage['totalPages'],
            $customerPage['offset']
        );

        $this->render('crm/index', [
            'pageTitle' => 'CRM',
            'pageSubtitle' => 'View and Manage Operational Data',
            'stats' => $stats,
            'customers' => $customerPage['items'],
            'pagination' => $pagination,
            'selectedTier' => $tier,
            'searchTerm' => $search,
            'perPage' => $customerPage['perPage']
        ], [
            'styles' => ['css/crm.css'],
            'scripts' => ['js/crm.js']
        ]);
    }

    private function handleAjax(string $action): void
    {
        $payload = $this->readJsonBody();
        $relatedId = (int) ($payload['customer_id'] ?? 0);

        switch ($action) {
            case 'customer-updated':
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'status_update',
                        'CRM',
                        max(1, $relatedId),
                        'Customer profile has been updated in CRM.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'CRM customer update notification created.'
                ]);
                return;

            case 'new-booking':
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'reservation_confirmed',
                        'CRM',
                        max(1, $relatedId),
                        'New booking has been added in CRM.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'CRM booking notification created.'
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

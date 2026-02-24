<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CrmModel.php';
require_once __DIR__ . '/../includes/auth.php';

class CrmController extends BaseController
{
    private CrmModel $crmModel;
    private const DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->crmModel = new CrmModel();
    }

    public function index(): void
    {
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
}

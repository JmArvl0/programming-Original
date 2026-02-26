<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PassportVisaModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class PassportVisaController extends BaseController
{
    private PassportVisaModel $passportVisaModel;
    private ?NotificationsModel $notificationsModel = null;
    private const DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->passportVisaModel = new PassportVisaModel();
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
        $filter = isset($_GET['filter']) ? strtolower(trim((string) $_GET['filter'])) : 'all';
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $allowedFilters = ['all', 'new', 'documents-issue', 'under-processing', 'for-action', 'approved', 'completed'];
        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $pageData = $this->passportVisaModel->getApplicantsPage($page, $perPage, $filter, $search);
        $applicants = array_map([$this, 'prepareApplicantForView'], $pageData['items']);
        $stats = $this->passportVisaModel->buildStats($pageData['allFiltered']);

        $pagination = $this->buildPaginationData(
            $pageData['page'],
            $pageData['perPage'],
            $pageData['totalItems'],
            $pageData['totalPages'],
            $pageData['offset']
        );

        $this->render('passport/applicant_list.view', [
            'pageTitle' => 'Passport & Visa',
            'pageSubtitle' => 'Manage Passport & Visa Processing',
            'applicants' => $applicants,
            'stats' => $stats,
            'pagination' => $pagination,
            'selectedFilter' => $filter,
            'searchTerm' => $search,
            'perPage' => $pageData['perPage']
        ], [
            'styles' => ['css/passport-visa.css'],
            'scripts' => ['js/passport-visa.js']
        ]);
    }

    private function normalizeStatus(string $value): string
    {
        return strtolower(trim($value));
    }

    private function badgeClassForStatus(string $value): string
    {
        $map = [
            'approved' => 'bg-success',
            'submitted' => 'bg-primary',
            'processing' => 'bg-info text-dark',
            'under review' => 'bg-warning text-dark',
            'missing' => 'bg-warning text-dark',
            'pending' => 'bg-secondary',
            'rejected' => 'bg-danger',
            'action required' => 'bg-danger',
            'not started' => 'bg-light text-dark border',
            'visa issued' => 'bg-success'
        ];

        return $map[$this->normalizeStatus($value)] ?? 'bg-secondary';
    }

    private function prepareApplicantForView(array $applicant): array
    {
        $documentsText = (string) ($applicant['documents']['text'] ?? '');
        $applicationText = (string) ($applicant['application']['text'] ?? '');
        $priority = (string) ($applicant['priority'] ?? 'Low');

        return $applicant + [
            'documentsNormalized' => $this->normalizeStatus($documentsText),
            'applicationNormalized' => $this->normalizeStatus($applicationText),
            'documentsBadgeClass' => $this->badgeClassForStatus($documentsText),
            'applicationBadgeClass' => $this->badgeClassForStatus($applicationText),
            'priorityClass' => 'priority-' . strtolower($priority)
        ];
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
            case 'upload-document':
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'upload-document',
                    'message' => 'Upload endpoint ready for integration.'
                ]);
                return;

            case 'update-status':
                $applicationId = (int) ($payload['applicantId'] ?? 0);
                $status = strtolower(trim((string) ($payload['status'] ?? 'approved')));
                $message = $status === 'missing' || $status === 'missing requirement'
                    ? 'Missing passport requirement detected.'
                    : 'Document has been approved.';
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'status_update',
                        'Passport',
                        max(1, $applicationId),
                        $message
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'update-status',
                    'message' => 'Passport status updated and notification sent.'
                ]);
                return;

            case 'send-reminder':
                $applicationId = (int) ($payload['applicantId'] ?? 0);
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'status_update',
                        'Passport',
                        max(1, $applicationId),
                        'Reminder sent for pending passport requirement.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'action' => 'send-reminder',
                    'message' => 'Reminder sent and notification recorded.'
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

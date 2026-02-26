<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ScheduleRatesModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class ScheduleRatesController extends BaseController
{
    private ScheduleRatesModel $scheduleRatesModel;
    private ?NotificationsModel $notificationsModel = null;

    public function __construct()
    {
        $this->scheduleRatesModel = new ScheduleRatesModel();
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

        $data = $this->scheduleRatesModel->getPageData($_GET);
        $data['isScheduleView'] = ($data['selectedPurpose'] ?? 'schedule') === 'schedule';
        $data['pageTitle'] = 'Schedule & Rates';
        $data['pageSubtitle'] = 'Manage tour schedules, availability, and pricing';

        $this->render('schedule_rates/index', $data, [
            'styles' => ['css/schedule-rates.css'],
            'scripts' => ['js/schedule-rates.js']
        ]);
    }

    private function handleAjax(string $action): void
    {
        $payload = $this->readJsonBody();
        $relatedId = (int) ($payload['tour_id'] ?? 0);

        switch ($action) {
            case 'confirm-booking':
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'reservation_confirmed',
                        'Schedule',
                        max(1, $relatedId),
                        'Booking has been confirmed in Schedule.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Booking confirmed and notification sent.'
                ]);
                return;

            case 'update-rate':
                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        'status_update',
                        'Schedule',
                        max(1, $relatedId),
                        'Tour rate has changed. Please review latest pricing.'
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Rate update notification sent.'
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

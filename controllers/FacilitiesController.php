<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/FacilitiesModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class FacilitiesController extends BaseController
{
    private FacilitiesModel $facilitiesModel;
    private ?NotificationsModel $notificationsModel = null;

    public function __construct()
    {
        $this->facilitiesModel = new FacilitiesModel();
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

        $data = $this->facilitiesModel->getPageData($_GET);
        $data['isReservationRequestsView'] = ($data['selectedView'] ?? 'reservation_requests') === 'reservation_requests';
        $data['isAvailabilityOverviewView'] = ($data['selectedView'] ?? 'reservation_requests') === 'availability_overview';
        $data['isCoordinationStatusView'] = ($data['selectedView'] ?? 'reservation_requests') === 'coordination_status';
        $data['pageTitle'] = 'Facilities Reservation';
        $data['pageSubtitle'] = 'Customer service reservations and facility coordination';

        $this->render('facilities/index', $data, [
            'styles' => ['css/facilities.css'],
            'scripts' => ['js/facilities.js']
        ]);
    }

    private function handleAjax(string $action): void
    {
        $payload = $this->readJsonBody();
        $reservationId = (int) ($payload['reservation_id'] ?? 0);

        switch ($action) {
            case 'update-reservation':
                $status = strtolower(trim((string) ($payload['status'] ?? 'approved')));
                $message = $status === 'completed'
                    ? 'Facilities service has been completed.'
                    : 'Facilities reservation has been approved.';
                $type = $status === 'completed' ? 'status_update' : 'reservation_confirmed';

                if ($this->notificationsModel instanceof NotificationsModel) {
                    $this->notificationsModel->createNotification(
                        $type,
                        'Facilities',
                        max(1, $reservationId),
                        $message
                    );
                }
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Facilities update saved and notification sent.'
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

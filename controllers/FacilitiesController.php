<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/FacilitiesModel.php';
require_once __DIR__ . '/../includes/auth.php';

class FacilitiesController extends BaseController
{
    private FacilitiesModel $facilitiesModel;

    public function __construct()
    {
        $this->facilitiesModel = new FacilitiesModel();
    }

    public function index(): void
    {
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
}

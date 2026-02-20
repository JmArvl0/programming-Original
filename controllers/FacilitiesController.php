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
        $data['pageTitle'] = 'Facilities Reservation';
        $data['pageSubtitle'] = 'Manage and monitor all company owned assets';

        $this->render('facilities/index', $data, [
            'styles' => ['css/facilities.css'],
            'scripts' => ['js/facilities.js']
        ]);
    }
}

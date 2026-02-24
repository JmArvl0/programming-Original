<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ScheduleRatesModel.php';
require_once __DIR__ . '/../includes/auth.php';

class ScheduleRatesController extends BaseController
{
    private ScheduleRatesModel $scheduleRatesModel;

    public function __construct()
    {
        $this->scheduleRatesModel = new ScheduleRatesModel();
    }

    public function index(): void
    {
        $data = $this->scheduleRatesModel->getPageData($_GET);
        $data['isScheduleView'] = ($data['selectedPurpose'] ?? 'schedule') === 'schedule';
        $data['pageTitle'] = 'Schedule & Rates';
        $data['pageSubtitle'] = 'Manage tour schedules, availability, and pricing';

        $this->render('schedule_rates/index', $data, [
            'styles' => ['css/schedule-rates.css'],
            'scripts' => ['js/schedule-rates.js']
        ]);
    }
}

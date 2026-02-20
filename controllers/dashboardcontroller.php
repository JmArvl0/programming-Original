<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../includes/auth.php';

class DashboardController extends BaseController
{
    private DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index(): void
    {
        $firstNames = $this->dashboardModel->getFirstNames();
        $lastNames = $this->dashboardModel->getLastNames();

        $data = [
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Overall Overview of Operational Status',
            'firstNames' => $firstNames,
            'lastNames' => $lastNames,
            'urgentActions' => $this->dashboardModel->getUrgentActions(),
            'upcomingDepartures' => $this->dashboardModel->getUpcomingDepartures(),
            'operations' => $this->dashboardModel->getOperations(),
            'documentStatus' => $this->dashboardModel->getDocumentStatus(),
            'bookingData' => $this->dashboardModel->getBookingData(),
            'operationalHealth' => $this->dashboardModel->getOperationalHealth(),
            'customerRating' => $this->dashboardModel->getCustomerRating(),
            'ratingBars' => $this->dashboardModel->getRatingBars(),
            'crmTrends' => $this->dashboardModel->getCrmTrends(),
            'crmTierIcons' => $this->dashboardModel->getCrmTierIcons()
        ];

        $this->render('dashboard/index', $data, [
            'styles' => ['css/dashboard.css'],
            'scripts' => ['js/dashboard.js'],
            'inlineScripts' => [
                'window.dashboardData = ' . json_encode([
                    'firstNames' => $firstNames,
                    'lastNames' => $lastNames
                ]) . ';'
            ]
        ]);
    }
}

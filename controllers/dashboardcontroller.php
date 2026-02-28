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

        $urgentActions = $this->dashboardModel->getUrgentActions();
        $upcomingDepartures = $this->dashboardModel->getUpcomingDepartures();
        $operations = $this->dashboardModel->getOperations();
        $documentStatus = $this->dashboardModel->getDocumentStatus();
        $bookingData = $this->dashboardModel->getBookingData();
        $operationalHealth = $this->dashboardModel->getOperationalHealth();
        $customerRating = $this->dashboardModel->getCustomerRating();
        $ratingBars = $this->dashboardModel->getRatingBars();
        $crmTrends = $this->dashboardModel->getCrmTrends();
        $crmTierIcons = $this->dashboardModel->getCrmTierIcons();

        $ratingStars = $this->buildRatingStars($customerRating);
        $ratingPercent = round(($customerRating / 5) * 100, 2);
        $documentStatus['approvedRatio'] = max(0, 1 - (float) $documentStatus['ratio']);
        $documentStatus['ratioPercent'] = round((float) $documentStatus['ratio'] * 100, 2);
        $bookingData['percentageWidth'] = $bookingData['percentage'] . '%';
        $operationalHealthDisplay = $operationalHealth . '%';
        $urgentCount = count($urgentActions);
        $departuresCount = count($upcomingDepartures);

        $urgentActions = array_map([$this, 'prepareUrgentAction'], $urgentActions);
        $upcomingDepartures = array_map([$this, 'prepareDeparture'], $upcomingDepartures);
        $operations = array_map([$this, 'prepareOperation'], $operations);
        $crmTrends = array_map(static function (array $trend): array {
            $label = (string) ($trend['label'] ?? '');
            $percentage = (float) ($trend['percentage'] ?? 0);
            return $trend + [
                'tierSlug' => strtolower(str_replace(' ', '-', $label)),
                'percentageWidth' => $percentage . '%'
            ];
        }, $crmTrends);

        // Load facilities highlights for dashboard card
        require_once __DIR__ . '/../models/FacilitiesModel.php';
        $facilitiesModel = new FacilitiesModel();
        $facOverviewReservation = $facilitiesModel->getPageData(['view' => 'reservation_requests']);
        $facOverviewAvailability = $facilitiesModel->getPageData(['view' => 'availability_overview']);
        $facOverviewCoordination = $facilitiesModel->getPageData(['view' => 'coordination_status']);

        $data = [
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Overall Overview of Operational Status',
            'firstNames' => $firstNames,
            'lastNames' => $lastNames,
            'urgentActions' => $urgentActions,
            'upcomingDepartures' => $upcomingDepartures,
            'operations' => $operations,
            'documentStatus' => $documentStatus,
            'bookingData' => $bookingData,
            'operationalHealth' => $operationalHealth,
            'operationalHealthDisplay' => $operationalHealthDisplay,
            'customerRating' => $customerRating,
            'ratingStars' => $ratingStars,
            'ratingPercent' => $ratingPercent,
            'ratingBars' => $ratingBars,
            'crmTrends' => $crmTrends,
            'crmTierIcons' => $crmTierIcons,
            'urgentCount' => $urgentCount,
            'departuresCount' => $departuresCount,
            'facilitiesHighlights' => [
                'reservation_requests' => $facOverviewReservation['overviewCards'] ?? [],
                'availability_overview' => $facOverviewAvailability['overviewCards'] ?? [],
                'coordination_status' => $facOverviewCoordination['overviewCards'] ?? []
            ],
            'widgetConfig' => [
                'lazyReady' => true,
                'widgets' => ['urgent_queue', 'departures', 'crm_trends', 'operations_overview', 'map']
            ]
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

    private function prepareUrgentAction(array $action): array
    {
        $name = (string) ($action['name'] ?? '');
        return $action + ['rowId' => strtolower(str_replace(' ', '-', $name))];
    }

    private function prepareDeparture(array $departure): array
    {
        $name = (string) ($departure['name'] ?? '');
        return $departure + ['rowId' => strtolower(str_replace(' ', '-', $name))];
    }

    private function prepareOperation(array $operation): array
    {
        $name = (string) ($operation['name'] ?? '');
        return $operation + ['rowId' => strtolower(str_replace(' ', '-', $name))];
    }

    private function buildRatingStars(float $rating): string
    {
        $fullStars = (int) floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $stars = '';

        for ($i = 0; $i < 5; $i++) {
            if ($i < $fullStars || ($i === $fullStars && $hasHalfStar)) {
                $stars .= '★';
            } else {
                $stars .= '☆';
            }
        }

        return $stars;
    }
}

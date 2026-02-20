<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../includes/auth.php';

/**
 * Dashboard Controller
 * Handles all dashboard-related requests
 */
class DashboardController extends BaseController {
    
    private $dashboardModel;
    
    public function __construct() {
        $this->dashboardModel = new DashboardModel();
    }
    
    /**
     * Display the dashboard
     * @return void
     */
    public function index() {
        // Prepare data for the view
        $data = [
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Overall Overview of Operational Status',
            'firstNames' => $this->dashboardModel->getFirstNames(),
            'lastNames' => $this->dashboardModel->getLastNames(),
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
        
        // Load the view
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Get fresh dashboard data via AJAX
     * @return void
     */
    public function refreshData() {
        $data = [
            'urgentActions' => $this->dashboardModel->getUrgentActions(),
            'upcomingDepartures' => $this->dashboardModel->getUpcomingDepartures(),
            'operations' => $this->dashboardModel->getOperations(),
            'documentStatus' => $this->dashboardModel->getDocumentStatus(),
            'bookingData' => $this->dashboardModel->getBookingData(),
            'operationalHealth' => $this->dashboardModel->getOperationalHealth(),
            'customerRating' => $this->dashboardModel->getCustomerRating(),
            'ratingBars' => $this->dashboardModel->getRatingBars(),
            'crmTrends' => $this->dashboardModel->getCrmTrends()
        ];
        
        $this->jsonResponse($data);
    }
    
    /**
     * Handle view action for urgent items
     * @return void
     */
    public function viewItem() {
        $input = $this->getJsonInput();
        
        // Process view action
        $response = [
            'success' => true,
            'message' => 'Item viewed successfully',
            'data' => $input
        ];
        
        $this->jsonResponse($response);
    }
    
    /**
     * Handle remind action for departures
     * @return void
     */
    public function remindCustomer() {
        $input = $this->getJsonInput();
        
        // Process remind action
        $response = [
            'success' => true,
            'message' => 'Reminder sent successfully',
            'data' => $input
        ];
        
        $this->jsonResponse($response);
    }
    
    /**
     * Refresh map locations
     * @return void
     */
    public function refreshMap() {
        // Generate random map locations
        $locations = [];
        for ($i = 0; $i < 5; $i++) {
            $locations[] = [
                'lat' => rand(-90, 90) + (rand(0, 999) / 1000),
                'lng' => rand(-180, 180) + (rand(0, 999) / 1000),
                'type' => $this->dashboardModel->getOperationTypes()[array_rand($this->dashboardModel->getOperationTypes())]
            ];
        }
        
        $this->jsonResponse(['locations' => $locations]);
    }
}
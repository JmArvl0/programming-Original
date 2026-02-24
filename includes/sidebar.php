<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$selectedPurposeSidebar = isset($_GET['purpose']) ? (string) $_GET['purpose'] : 'schedule';
$selectedFacilitiesViewSidebar = isset($_GET['view']) ? (string) $_GET['view'] : 'reservation_requests';
$isScheduleRatesPage = $currentPage === 'schedule_rates.php';
$isScheduleListActive = $isScheduleRatesPage && $selectedPurposeSidebar !== 'tour_rates';
$isRatesListActive = $isScheduleRatesPage && $selectedPurposeSidebar === 'tour_rates';
$isScheduleRatesExpanded = $isScheduleRatesPage ? 'true' : 'false';
$scheduleRatesCollapseClass = $isScheduleRatesPage ? 'show' : '';
$isFacilitiesPage = $currentPage === 'facilities.php';
$isFacilitiesRequestsActive = $isFacilitiesPage && $selectedFacilitiesViewSidebar === 'reservation_requests';
$isFacilitiesAvailabilityActive = $isFacilitiesPage && $selectedFacilitiesViewSidebar === 'availability_overview';
$isFacilitiesCoordinationActive = $isFacilitiesPage && $selectedFacilitiesViewSidebar === 'coordination_status';
$isFacilitiesExpanded = $isFacilitiesPage ? 'true' : 'false';
$facilitiesCollapseClass = $isFacilitiesPage ? 'show' : '';
?>
<!-- Offcanvas Sidebar Navigation -->
<div class="offcanvas offcanvas-start text-white" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
    <div class="offcanvas-body p-3">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="logo-container text-center mb-4 pb-3 border-bottom position-relative" style="border-color: rgba(255, 255, 255, 0.2) !important;">
            <!-- Company Logo -->
            <div class="company-logo mb-3">
                <div class="logo-wrapper d-flex flex-column align-items-center">
                    <img src="assets/LOGO.png" alt="Company Logo" style="width: 180px; height: auto; border-radius: 5px;">
                    <!-- Fallback text logo if image doesn't load -->
                    <div class="fallback-logo" style="display: none; text-align: center; color: white;">
                        <div class="fw-bold" style="font-size: 1.5rem; line-height: 1.2;">BEYOND THE MAP</div>
                        <div class="small mt-1">TRAVEL & TOURS</div>
                    </div>
                </div>
            </div>

            <div class="company-info mt-2">
                <div class="company-name fw-bold" style="font-size: 1.1rem;">Beyond The Map</div>
                <div class="company-subtitle small opacity-75">Travel & Tours</div>
            </div>
        </div>

        <ul class="nav-menu list-unstyled flex-grow-1 mb-0">
        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" data-bs-toggle="tooltip" title="Dashboard">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/>
                    </svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item mb-2">
            <a href="account_executive.php" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $currentPage == 'account_executive.php' ? 'active' : ''; ?>" data-bs-toggle="tooltip" title="Account Executive">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <span class="nav-text">Account Executive</span>
            </a>
        </li>
        
        <li class="nav-item mb-2">
            <a href="passport_visa.php" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $currentPage == 'passport_visa.php' ? 'active' : ''; ?>" data-bs-toggle="tooltip" title="Passport & Visa">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v4h10V2a1 1 0 0 0-1-1zm9 6H6v2h7zm0 3H6v2h7zm0 3H6v2h6a1 1 0 0 0 1-1zm-8 2v-2H3v1a1 1 0 0 0 1 1z"/>
                    </svg>
                </span>
                <span class="nav-text">Passport & Visa</span>
            </a>
        </li>
        
        <li class="nav-item mb-2 nav-item-has-submenu">
            <a href="#scheduleRatesSubmenu" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $isScheduleRatesPage ? 'active' : ''; ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo $isScheduleRatesExpanded; ?>" aria-controls="scheduleRatesSubmenu" title="Schedule & Rates">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                    </svg>
                </span>
                <span class="nav-text">Schedule & Rates</span>
                <span class="submenu-caret ms-auto">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <ul class="submenu collapse <?php echo $scheduleRatesCollapseClass; ?>" id="scheduleRatesSubmenu">
                <li>
                    <a href="schedule_rates.php?purpose=schedule" class="submenu-link <?php echo $isScheduleListActive ? 'active' : ''; ?>">
                        List of Schedule
                    </a>
                </li>
                <li>
                    <a href="schedule_rates.php?purpose=tour_rates" class="submenu-link <?php echo $isRatesListActive ? 'active' : ''; ?>">
                        List of Rates
                    </a>
                </li>
            </ul>
        </li>
        
        <li class="nav-item mb-2">
            <a href="crm.php" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $currentPage == 'crm.php' ? 'active' : ''; ?>" data-bs-toggle="tooltip" title="CRM">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 2.5A1.5 1.5 0 0 1 2.5 1h1A1.5 1.5 0 0 1 5 2.5h4.134a1 1 0 1 1 0 1h-2.01q.269.27.484.605C8.246 5.097 8.5 6.459 8.5 8c0 1.993.257 3.092.713 3.7.356.476.895.721 1.787.784A1.5 1.5 0 0 1 12.5 11h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5H6.866a1 1 0 1 1 0-1h1.711a3 3 0 0 1-.165-.2C7.743 11.407 7.5 10.007 7.5 8c0-1.46-.246-2.597-.733-3.355-.39-.605-.952-1-1.767-1.112A1.5 1.5 0 0 1 3.5 5h-1A1.5 1.5 0 0 1 1 3.5zM2.5 2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm10 10a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                    </svg>
                </span>
                <span class="nav-text">CRM</span>
            </a>
        </li>
        
        <li class="nav-item mb-2 nav-item-has-submenu">
            <a href="#facilitiesSubmenu" class="nav-link text-white d-flex align-items-center p-3 rounded <?php echo $isFacilitiesPage ? 'active' : ''; ?>" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo $isFacilitiesExpanded; ?>" aria-controls="facilitiesSubmenu" title="Facilities and Reservation">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M7.84 4.1a.178.178 0 0 1 .32 0l.634 1.285a.18.18 0 0 0 .134.098l1.42.206c.145.021.204.2.098.303L9.42 6.993a.18.18 0 0 0-.051.158l.242 1.414a.178.178 0 0 1-.258.187l-1.27-.668a.18.18 0 0 0-.165 0l-1.27.668a.178.178 0 0 1-.257-.187l.242-1.414a.18.18 0 0 0-.05-.158l-1.03-1.001a.178.178 0 0 1 .098-.303l1.42-.206a.18.18 0 0 0 .134-.098z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
                    </svg>
                </span>
                <span class="nav-text">Facilities and Reservation</span>
                <span class="submenu-caret ms-auto">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <ul class="submenu collapse <?php echo $facilitiesCollapseClass; ?>" id="facilitiesSubmenu">
                <li>
                    <a href="facilities.php?view=reservation_requests" class="submenu-link <?php echo $isFacilitiesRequestsActive ? 'active' : ''; ?>">
                        Reservation Requests
                    </a>
                </li>
                <li>
                    <a href="facilities.php?view=availability_overview" class="submenu-link <?php echo $isFacilitiesAvailabilityActive ? 'active' : ''; ?>">
                        Availability Overview
                    </a>
                </li>
                <li>
                    <a href="facilities.php?view=coordination_status" class="submenu-link <?php echo $isFacilitiesCoordinationActive ? 'active' : ''; ?>">
                        Coordination Status
                    </a>
                </li>
            </ul>
        </li>
        </ul>

        <div class="logout mt-auto pt-3 border-top" style="border-color: rgba(255, 255, 255, 0.2) !important;">
            <a href="logout.php" class="nav-link text-white d-flex align-items-center p-3 rounded" data-bs-toggle="tooltip" title="Logout">
                <span class="nav-icon me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>
                        <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                    </svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>
</div>


<link rel="stylesheet" href="css/sidebar.css">
<!-- Sidebar script disabled for presentation -->



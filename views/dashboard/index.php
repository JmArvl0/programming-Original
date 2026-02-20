<!-- Welcome Section -->
<div class="metric-card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="metric-icon me-3" style="background: linear-gradient(135deg, #007bff20, #007bff40); color: var(--primary-blue);">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-person-raised-hand" viewBox="0 0 16 16">
                    <path d="M6 6.207v9.043a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H6.236a1 1 0 0 1-.447-.106l-.33-.165A.83.83 0 0 1 5 2.488V.75a.75.75 0 0 0-1.5 0v2.083c0 .715.404 1.37 1.044 1.689L5.5 5c.32.32.5.754.5 1.207"/>
                    <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                </svg>
            </div>
            <div>
                <h2 class="mb-1" style="color: #333;">Welcome, Staff</h2>
                <p class="mb-0 text-muted">Here's what's happening with your business today</p>
            </div>
        </div>
    </div>
</div>

<!-- Top Row Widgets -->
<div class="row">
    <!-- Operational Health Card -->
    <div class="col-md-3 mb-4">
        <div class="metric-card highlight-card">
            <div class="card-body">
                <div class="card-header-section">
                    <div>
                        <div class="metric-title">Operational Health</div>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #f59e0b20, #f59e0b40); color: #f59e0b;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building-fill-gear" viewBox="0 0 16 16">
                            <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v7.256A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-3.59 1.787A.5.5 0 0 0 9 9.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .39-.187A4.5 4.5 0 0 0 8.027 12H6.5a.5.5 0 0 0-.5.5V16H3a1 1 0 0 1-1-1zm2 1.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3 0v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                            <path d="M11.886 9.46c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                        </svg>
                    </div>
                </div>
                <div class="donut-chart-container">
                    <svg class="donut-chart-svg" viewBox="0 0 120 120">
                        <circle class="donut-chart-bg" cx="55" cy="55" r="45"></circle>
                        <circle class="donut-chart-progress" cx="55" cy="55" r="45" 
                                style="stroke: #f59e0b; --percentage: <?php echo $operationalHealth; ?>;"></circle>
                    </svg>
                    <div class="donut-chart-value"><?php echo $operationalHealth; ?>%</div>
                </div>
                <div class="donut-chart-label">Total Weighted Usage</div>
            </div>
        </div>
    </div>

    <!-- Total Bookings Card -->
    <div class="col-md-3 mb-4">
        <div class="metric-card highlight-card">
            <div class="card-body">
                <div class="card-header-section">
                    <div>
                        <div class="metric-title">Total Bookings</div>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #28a74520, #28a74540); color: var(--success-green);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-range-fill" viewBox="0 0 16 16">
                            <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 7V5H0v5h5a1 1 0 1 1 0 2H0v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9h-6a1 1 0 1 1 0-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="progress-value-index booking-slots-value-wrap">
                    <span class="progress-value"><?php echo $bookingData['used_slots']; ?><p class="total-slots">/<?php echo $bookingData['total_slots']; ?></p></span>
                </div>
                <div class="metric-progress-container">
                    <div class="progress-header">
                        <span class="progress-percentage"><?php echo $bookingData['percentage']; ?>%</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill-custom" style="width: <?php echo $bookingData['percentage']; ?>%;"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Available</span>
                        <span class="fw-bold"><?php echo $bookingData['available']; ?> slots</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Card -->
    <div class="col-md-3 mb-4">
        <div class="metric-card highlight-card">
            <div class="card-body">
                <div class="card-header-section">
                    <div>
                        <div class="metric-title">Documents Status</div>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #dc354520, #dc354540); color: var(--danger-red);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-text-fill" viewBox="0 0 16 16">
                            <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1m-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1m0 2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-0">
                    <div class="urgent-document">
                        <span class="urgent-document-text fw-bold text-danger"><?php echo $documentStatus['urgent']; ?> <p class="urgency-status">Action Required</p></span>
                    </div>
                    <div class="document-status">
                        <div class="status-bars">
                            <div class="status-urgent" style="flex: <?php echo $documentStatus['ratio']; ?>;"></div>
                            <div class="status-approved" style="flex: <?php echo 1 - $documentStatus['ratio']; ?>;"></div>
                        </div>
                        <div class="status-labels">
                            <span>Urgent</span>
                            <span>Approved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Feedback Card -->
    <div class="col-md-3 mb-4">
        <div class="metric-card highlight-card">
            <div class="card-body">
                <div class="card-header-section">
                    <div>
                        <div class="metric-title">Customer Feedback</div>
                    </div>
                    <div class="metric-icon" style="background: linear-gradient(135deg, #ffc10720, #ffc10740); color: var(--warning-yellow);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-line" viewBox="0 0 16 16">
                            <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z"/>
                        </svg>
                    </div>
                </div>
                <div class="rating-container">
                    <div class="rating-score">
                        <span class="rating-value"><?php echo $customerRating; ?></span>
                        <span class="rating-max">/5.0</span>
                    </div>
                    <div class="stars">
                        <?php 
                        $fullStars = floor($customerRating);
                        $halfStar = ($customerRating - $fullStars) >= 0.5;
                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $fullStars) {
                                echo '★';
                            } elseif ($i == $fullStars && $halfStar) {
                                echo '★';
                            } else {
                                echo '☆';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="rating-chart">
                        <?php foreach ($ratingBars as $height): ?>
                            <div class="rating-bar" style="height: <?php echo $height; ?>%;"></div>
                        <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Middle Row Widgets -->
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="metric-card">
            <div class="card-body">
                <div class="card-header-section mb-3">
                    <div>
                        <div class="metric-title">Urgent Action Queue</div>
                    </div>
                    <div class="badge bg-danger"><?php echo count($urgentActions); ?> Pending</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urgentActions as $action): ?>
                            <tr class="clickable-row" data-action="view" data-id="<?php echo strtolower(str_replace(' ', '-', $action['name'])); ?>">
                                <td><?php echo $action['name']; ?></td>
                                <td><?php echo $action['type']; ?></td>
                                <td>
                                    <span class="status-dot status-<?php echo $action['status']['color']; ?>"></span>
                                    <?php echo $action['status']['status']; ?>
                                </td>
                                <td><?php echo $action['deadline']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action view-btn" 
                                            data-name="<?php echo htmlspecialchars($action['name']); ?>"
                                            data-type="<?php echo htmlspecialchars($action['type']); ?>"
                                            data-status="<?php echo htmlspecialchars($action['status']['status']); ?>"
                                            data-deadline="<?php echo htmlspecialchars($action['deadline']); ?>">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="metric-card">
            <div class="card-body">
                <div class="card-header-section mb-3">
                    <div>
                        <div class="metric-title">Upcoming Departures</div>
                    </div>
                    <div class="badge bg-warning"><?php echo count($upcomingDepartures); ?> Today</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Departure</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingDepartures as $departure): ?>
                            <tr class="clickable-row" data-action="remind" data-id="<?php echo strtolower(str_replace(' ', '-', $departure['name'])); ?>">
                                <td><?php echo $departure['name']; ?></td>
                                <td><?php echo $departure['departure']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-action remind-btn" 
                                            data-name="<?php echo htmlspecialchars($departure['name']); ?>"
                                            data-departure="<?php echo htmlspecialchars($departure['departure']); ?>">
                                        Remind
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row Widgets -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="metric-card">
            <div class="card-body">
                <div class="card-header-section crm-trends-header mb-3">
                    <div class="metric-title">CRM Trends</div>
                    <div class="crm-vip-indicator">
                        VIP <?php echo $crmTrends[0]['percentage']; ?>%
                    </div>
                </div>
                <div class="crm-trends-grid mt-3">
                    <?php foreach ($crmTrends as $trend): ?>
                    <?php $tierSlug = strtolower(str_replace(' ', '-', $trend['label'])); ?>
                    <div class="crm-tier-box">
                        <div class="crm-tier-top">
                            <span class="crm-tier-count"><?php echo $trend['percentage']; ?>% Customers</span>
                            <span class="crm-tier-label"><?php echo $trend['label']; ?></span>
                        </div>
                        <div class="crm-tier-icon crm-tier-icon-<?php echo $tierSlug; ?>"><?php echo $crmTierIcons[$trend['label']] ?? '&#9679;'; ?></div>
                        <div class="progress crm-tier-progress">
                            <div class="progress-bar crm-tier-progress-<?php echo $tierSlug; ?>" style="width: <?php echo $trend['percentage']; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="metric-card">
            <div class="card-body">
                <div class="card-header-section mb-3">
                    <div class="metric-title">Operations Overview</div>
                </div>
                <div class="map-container text-center">
                    <div class="fs-1 mb-2">🗺️</div>
                    <p class="fw-bold">Live Map View</p>
                    <p class="small text-muted">Interactive map with transport and staff locations</p>
                    <button class="btn btn-primary btn-sm mt-2" id="refreshMapBtn">Refresh Locations</button>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Staff On-Duty</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($operations as $operation): ?>
                            <tr class="clickable-row" data-action="track" data-id="<?php echo strtolower(str_replace(' ', '-', $operation['name'])); ?>">
                                <td><?php echo $operation['name']; ?></td>
                                <td><?php echo $operation['staff']; ?></td>
                                <td><?php echo $operation['type']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
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
                        <circle class="donut-chart-progress" cx="55" cy="55" r="45" style="stroke: #f59e0b; --percentage: <?= (int) $operationalHealth ?>;"></circle>
                    </svg>
                    <div class="donut-chart-value"><?= htmlspecialchars($operationalHealthDisplay, ENT_QUOTES, 'UTF-8') ?></div>
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
                    <span class="progress-value"><?= (int) $bookingData['used_slots'] ?><p class="total-slots">/<?= (int) $bookingData['total_slots'] ?></p></span>
                </div>
                <div class="metric-progress-container">
                    <div class="progress-header">
                        <span class="progress-percentage"><?= (int) $bookingData['percentage'] ?>%</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill-custom" style="width: <?= htmlspecialchars($bookingData['percentageWidth'], ENT_QUOTES, 'UTF-8') ?>;"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Available</span>
                        <span class="fw-bold"><?= (int) $bookingData['available'] ?> slots</span>
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
                        <span class="urgent-document-text fw-bold text-danger"><?= (int) $documentStatus['urgent'] ?> <p class="urgency-status">Action Required</p></span>
                    </div>
                    <div class="document-status">
                        <div class="status-bars">
                            <div class="status-urgent" style="flex: <?= (float) $documentStatus['ratio'] ?>;"></div>
                            <div class="status-approved" style="flex: <?= (float) $documentStatus['approvedRatio'] ?>;"></div>
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
                        <span class="rating-value"><?= number_format((float) $customerRating, 1) ?></span>
                        <span class="rating-max">/5.0</span>
                    </div>
                    <div class="stars"><?= htmlspecialchars($ratingStars, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="rating-chart">
                    <?php foreach ($ratingBars as $height): ?>
                    <div class="rating-bar" style="height: <?= (int) $height ?>%;"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

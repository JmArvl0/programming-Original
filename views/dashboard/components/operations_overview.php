<div class="operations-overview-widget" data-widget="operations_overview" data-lazy-ready="true">
    <div class="metric-card highlight-card">
        <div class="operations-overview-body">
            <div class="card-header-section operations-overview-header">
                <div>
                    <div class="metric-title">Facilities Reservation</div>
                </div>
                <div>
                    <a href="facilities.php" class="btn btn-sm btn-outline-primary">Open Facilities</a>
                </div>
            </div>
            <div class="facilities-highlights">
                <?php $fh = $facilitiesHighlights ?? []; ?>
                <?php
                    $res = $fh['reservation_requests'] ?? [];
                    $totalRes = isset($res[0]['value']) ? (int) $res[0]['value'] : 0;
                    $pendingRes = isset($res[1]['value']) ? (int) $res[1]['value'] : 0;
                    $percentPending = $totalRes > 0 ? (int) round(($pendingRes / $totalRes) * 100) : 0;

                    $avail = $fh['availability_overview'] ?? [];
                    $totalFacilities = isset($avail[0]['value']) ? (int) $avail[0]['value'] : 0;
                    $openSlots = isset($avail[4]['value']) ? (int) $avail[4]['value'] : 0;

                    $coord = $fh['coordination_status'] ?? [];
                    $activeOps = isset($coord[1]['value']) ? (int) $coord[1]['value'] : 0;
                ?>

                <div class="facilities-layout">
                    <div class="facilities-chart-col">
                        <div class="donut-chart-container operations-overview-donut">
                            <svg class="donut-chart-svg" viewBox="0 0 120 120">
                                <circle class="donut-chart-bg" cx="55" cy="55" r="45"></circle>
                                <circle class="donut-chart-progress" cx="55" cy="55" r="45" data-percentage="<?= $percentPending; ?>"></circle>
                            </svg>
                            <div class="donut-chart-value"><?= $percentPending ?>%</div>
                        </div>
                        <div class="donut-chart-label mt-3">Pending Reservations</div>
                        <div class="text-muted small mt-1"><?= $pendingRes ?> of <?= $totalRes ?> today</div>
                    </div>
                    <div class="facilities-stats-col">
                        <div class="facilities-stats-grid">
                            <div class="facilities-stat-item">
                                <div class="metric-card small">
                                    <div class="card-body">
                                        <div class="facilities-stat-icon" aria-hidden="true"><i class="fas fa-building"></i></div>
                                        <div class="metric-title">Total Facilities</div>
                                        <div class="value mt-2"><?= $totalFacilities ?></div>
                                        <div class="text-muted small">Overview</div>
                                    </div>
                                </div>
                            </div>
                            <div class="facilities-stat-item">
                                <div class="metric-card small">
                                    <div class="card-body">
                                        <div class="facilities-stat-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></div>
                                        <div class="metric-title">Open Slots</div>
                                        <div class="value mt-2"><?= $openSlots ?></div>
                                        <div class="text-muted small">Capacity Left</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="facilities-active-row">
                            <div class="metric-card small">
                                <div class="card-body active-ops-body">
                                    <div>
                                        <div class="facilities-stat-icon" aria-hidden="true"><i class="fas fa-truck-fast"></i></div>
                                        <div class="metric-title">Active Ops</div>
                                        <div class="value mt-2"><?= $activeOps ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

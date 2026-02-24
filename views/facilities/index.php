<div class="widget" style="margin-bottom: 10px;">
    <form method="GET" action="" class="facilities-toolbar">
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($selectedView, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="page" value="1">
        <?php
            $facilitiesSectionTitle = 'Facility Reservation Requests';
            if ($selectedView === 'availability_overview') {
                $facilitiesSectionTitle = 'Facility Availability Overview';
            } elseif ($selectedView === 'coordination_status') {
                $facilitiesSectionTitle = 'Service Coordination Status';
            }
        ?>
        <h2 class="section-title"><?php echo htmlspecialchars($facilitiesSectionTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="facilities-search-wrap">
            <i class="fas fa-search search-icon" aria-hidden="true"></i>
            <input type="text" name="search" class="search-input" placeholder="Search customer, reference, facility..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
    </form>
</div>

<?php
    $buildFacilitiesUrl = static function (array $overrides = []) use ($selectedView, $searchTerm): string {
        $params = [
            'view' => $selectedView
        ];
        if ($searchTerm !== '') {
            $params['search'] = $searchTerm;
        }

        $params = array_merge($params, $overrides);
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
            }
        }

        return 'facilities.php' . (count($params) > 0 ? '?' . http_build_query($params) : '');
    };

    $currentPage = (int) ($pagination['page'] ?? 1);
    $totalPages = (int) ($pagination['totalPages'] ?? 1);
    $maxPageLinks = 10;
    $startPage = max(1, $currentPage - (int) floor($maxPageLinks / 2));
    $endPage = min($totalPages, $startPage + $maxPageLinks - 1);
    if (($endPage - $startPage + 1) < $maxPageLinks) {
        $startPage = max(1, $endPage - $maxPageLinks + 1);
    }
?>

<div class="widget-grid">
    <?php foreach (($overviewCards ?? []) as $card): ?>
    <div class="widget">
        <div class="widget-title">
            <span><?php echo htmlspecialchars((string) ($card['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="fas <?php echo htmlspecialchars((string) ($card['icon'] ?? 'fa-chart-bar'), ENT_QUOTES, 'UTF-8'); ?>"></i>
        </div>
        <div style="margin-top: 20px;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 15px;"><?php echo (int) ($card['value'] ?? 0); ?></div>
            <div class="progress-info">
                <span><?php echo htmlspecialchars((string) ($card['metaLeft'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                <span><?php echo htmlspecialchars((string) ($card['metaRight'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($isReservationRequestsView): ?>
<div class="table-section">
    <div class="header-container">
        <small style="font-size: 14px; color: #28a745; font-weight: normal; margin-left: 10px;">
            (<?php echo count($reservationRequests); ?> records)
        </small>

        <div class="table-controls">
            <button class="filter-btn" onclick="addReservationRequest()">
                <i class="fas fa-plus"></i>
                <span>New Request</span>
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Trip / Booking Reference</th>
                    <th>Facility / Service Type</th>
                    <th>Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reservationRequests) > 0): ?>
                    <?php foreach ($reservationRequests as $request): ?>
                    <?php
                        $statusColorMap = [
                            'Requested' => 'blue',
                            'Approved' => 'yellow',
                            'Assigned' => 'gray',
                            'In Progress' => 'green',
                            'Completed' => 'green'
                        ];
                        $badgeClass = 'badge-' . ($statusColorMap[$request['status']] ?? 'gray');
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($request['customerName'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td><?php echo htmlspecialchars($request['bookingReference'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($request['facilityType'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($request['date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php
                                $priorityClass = strtolower($request['priority']) === 'high'
                                    ? 'priority-high'
                                    : 'priority-normal';
                            ?>
                            <span class="priority-badge <?php echo $priorityClass; ?>">
                                <?php echo htmlspecialchars($request['priority'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                <?php echo htmlspecialchars($request['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" onclick="viewReservationRequest(<?php echo (int) $request['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn-track" onclick="updateReservationStatus(<?php echo (int) $request['id']; ?>)">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--secondary-color);">
                        <i class="fas fa-concierge-bell fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h4>No reservation requests found</h4>
                        <p>Try changing your search criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer d-flex justify-content-between align-items-center mt-3">
        <div class="entries-info">
            Showing <span><?= (int) $pagination['start'] ?></span>-<span><?= (int) $pagination['end'] ?></span> of <span><?= (int) $pagination['totalItems'] ?></span> entries
        </div>
        <div class="pagination-wrap">
            <nav aria-label="Facilities pagination">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => max(1, $currentPage - 1)]), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    </li>
                    <?php for ($pageNum = $startPage; $pageNum <= $endPage; $pageNum++): ?>
                    <li class="page-item <?= $currentPage === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => $pageNum]), ENT_QUOTES, 'UTF-8') ?>" <?= $currentPage === $pageNum ? 'aria-current="page"' : '' ?>><?= $pageNum ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => min($totalPages, $currentPage + 1)]), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php elseif ($isAvailabilityOverviewView): ?>
<div class="table-section">
    <div class="table-header">
        <small style="font-size: 14px; color: var(--secondary-color); font-weight: normal; margin-left: 10px;">
            (<?php echo count($facilityAvailability); ?> facilities)
        </small>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Facility Name</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Reserved Today</th>
                    <th>Available Slots</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($facilityAvailability) > 0): ?>
                    <?php foreach ($facilityAvailability as $facility): ?>
                    <?php $badgeClass = 'badge-' . $facility['status']['color']; ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($facility['facilityName'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td><span class="asset-type"><?php echo htmlspecialchars($facility['type'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?php echo (int) $facility['capacity']; ?></td>
                        <td><?php echo (int) $facility['reservedToday']; ?></td>
                        <td><?php echo (int) $facility['availableSlots']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                <?php echo htmlspecialchars($facility['status']['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--secondary-color);">
                        <i class="fas fa-list-check fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h4>No facility overview records found</h4>
                        <p>Try changing your filter criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer d-flex justify-content-between align-items-center">
        <div class="entries-info">
            Showing <span><?= (int) $pagination['start'] ?></span>-<span><?= (int) $pagination['end'] ?></span> of <span><?= (int) $pagination['totalItems'] ?></span> entries
        </div>
        <div class="pagination-wrap">
            <nav aria-label="Facilities pagination">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => max(1, $currentPage - 1)]), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    </li>
                    <?php for ($pageNum = $startPage; $pageNum <= $endPage; $pageNum++): ?>
                    <li class="page-item <?= $currentPage === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => $pageNum]), ENT_QUOTES, 'UTF-8') ?>" <?= $currentPage === $pageNum ? 'aria-current="page"' : '' ?>><?= $pageNum ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => min($totalPages, $currentPage + 1)]), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php else: ?>
<div class="table-section">
    <div class="table-header">
        <small style="font-size: 14px; color: var(--secondary-color); font-weight: normal; margin-left: 10px;">
            (Read-only feed from Logistics)
        </small>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Facility</th>
                    <th>Assigned Staff</th>
                    <th>Logistics Status</th>
                    <th>Completion Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($coordinationStatuses) > 0): ?>
                    <?php foreach ($coordinationStatuses as $coordination): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($coordination['customerName'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td><?php echo htmlspecialchars($coordination['facility'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($coordination['assignedStaff'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="status-badge badge-blue">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                <?php echo htmlspecialchars($coordination['logisticsStatus'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($coordination['completionTime'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--secondary-color);">
                        <i class="fas fa-link fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h4>No logistics coordination records found</h4>
                        <p>No data was received from Logistics for the selected criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer d-flex justify-content-between align-items-center">
        <div class="entries-info">
            Showing <span><?= (int) $pagination['start'] ?></span>-<span><?= (int) $pagination['end'] ?></span> of <span><?= (int) $pagination['totalItems'] ?></span> entries
        </div>
        <div class="pagination-wrap">
            <nav aria-label="Facilities pagination">
                <ul class="pagination mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => max(1, $currentPage - 1)]), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    </li>
                    <?php for ($pageNum = $startPage; $pageNum <= $endPage; $pageNum++): ?>
                    <li class="page-item <?= $currentPage === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => $pageNum]), ENT_QUOTES, 'UTF-8') ?>" <?= $currentPage === $pageNum ? 'aria-current="page"' : '' ?>><?= $pageNum ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildFacilitiesUrl(['page' => min($totalPages, $currentPage + 1)]), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?>

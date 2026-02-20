<div class="widget" style="margin-bottom: 30px;">
    <form method="GET" action="" class="d-flex gap-2" style="flex-wrap: wrap;">
        <div class="filter-dropdown">
            <button type="button" class="filter-btn">
                <i class="fas fa-layer-group"></i>
                <span>Type: <?php echo $filterType === 'all' ? 'All Types' : $filterType; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="filter-content">
                <a href="?type=all&amp;status=<?php echo urlencode($filterStatus); ?>&amp;search=<?php echo urlencode($searchTerm); ?>" class="<?php echo $filterType === 'all' ? 'active' : ''; ?>">All Types</a>
                <?php foreach ($assetTypes as $type): ?>
                <a href="?type=<?php echo urlencode($type); ?>&amp;status=<?php echo urlencode($filterStatus); ?>&amp;search=<?php echo urlencode($searchTerm); ?>" class="<?php echo $filterType === $type ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="filter-dropdown">
            <button type="button" class="filter-btn">
                <i class="fas fa-circle"></i>
                <span>Status: <?php echo $filterStatus === 'all' ? 'All Statuses' : $filterStatus; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="filter-content">
                <a href="?type=<?php echo urlencode($filterType); ?>&amp;status=all&amp;search=<?php echo urlencode($searchTerm); ?>" class="<?php echo $filterStatus === 'all' ? 'active' : ''; ?>">All Statuses</a>
                <?php foreach ($statusTypes as $status): ?>
                <a href="?type=<?php echo urlencode($filterType); ?>&amp;status=<?php echo urlencode($status); ?>&amp;search=<?php echo urlencode($searchTerm); ?>" class="<?php echo $filterStatus === $status ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="text" name="search" class="search-input" placeholder="Search assets or services..." value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="filter-btn" style="background: var(--primary-color); color: white;">
            <i class="fas fa-search"></i>
            <span>Search</span>
        </button>
        <button type="button" class="filter-btn" onclick="window.location.href='facilities.php'">
            <i class="fas fa-redo"></i>
            <span>Reset</span>
        </button>
    </form>
</div>

<div class="widget-grid">
    <div class="widget">
        <div class="widget-title">
            <span>Total Capacity</span>
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="donut-container">
            <div class="donut-chart" style="background: conic-gradient(var(--transport-color) <?php echo $stats['capacityUsage']; ?>%, #f0f0f0 0);">
                <div class="donut-value"><?php echo $stats['capacityUsage']; ?>%</div>
            </div>
            <div class="donut-label">Resources Usage</div>
            <div class="progress-info">
                <span><?php echo $stats['activeShuttles']; ?> active</span>
                <span><?php echo $stats['totalShuttles']; ?> total</span>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title">
            <span>Active Shuttles</span>
            <i class="fas fa-bus"></i>
        </div>
        <div style="margin-top: 20px;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 15px;"><?php echo $stats['activeShuttles']; ?>/<?php echo $stats['totalShuttles']; ?></div>
            <div class="progress-container">
                <div class="progress-fill" style="width: <?php echo $stats['capacityUsage']; ?>%; background: var(--transport-color);"></div>
            </div>
            <div class="progress-info">
                <span><?php echo $stats['availableAssets']; ?> available</span>
                <span><?php echo $stats['maintenanceCount']; ?> in maintenance</span>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title">
            <span>Lounge Occupancy</span>
            <i class="fas fa-couch"></i>
        </div>
        <div class="donut-container">
            <div class="donut-chart" style="background: conic-gradient(var(--lounge-color) <?php echo $stats['loungeUsage']; ?>%, #f0f0f0 0);">
                <div class="donut-value"><?php echo $stats['loungeUsage']; ?>%</div>
            </div>
            <div class="donut-label">
                <?php echo $stats['loungeUsage'] >= 80 ? 'Busy' : ($stats['loungeUsage'] >= 50 ? 'Moderate' : 'Quiet'); ?>
            </div>
            <div class="progress-info">
                <span><?php echo $stats['loungeOccupancy']; ?> occupied</span>
                <span><?php echo $stats['loungeCapacity']; ?> capacity</span>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title">
            <span>Maintenance</span>
            <i class="fas fa-tools"></i>
        </div>
        <div class="maintenance-widget">
            <div class="maintenance-icon"><i class="fas fa-wrench"></i></div>
            <div class="maintenance-info">
                <div class="maintenance-value"><?php echo $stats['maintenanceCount']; ?> units</div>
                <div style="color: var(--secondary-color); font-size: 14px;">In Maintenance</div>
                <div style="margin-top: 10px;">
                    <div class="progress-container">
                        <div class="progress-fill" style="width: <?php echo ($stats['maintenanceCount'] / max(1, $stats['totalAssets'])) * 100; ?>%; background: var(--danger-color);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="widget-title">
    <span>Live Asset Locations</span>
    <i class="fas fa-map-marked-alt"></i>
</div>
<div class="map-container" onclick="viewLiveMap()">
    <div style="text-align: center;">
        <div style="font-size: 48px; margin-bottom: 10px;">MAP</div>
        <p>Interactive Map View</p>
        <p style="font-size: 12px; color: var(--secondary-color);">Click to view detailed map</p>

        <div class="map-grid">
            <?php foreach ($mapCells as $cell): ?>
                <?php if ($cell['type'] === 'empty'): ?>
                <div class="map-cell"></div>
                <?php else: ?>
                <div class="map-cell <?php echo $cell['type']; ?>" title="<?php echo ucfirst($cell['type']); ?> (<?php echo $cell['count']; ?>)">
                    <span style="font-size: 11px;"><?php echo htmlspecialchars($cell['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($cell['count'] > 1): ?>
                    <span style="position: absolute; top: 2px; right: 2px; font-size: 10px; background: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;">
                        <?php echo (int) $cell['count']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div style="display: flex; gap: 20px; justify-content: center; margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 5px;">
                <span class="map-cell transport" style="width: 20px; height: 20px;"></span>
                <span style="font-size: 12px;">Transport</span>
            </div>
            <div style="display: flex; align-items: center; gap: 5px;">
                <span class="map-cell staff" style="width: 20px; height: 20px;"></span>
                <span style="font-size: 12px;">Staff</span>
            </div>
            <div style="display: flex; align-items: center; gap: 5px;">
                <span class="map-cell lounge" style="width: 20px; height: 20px;"></span>
                <span style="font-size: 12px;">Lounge</span>
            </div>
        </div>
    </div>
</div>

<div class="table-section">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">
            <i class="fas fa-concierge-bell"></i>
            Ground and Terminal Services
            <small style="font-size: 14px; color: var(--secondary-color); font-weight: normal; margin-left: 10px;">
                (<?php echo $stats['activeServices']; ?> active)
            </small>
        </h2>
        <div class="table-controls">
            <button class="filter-btn" onclick="addNewService()">
                <i class="fas fa-plus"></i>
                <span>Add Service</span>
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Staff On-Duty</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($services) > 0): ?>
                    <?php foreach (array_slice($services, 0, 8) as $service): ?>
                    <?php $badgeClass = 'badge-' . $service['status']['color']; ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($service['customerName'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br>
                            <small class="text-muted">Started: <?php echo htmlspecialchars($service['startTime'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($service['service'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($service['staffOnDuty'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                <?php echo htmlspecialchars($service['status']['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($service['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="priority-badge priority-<?php echo strtolower(htmlspecialchars($service['priority'], ENT_QUOTES, 'UTF-8')); ?>">
                                <?php echo htmlspecialchars($service['priority'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" onclick="viewService(<?php echo (int) $service['id']; ?>)"><i class="fas fa-eye"></i> View</button>
                                <button class="btn-track" onclick="updateService(<?php echo (int) $service['id']; ?>)"><i class="fas fa-edit"></i> Update</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--secondary-color);">
                        <i class="fas fa-concierge-bell fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h4>No services found</h4>
                        <p>Try changing your search criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-section">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">
            <i class="fas fa-boxes"></i>
            Asset Management
            <small style="font-size: 14px; color: var(--secondary-color); font-weight: normal; margin-left: 10px;">
                (<?php echo count($assets); ?> assets)
            </small>
        </h2>
        <div class="table-controls">
            <button class="filter-btn" onclick="addNewAsset()">
                <i class="fas fa-plus"></i>
                <span>Add Asset</span>
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Category</th>
                    <th>Capacity</th>
                    <th>Live Location</th>
                    <th>Status</th>
                    <th>Maintenance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($assets) > 0): ?>
                    <?php foreach (array_slice($assets, 0, 10) as $asset): ?>
                    <?php
                    $badgeClass = 'badge-' . $asset['status']['color'];
                    $typeClass = 'type-' . strtolower($asset['type']);
                    $capacityClass = $asset['usagePercent'] >= 70 ? 'capacity-high' : ($asset['usagePercent'] >= 30 ? 'capacity-medium' : 'capacity-low');
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($asset['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <br>
                            <small class="text-muted">ID: <?php echo str_pad((string) $asset['id'], 3, '0', STR_PAD_LEFT); ?></small>
                        </td>
                        <td><span class="asset-type <?php echo $typeClass; ?>"><?php echo htmlspecialchars($asset['type'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td>
                            <div class="capacity-indicator">
                                <span><?php echo (int) $asset['current']; ?>/<?php echo (int) $asset['capacity']; ?></span>
                                <div class="capacity-bar">
                                    <div class="capacity-fill <?php echo $capacityClass; ?>" style="width: <?php echo (float) $asset['usagePercent']; ?>%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($asset['location'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($asset['fuelLevel'] !== null): ?>
                            <br>
                            <small class="text-muted">Fuel: <?php echo (int) $asset['fuelLevel']; ?>%</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($asset['status']['icon'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php echo htmlspecialchars($asset['status']['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($asset['status']['name'] === 'Maintenance'): ?>
                            <span class="text-danger">Due: <?php echo date('M d', strtotime($asset['nextMaintenance'])); ?></span>
                            <?php else: ?>
                            <span class="text-muted">Next: <?php echo date('M d', strtotime($asset['nextMaintenance'])); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" onclick="viewAsset(<?php echo (int) $asset['id']; ?>)"><i class="fas fa-eye"></i> View</button>
                                <?php if ($asset['status']['name'] !== 'Maintenance'): ?>
                                <button class="btn-track" onclick="trackAsset(<?php echo (int) $asset['id']; ?>)"><i class="fas fa-map-marker-alt"></i> Track</button>
                                <?php else: ?>
                                <button class="btn-maintenance" onclick="updateMaintenance(<?php echo (int) $asset['id']; ?>)"><i class="fas fa-tools"></i> Repair</button>
                                <?php endif; ?>
                                <?php if ($asset['type'] === 'Lounge' && $asset['status']['name'] !== 'Occupied'): ?>
                                <button class="btn-reserve" onclick="reserveAsset(<?php echo (int) $asset['id']; ?>)"><i class="fas fa-calendar-plus"></i> Reserve</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--secondary-color);">
                        <i class="fas fa-boxes fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h4>No assets found</h4>
                        <p>Try changing your filter criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
        <div style="color: var(--secondary-color); font-size: 14px;">
            Showing <?php echo min(10, count($assets)); ?> of <?php echo count($assets); ?> assets
        </div>
        <div>
            <button class="filter-btn" onclick="exportAssets()">
                <i class="fas fa-download"></i>
                <span>Export Assets</span>
            </button>
            <button class="filter-btn" onclick="scheduleMaintenance()">
                <i class="fas fa-calendar-check"></i>
                <span>Schedule Maintenance</span>
            </button>
        </div>
    </div>
</div>

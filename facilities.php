<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "Facilities Reservation";
$pageSubtitle = "Manage and monitor all company owned assets";

// Generate random facility data
function generateRandomAsset($index) {
    $assetTypes = [
        ['type' => 'Transport', 'subtypes' => ['Shuttle Van', 'Shuttle Bus', 'Luxury Van', 'Executive Car']],
        ['type' => 'Lounge', 'subtypes' => ['VIP Lounge', 'Business Lounge', 'Economy Lounge', 'Conference Room']],
        ['type' => 'Equipment', 'subtypes' => ['Luggage Cart', 'Check-in Counter', 'Security Scanner', 'Waiting Chair']],
        ['type' => 'Staff', 'subtypes' => ['Driver', 'Porter', 'Receptionist', 'Security']]
    ];
    
    $locations = [
        'NAIA Terminal 1', 'NAIA Terminal 2', 'NAIA Terminal 3', 'Clark International Airport',
        'Mactan-Cebu Airport', 'Davao Airport', 'Makati Office', 'BGC Office', 'Quezon City Office',
        'Caloocan City', 'Pasay City', 'Parañaque City', 'Taguig City'
    ];
    
    $statuses = [
        ['name' => 'Active', 'color' => 'green', 'icon' => '●'],
        ['name' => 'In Transit', 'color' => 'blue', 'icon' => '⇄'],
        ['name' => 'Loading', 'color' => 'yellow', 'icon' => '↻'],
        ['name' => 'Maintenance', 'color' => 'red', 'icon' => '🔧'],
        ['name' => 'Available', 'color' => 'green', 'icon' => '✓'],
        ['name' => 'Occupied', 'color' => 'red', 'icon' => '⛔'],
        ['name' => 'Reserved', 'color' => 'blue', 'icon' => '📅']
    ];
    
    $assetType = $assetTypes[array_rand($assetTypes)];
    $assetSubtype = $assetType['subtypes'][array_rand($assetType['subtypes'])];
    
    // Generate capacity based on type
    if ($assetType['type'] === 'Transport') {
        $capacity = rand(3, 20);
        $current = rand(0, $capacity);
    } elseif ($assetType['type'] === 'Lounge') {
        $capacity = rand(20, 100);
        $current = rand(0, $capacity);
    } else {
        $capacity = 1;
        $current = rand(0, 1);
    }
    
    // Determine status based on usage
    $usagePercent = ($current / $capacity) * 100;
    if ($assetType['type'] === 'Transport') {
        if ($usagePercent >= 90) $status = $statuses[5]; // Occupied
        elseif ($usagePercent >= 50) $status = $statuses[2]; // Loading
        elseif ($usagePercent > 0) $status = $statuses[1]; // In Transit
        elseif (rand(0, 10) === 0) $status = $statuses[3]; // Maintenance
        else $status = $statuses[0]; // Active
    } else {
        if ($usagePercent >= 90) $status = $statuses[5]; // Occupied
        elseif ($usagePercent >= 50) $status = $statuses[6]; // Reserved
        elseif ($current > 0) $status = $statuses[4]; // Available
        else $status = $statuses[0]; // Active
    }
    
    return [
        'id' => $index + 1,
        'name' => $assetSubtype . ' #' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
        'type' => $assetType['type'],
        'subtype' => $assetSubtype,
        'capacity' => $capacity,
        'current' => $current,
        'usagePercent' => $usagePercent,
        'location' => $locations[array_rand($locations)],
        'status' => $status,
        'lastMaintenance' => date('Y-m-d', strtotime('-' . rand(0, 180) . ' days')),
        'nextMaintenance' => date('Y-m-d', strtotime('+' . rand(30, 365) . ' days')),
        'assignedStaff' => rand(1, 5),
        'fuelLevel' => $assetType['type'] === 'Transport' ? rand(20, 100) : null,
        'reservations' => rand(0, 10)
    ];
}

// Generate random service data
function generateRandomService($index) {
    $services = ['Baggage Assist', 'Priority Check In', 'Meet & Greet', 'Concierge', 'Security Escort', 'Luggage Storage'];
    $staffFirstNames = ['John', 'Jane', 'Robert', 'Mary', 'Michael', 'Sarah', 'David', 'Emily', 'James', 'Jennifer'];
    $staffLastNames = ['Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor'];
    $customerFirstNames = ['Vanessa', 'Erick', 'Rens', 'Maria', 'Joseph', 'Mark', 'James', 'Sarah', 'Michael', 'Jennifer'];
    $customerLastNames = ['Radaza', 'Taguba', 'Solano', 'Alvares', 'De Guzman', 'Villotes', 'Cruz', 'Dela Cruz', 'Santos', 'Reyes'];
    
    $serviceStatuses = [
        ['name' => 'Active', 'color' => 'green'],
        ['name' => 'Ongoing', 'color' => 'yellow'],
        ['name' => 'Waiting', 'color' => 'blue'],
        ['name' => 'Completed', 'color' => 'gray']
    ];
    
    $status = $serviceStatuses[array_rand($serviceStatuses)];
    
    return [
        'id' => $index + 1,
        'customerName' => $customerFirstNames[array_rand($customerFirstNames)] . ' ' . $customerLastNames[array_rand($customerLastNames)],
        'service' => $services[array_rand($services)],
        'staffOnDuty' => $staffFirstNames[array_rand($staffFirstNames)] . ' ' . $staffLastNames[array_rand($staffLastNames)],
        'status' => $status,
        'startTime' => date('H:i', strtotime('-' . rand(0, 120) . ' minutes')),
        'duration' => rand(15, 180) . ' min',
        'location' => rand(0, 1) ? 'Terminal ' . rand(1, 3) : 'Ground Floor',
        'priority' => rand(1, 10) > 7 ? 'High' : 'Normal'
    ];
}

// Generate data
$assets = [];
for ($i = 0; $i < 25; $i++) {
    $assets[] = generateRandomAsset($i);
}

$services = [];
for ($i = 0; $i < 20; $i++) {
    $services[] = generateRandomService($i);
}

// Filter functionality
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Apply filters to assets
if ($filterType !== 'all') {
    $assets = array_filter($assets, function($asset) use ($filterType) {
        return $asset['type'] === $filterType;
    });
}

if ($filterStatus !== 'all') {
    $assets = array_filter($assets, function($asset) use ($filterStatus) {
        return $asset['status']['name'] === $filterStatus;
    });
}

if ($searchTerm) {
    $assets = array_filter($assets, function($asset) use ($searchTerm) {
        return stripos($asset['name'], $searchTerm) !== false || 
               stripos($asset['location'], $searchTerm) !== false;
    });
    
    $services = array_filter($services, function($service) use ($searchTerm) {
        return stripos($service['customerName'], $searchTerm) !== false || 
               stripos($service['service'], $searchTerm) !== false;
    });
}

// Calculate statistics
$stats = [
    'totalCapacity' => 0,
    'activeShuttles' => 0,
    'totalShuttles' => 0,
    'loungeOccupancy' => 0,
    'loungeCapacity' => 0,
    'maintenanceCount' => 0,
    'totalAssets' => count($assets),
    'availableAssets' => 0,
    'inUseAssets' => 0,
    'totalServices' => count($services),
    'activeServices' => 0
];

foreach ($assets as $asset) {
    if ($asset['type'] === 'Transport') {
        $stats['totalShuttles']++;
        if ($asset['status']['name'] !== 'Maintenance') {
            $stats['activeShuttles']++;
        }
        $stats['totalCapacity'] += $asset['capacity'];
    }
    
    if ($asset['type'] === 'Lounge') {
        $stats['loungeCapacity'] += $asset['capacity'];
        $stats['loungeOccupancy'] += $asset['current'];
    }
    
    if ($asset['status']['name'] === 'Maintenance') {
        $stats['maintenanceCount']++;
    }
    
    if ($asset['current'] > 0) {
        $stats['inUseAssets']++;
    } else {
        $stats['availableAssets']++;
    }
}

foreach ($services as $service) {
    if ($service['status']['name'] === 'Active' || $service['status']['name'] === 'Ongoing') {
        $stats['activeServices']++;
    }
}

// Calculate percentages
$stats['capacityUsage'] = $stats['totalShuttles'] > 0 ? 
    round(($stats['activeShuttles'] / $stats['totalShuttles']) * 100) : 0;
    
$stats['loungeUsage'] = $stats['loungeCapacity'] > 0 ? 
    round(($stats['loungeOccupancy'] / $stats['loungeCapacity']) * 100) : 0;

// Get unique asset types for filter
$assetTypes = array_unique(array_column($assets, 'type'));
$statusTypes = array_unique(array_map(function($asset) {
    return $asset['status']['name'];
}, $assets));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Beyond The Map</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Your custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/facilities.css">
</head>
<body>
    
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content Wrapper -->
    <div id="content-wrapper">
        <div class="content-container">
            <!-- Filter Controls -->
            <div class="widget" style="margin-bottom: 30px;">
                
                <form method="GET" action="" class="d-flex gap-2" style="flex-wrap: wrap;">
                    <div class="filter-dropdown">
                        <button type="button" class="filter-btn">
                            <i class="fas fa-layer-group"></i>
                            <span>Type: <?php echo $filterType === 'all' ? 'All Types' : $filterType; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="filter-content">
                            <a href="?type=all&status=<?php echo $filterStatus; ?>&search=<?php echo urlencode($searchTerm); ?>" 
                               class="<?php echo $filterType === 'all' ? 'active' : ''; ?>">All Types</a>
                            <?php foreach ($assetTypes as $type): ?>
                                <a href="?type=<?php echo urlencode($type); ?>&status=<?php echo $filterStatus; ?>&search=<?php echo urlencode($searchTerm); ?>" 
                                   class="<?php echo $filterType === $type ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($type); ?>
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
                            <a href="?type=<?php echo $filterType; ?>&status=all&search=<?php echo urlencode($searchTerm); ?>" 
                               class="<?php echo $filterStatus === 'all' ? 'active' : ''; ?>">All Statuses</a>
                            <?php foreach ($statusTypes as $status): ?>
                                <a href="?type=<?php echo $filterType; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($searchTerm); ?>" 
                                   class="<?php echo $filterStatus === $status ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="text" name="search" class="search-input" placeholder="Search assets or services..." value="<?php echo htmlspecialchars($searchTerm); ?>">
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

            <!-- Key Metrics -->
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
                        <div style="font-size: 32px; font-weight: bold; margin-bottom: 15px;">
                            <?php echo $stats['activeShuttles']; ?>/<?php echo $stats['totalShuttles']; ?>
                        </div>
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
                        <div class="maintenance-icon">
                            <i class="fas fa-wrench"></i>
                        </div>
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

            <!-- Live Location Section -->
            
                <div class="widget-title">
                    <span>Live Asset Locations</span>
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="map-container" onclick="viewLiveMap()">
                    <div style="text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 10px;">🗺️</div>
                        <p>Interactive Map View</p>
                        <p style="font-size: 12px; color: var(--secondary-color);">Click to view detailed map</p>
                        
                        <div class="map-grid">
                            <?php
                            // Generate random map cells
                            $mapCells = [];
                            for ($i = 0; $i < 16; $i++) {
                                $rand = rand(1, 10);
                                if ($rand <= 3) {
                                    $mapCells[] = ['type' => 'transport', 'icon' => '🚗', 'count' => rand(1, 3)];
                                } elseif ($rand <= 6) {
                                    $mapCells[] = ['type' => 'staff', 'icon' => '👤', 'count' => rand(1, 5)];
                                } elseif ($rand <= 8) {
                                    $mapCells[] = ['type' => 'lounge', 'icon' => '🛋️', 'count' => rand(1, 2)];
                                } else {
                                    $mapCells[] = ['type' => 'empty', 'icon' => '', 'count' => 0];
                                }
                            }
                            
                            foreach ($mapCells as $cell):
                                if ($cell['type'] === 'empty'): ?>
                                    <div class="map-cell"></div>
                                <?php else: ?>
                                    <div class="map-cell <?php echo $cell['type']; ?>" title="<?php echo ucfirst($cell['type']); ?> (<?php echo $cell['count']; ?>)">
                                        <span style="font-size: 20px;"><?php echo $cell['icon']; ?></span>
                                        <?php if ($cell['count'] > 1): ?>
                                            <span style="position: absolute; top: 2px; right: 2px; font-size: 10px; background: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;">
                                                <?php echo $cell['count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif;
                            endforeach; ?>
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
           

            <!-- Ground and Terminal Service -->
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
                                <?php foreach (array_slice($services, 0, 8) as $service): 
                                    $badgeClass = 'badge-' . $service['status']['color'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $service['customerName']; ?></strong>
                                        <br>
                                        <small class="text-muted">Started: <?php echo $service['startTime']; ?></small>
                                    </td>
                                    <td><?php echo $service['service']; ?></td>
                                    <td><?php echo $service['staffOnDuty']; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                                            <?php echo $service['status']['name']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $service['location']; ?></td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo strtolower($service['priority']); ?>">
                                            <?php echo $service['priority']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewService(<?php echo $service['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn-track" onclick="updateService(<?php echo $service['id']; ?>)">
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
                                        <h4>No services found</h4>
                                        <p>Try changing your search criteria.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Asset Management -->
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
                                <?php foreach (array_slice($assets, 0, 10) as $asset): 
                                    $badgeClass = 'badge-' . $asset['status']['color'];
                                    $typeClass = 'type-' . strtolower($asset['type']);
                                    $capacityClass = $asset['usagePercent'] >= 70 ? 'capacity-high' : ($asset['usagePercent'] >= 30 ? 'capacity-medium' : 'capacity-low');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $asset['name']; ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?php echo str_pad($asset['id'], 3, '0', STR_PAD_LEFT); ?></small>
                                    </td>
                                    <td>
                                        <span class="asset-type <?php echo $typeClass; ?>">
                                            <?php echo $asset['type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="capacity-indicator">
                                            <span><?php echo $asset['current']; ?>/<?php echo $asset['capacity']; ?></span>
                                            <div class="capacity-bar">
                                                <div class="capacity-fill <?php echo $capacityClass; ?>" style="width: <?php echo $asset['usagePercent']; ?>%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $asset['location']; ?>
                                        <?php if ($asset['fuelLevel'] !== null): ?>
                                            <br>
                                            <small class="text-muted">Fuel: <?php echo $asset['fuelLevel']; ?>%</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo $asset['status']['icon']; ?>
                                            <?php echo $asset['status']['name']; ?>
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
                                            <button class="btn-view" onclick="viewAsset(<?php echo $asset['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if ($asset['status']['name'] !== 'Maintenance'): ?>
                                                <button class="btn-track" onclick="trackAsset(<?php echo $asset['id']; ?>)">
                                                    <i class="fas fa-map-marker-alt"></i> Track
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-maintenance" onclick="updateMaintenance(<?php echo $asset['id']; ?>)">
                                                    <i class="fas fa-tools"></i> Repair
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($asset['type'] === 'Lounge' && $asset['status']['name'] !== 'Occupied'): ?>
                                                <button class="btn-reserve" onclick="reserveAsset(<?php echo $asset['id']; ?>)">
                                                    <i class="fas fa-calendar-plus"></i> Reserve
                                                </button>
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
                
                <!-- Summary -->
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
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Your custom JS -->
    <script src="js/main.js"></script>
    <script src="js/facilities.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "Account Executive";
$pageSubtitle = "Handles Customer Processing";

function normalizeValue($value) {
    return strtolower(trim((string) $value));
}

function paymentBadgeClass($paymentStatus) {
    return match (normalizeValue($paymentStatus)) {
        'paid' => 'bg-success',
        'unpaid', 'overdue' => 'bg-danger',
        'partially paid' => 'bg-warning text-dark',
        default => 'bg-secondary'
    };
}

function statusBadgeClass($status) {
    return match (normalizeValue($status)) {
        'pending' => 'bg-warning text-dark',
        'processing' => 'bg-primary',
        'cancelled' => 'bg-danger',
        'finished' => 'bg-success',
        default => 'bg-secondary'
    };
}

/* ---------------- MOCK DATA ---------------- */
function generateCustomer($i) {
    $names = ['Robert Brown','Emily Davis','Jane Doe','Sarah Johnson','John Smith'];
    $destinations = ['USA','France','Canada','Australia','Japan','UK'];
    $payments = ['Paid','Unpaid','Overdue','Partially Paid'];
    $statuses = ['Processing','Pending','Cancelled','Finished'];
    $createdDaysAgo = rand(0, 60);
    $lastContactedDaysAgo = rand(0, 14);
    $createdDate = date('Y-m-d', strtotime("-{$createdDaysAgo} days"));
    $lastContactedDate = date('Y-m-d H:i:s', strtotime("-{$lastContactedDaysAgo} days -" . rand(0, 23) . " hours -" . rand(0, 59) . " minutes"));

    return [
        'id' => $i + 1,
        'name' => $names[array_rand($names)],
        'destination' => $destinations[array_rand($destinations)],
        'lastContacted' => date('m/d/Y - h:i a', strtotime($lastContactedDate)),
        'lastContactedDate' => date('Y-m-d', strtotime($lastContactedDate)),
        'createdDate' => $createdDate,
        'paymentStatus' => $payments[array_rand($payments)],
        'progress' => rand(10,100),
        'status' => $statuses[array_rand($statuses)],
        'admissionStatus' => rand(0,1) ? 'Admitted' : 'Pending',
        'refund' => rand(1, 10) > 7 ? 'true' : 'false'
    ];
}

$customers = [];
for ($i=0; $i<20; $i++) $customers[] = generateCustomer($i);

// Sort customers alphabetically by name
usort($customers, function($a, $b){
    return strcmp($a['name'], $b['name']);
});

/* ---------------- STATS ---------------- */
$stats = ['paid'=>0,'admitted'=>0,'pending'=>0,'unpaid'=>0];
foreach ($customers as $c) {
    if ($c['paymentStatus']==='Paid') $stats['paid']++;
    if ($c['admissionStatus']==='Admitted') $stats['admitted']++;
    if ($c['admissionStatus']==='Pending') $stats['pending']++;
    if ($c['paymentStatus']!=='Paid') $stats['unpaid']++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/account-executive.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<div id="content-wrapper">
    <div class="content-container">

        <div class="section-header">
            <h2 class="section-title">Customer List</h2>

            <!-- CONTROLS -->
            <div class="table-controls">
                <div class="left-controls">
                    <input type="text" class="form-control search-input"
                        placeholder="Search name or destination...">
                    <select class="form-select filter-select">
                        <option>All</option>
                        <option>Paid</option>
                        <option>Unpaid</option>
                        <option>Overdue</option>
                        <option>Partially Paid</option>
                    </select>
                </div>
                <button id="sendPaymentReminderBtn" class="btn btn-warning">
                    <i class="fa fa-bell"></i> Send Reminder
                </button>
            </div>

        </div>


        <div class="overview-cards">
            <div class="overview-card"><div class="icon-box"><i class="fa fa-credit-card"></i></div><div><small>Paid Customers</small><h4><?= $stats['paid'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-user-check"></i></div><div><small>Customer Admitted</small><h4><?= $stats['admitted'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Pending Admission</small><h4><?= $stats['pending'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-exclamation-circle"></i></div><div><small>Unpaid Customers</small><h4><?= $stats['unpaid'] ?></h4></div></div>
        </div>

        <div class="table-section">

                <ul class="nav nav-tabs mb-3" id="aeStatusTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#" data-filter="all" role="tab" aria-selected="true">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="new" role="tab" aria-selected="false">New</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="for-follow-up" role="tab" aria-selected="false">For Follow-up</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="ongoing" role="tab" aria-selected="false">Ongoing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="payment-issues" role="tab" aria-selected="false">Payment Issues</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="finished" role="tab" aria-selected="false">Finished</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="refund" role="tab" aria-selected="false">Refund</a>
                    </li>
                </ul>

                <!-- TABLE -->
            <div class="table-scroll">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Destination</th>
                            <th>Last Contacted</th>
                            <th>
                                <div class="dropdown payment-header-dropdown">
                                    <button class="btn btn-link dropdown-toggle" type="button" id="paymentHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Payment
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="paymentHeaderDropdown">
                                        <li>
                                            <a class="dropdown-item" href="#" data-payment="all">
                                                All Payments
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-payment="Paid">
                                                <span class="badge-dot bg-success me-2 ms-2"></span> Paid
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-payment="Unpaid">
                                                <span class="badge-dot bg-danger me-2 ms-2"></span> Unpaid
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-payment="Overdue">
                                                <span class="badge-dot bg-danger me-2 ms-2" style="background: #dc3545 !important;"></span> Overdue
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-payment="Partially Paid">
                                                <span class="badge-dot bg-warning me-2 ms-2"></span> Partially Paid
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                            <th>Progress</th>
                            <th>
                                <div class="dropdown status-header-dropdown">
                                    <button class="btn btn-link dropdown-toggle" type="button" id="statusHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="statusHeaderDropdown">
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="all">All Status</a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="Finished">
                                                <span class="badge-dot bg-success me-2 ms-2"></span> Finished
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="Processing">
                                                <span class="badge-dot bg-info me-2 ms-2"></span> Processing
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="Pending">
                                                <span class="badge-dot bg-warning me-2 ms-2"></span> Pending
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-status="Cancelled">
                                                <span class="badge-dot bg-danger me-2 ms-2"></span> Cancelled
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($customers as $c): ?>
                        <tr
                            data-payment="<?= htmlspecialchars(normalizeValue($c['paymentStatus']), ENT_QUOTES, 'UTF-8') ?>"
                            data-status="<?= htmlspecialchars(normalizeValue($c['status']), ENT_QUOTES, 'UTF-8') ?>"
                            data-progress="<?= (int) $c['progress'] ?>"
                            data-date="<?= $c['createdDate'] ?>"
                            data-last-contacted="<?= $c['lastContactedDate'] ?>"
                            data-refund="<?= $c['refund'] ?>"
                        >
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>#<?= str_pad($c['id'],3,'0',STR_PAD_LEFT) ?></td>
                            <td><?= $c['name'] ?></td>
                            <td><?= $c['destination'] ?></td>
                            <td><?= $c['lastContacted'] ?></td>
                            <td><span class="badge rounded-pill <?= paymentBadgeClass($c['paymentStatus']) ?>"><?= $c['paymentStatus'] ?></span></td>
                            <td>
                                <?= $c['progress'] ?>%
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width:<?= $c['progress'] ?>%"></div>
                                </div>
                            </td>
                            <td><span class="badge rounded-pill <?= statusBadgeClass($c['status']) ?>"><?= $c['status'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-sm btn-success"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- FOOTER: entries info + pagination -->
            <div class="table-footer d-flex justify-content-between align-items-center">
                <div class="entries-info">Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?= count($customers) ?></span> entries</div>
                                <div class="pagination-wrap">
                                        <nav aria-label="...">
                                            <ul class="pagination mb-0" id="tablePagination">
                                                <li class="page-item disabled">
                                                    <a class="page-link">Previous</a>
                                                </li>
                                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#" aria-current="page">2</a>
                                                </li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                </div>
            </div>


        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script src="js/account-executive.js"></script>

</body>
</html>

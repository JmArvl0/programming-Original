<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "CRM";
$pageSubtitle = "View and Manage Operational Data";

/* ===== MOCK DATA ===== */
$stats = [
    'total' => 50,
    'activePercent' => 62,
    'active' => 31,
    'vip' => 14,
    'gold' => 16,
    'silver' => 13,
    'new' => 7,
    'rating' => 5.8,
    'revenue' => 5571161,
    'avgLifetime' => 111423,
    'avgTrips' => 12.5,
    'followups' => 32
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $pageTitle ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/crm.css">
</head>

<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<div id="content-wrapper">
    <div class="content-container">

<!-- ===== KPI CARDS ===== -->

<h2 class="crm-section-title">Client Overview</h2>

<div class="crm-grid">

<div class="crm-card">
    <div class="crm-title">Total Customers <i class="fa fa-users"></i></div>
    <div class="crm-value"><?= $stats['total'] ?></div>
    <div class="crm-sub">Active Customer Base</div>
    <div class="crm-progress"><div class="crm-bar bar-vip" style="width:100%"></div></div>
</div>

<div class="crm-card">
    <div class="crm-title">Active Travelers <i class="fa fa-plane"></i></div>
    <div class="crm-value" style="color:var(--green)"><?= $stats['activePercent'] ?>%</div>
    <div class="crm-sub"><?= $stats['active'] ?> active customers</div>
    <div class="crm-progress"><div class="crm-bar bar-gold" style="width:<?= $stats['activePercent'] ?>%"></div></div>
</div>

<div class="crm-card">
    <div class="crm-title">Tier Distribution <i class="fa fa-layer-group"></i></div>
    <div class="tier-strip">
        <div style="width:28%;background:var(--vip)"></div>
        <div style="width:32%;background:var(--gold)"></div>
        <div style="width:26%;background:var(--silver)"></div>
        <div style="width:14%;background:var(--new)"></div>
    </div>
    <small class="tier-legend">
        <span class="tier-legend-item tier-legend-vip"><span class="tier-legend-icon">&#9733;</span> VIP <?= ['vip'] ?></span> ·
        <span class="tier-legend-item tier-legend-gold"><span class="tier-legend-icon">&#9670;</span> Gold <?= ['gold'] ?></span> ·
        <span class="tier-legend-item tier-legend-silver"><span class="tier-legend-icon">&#9679;</span> Silver <?= ['silver'] ?></span> ·
        <span class="tier-legend-item tier-legend-new"><span class="tier-legend-icon">&#10148;</span> New <?= ['new'] ?></span>
    </small>
</div>

<div class="crm-card feedback-card">
    <div class="crm-title">Customer Feedback <i class="fa fa-star"></i></div>
    <div class="crm-value"><?= $stats['rating'] ?></div>
    â­â­â­â­â­
    <div class="crm-progress">
        <div class="crm-bar bar-silver" style="width:<?= ($stats['rating']/5)*100 ?>%"></div>
    </div>
</div>

</div>

<!-- ===== CRM TABLE ===== -->
<h2 class="crm-section-title">Customer Records</h2>

<div class="crm-table-wrapper">

    <div class="crm-table-toolbar">
        <div class="crm-toolbar-left">
            <input class="crm-search" placeholder="Search name, destination or emailâ€¦">
            <select class="crm-filter">
                <option>All Tiers</option>
                <option>VIP</option>
                <option>Gold</option>
                <option>Silver</option>
                <option>New</option>
            </select>
        </div>

        <div class="crm-toolbar-right">
            <button class="crm-btn crm-btn-news">
                <i class="fa fa-envelope"></i> Send Newsletter
            </button>
            <button class="crm-btn crm-btn-export">
                <i class="fa fa-file-export"></i> Export Report
            </button>
        </div>
    </div>

    <div class="crm-table-scroll">
        <table class="table crm-table table-hover mb-0">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>Name</th>
                    <th>Loyalty Tier</th>
                    <th>Lifetime Value</th>
                    <th>Total Trips</th>
                    <th>Last Contacted</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $tiers = ['VIP','Gold','Silver','New'];
                $tierClasses = [
                    'VIP' => 'badge-vip',
                    'Gold' => 'badge-gold',
                    'Silver' => 'badge-silver',
                    'New' => 'badge-new'
                ];
                $tierIcons = [
                    'VIP' => '&#9733;',
                    'Gold' => '&#9670;',
                    'Silver' => '&#9679;',
                    'New' => '&#10148;'
                ];
                ?>

                <?php for($i=1;$i<=30;$i++): 
                    $tier = $tiers[array_rand($tiers)];
                ?>
                <tr>
                    <td><input type="checkbox"></td>

                    <td>
                        <strong>Customer <?= $i ?></strong><br>
                        <small class="text-muted">customer<?= $i ?>@mail.com</small>
                    </td>

                    <td>
                        <span class="badge <?= $tierClasses[$tier] ?> loyalty-tier-badge">
                            <span class="tier-badge-icon tier-badge-icon-<?= strtolower($tier) ?>"><?= $tierIcons[$tier] ?? '&#9679;' ?></span>
                            <?= $tier ?>
                        </span>
                    </td>

                    <td>
                        â‚±<?= number_format(rand(50000,500000), 0) ?>
                    </td>

                    <td>
                        <?= rand(1,25) ?>
                    </td>

                    <td>
                        <?= rand(1,30) ?> days ago
                    </td>

                    <td>
                        <div class="crm-actions">
                            <button class="btn-icon btn-view" title="View Profile">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button class="btn-icon btn-contact" title="Contact">
                                <i class="fa fa-phone"></i>
                            </button>
                            <button class="btn-icon btn-notes" title="Notes">
                                <i class="fa fa-sticky-note"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endfor; ?>
                </tbody>

        </table>
    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>


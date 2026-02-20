<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = "Passport & Visa";
$pageSubtitle = "Manage Passport & Visa Processing";

function normalizeStatus($value) {
    return strtolower(trim((string) $value));
}

function badgeClassForStatus($value) {
    $status = normalizeStatus($value);
    $map = [
        'approved' => 'bg-success',
        'submitted' => 'bg-primary',
        'processing' => 'bg-info text-dark',
        'under review' => 'bg-warning text-dark',
        'missing' => 'bg-warning text-dark',
        'pending' => 'bg-secondary',
        'rejected' => 'bg-danger',
        'action required' => 'bg-danger',
        'not started' => 'bg-light text-dark border',
        'visa issued' => 'bg-success'
    ];

    return $map[$status] ?? 'bg-secondary';
}

/* ================= MOCK DATA ================= */
function generateApplicant($i) {
    $first = ['Manuel','Jose','Manny','Rose Ann','John Mark','Judy Ann','Maria','James','Mark','Sarah'];
    $last  = ['Cruz','Dela Cruz','Padilla','Pacquiao','Smith','Johnson','Garcia','Miller','Davis','Martinez'];
    $countries = ['Philippines','USA','Japan','Canada','Australia','UK','France','Germany'];

    $passport = [
        ['status'=>'green','prefix'=>'DAS','desc'=>'Valid'],
        ['status'=>'gray','prefix'=>'IN','desc'=>'Processing'],
        ['status'=>'yellow','prefix'=>'REV','desc'=>'Under Review'],
        ['status'=>'red','prefix'=>'EXP','desc'=>'Expired']
    ];

    $documents = [
        ['status'=>'green','text'=>'Approved','desc'=>'All documents approved'],
        ['status'=>'blue','text'=>'Submitted','desc'=>'Documents submitted'],
        ['status'=>'yellow','text'=>'Missing','desc'=>'Missing documents'],
        ['status'=>'red','text'=>'Rejected','desc'=>'Rejected'],
        ['status'=>'gray','text'=>'Not Started','desc'=>'No upload yet']
    ];

    $applications = [
        ['status'=>'green','text'=>'Visa Issued','desc'=>'Approved'],
        ['status'=>'green','text'=>'Approved','desc'=>'Approved'],
        ['status'=>'blue','text'=>'Processing','desc'=>'In process'],
        ['status'=>'yellow','text'=>'Under Review','desc'=>'Under review'],
        ['status'=>'yellow','text'=>'Pending','desc'=>'Under review'],
        ['status'=>'red','text'=>'Action Required','desc'=>'Action needed'],
        ['status'=>'gray','text'=>'Not Started','desc'=>'Not started']
    ];

    $p = $passport[array_rand($passport)];
    $d = $documents[array_rand($documents)];
    $a = $applications[array_rand($applications)];

    return [
        'id'=>$i+1,
        'name'=>$first[array_rand($first)].' '.$last[array_rand($last)],
        'passport'=>[
            'number'=>$p['prefix'].rand(1000,9999).chr(rand(65,90)),
            'status'=>$p['status'],
            'desc'=>$p['desc']
        ],
        'country'=>$countries[array_rand($countries)],
        'documents'=>$d,
        'application'=>$a,
        'submissionDate'=>date('m/d/Y',strtotime('-'.rand(0,60).' days')),
        'priority'=>rand(1,10)>7?'High':(rand(1,10)>4?'Medium':'Low')
    ];
}

$applicants=[];
for($i=0;$i<15;$i++) $applicants[]=generateApplicant($i);

/* ================= STATS ================= */
$stats=['total'=>count($applicants),'approved'=>0,'review'=>0,'new'=>0];
foreach($applicants as $a){
    if($a['application']['status']==='green')$stats['approved']++;
    if(in_array($a['application']['status'],['yellow','blue']))$stats['review']++;
    if(strtotime($a['submissionDate'])>strtotime('-7 days'))$stats['new']++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?> - Beyond The Map</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/passport-visa.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<div id="content-wrapper">
    <div class="content-container">

        <div class="section-header">
            <h2 class="section-title">Applicant List</h2>

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
            <div class="overview-card"><div class="icon-box"><i class="fa fa-users"></i></div><div><small>Total</small><h4><?= $stats['total'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-check-circle"></i></div><div><small>Approved</small><h4><?= $stats['approved'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Under Review</small><h4><?= $stats['review'] ?></h4></div></div>
            <div class="overview-card"><div class="icon-box"><i class="fa fa-user-plus"></i></div><div><small>New (7 days)</small><h4><?= $stats['new'] ?></h4></div></div>
        </div>

        <div class="table-section">
            <ul class="nav nav-tabs filter-tabs mb-3" id="applicantFilterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" type="button" data-filter="all" role="tab" aria-selected="true">All</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="new" role="tab" aria-selected="false">New</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="documents-issue" role="tab" aria-selected="false">Documents Issue</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="under-processing" role="tab" aria-selected="false">Under Processing</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="for-action" role="tab" aria-selected="false">For Action</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="approved" role="tab" aria-selected="false">Approved</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" type="button" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                </li>
            </ul>

            <div class="table-responsive table-scroll">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Passport</th>
                            <th>Country</th>
                            <th>Documents</th>
                            <th>Application</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($applicants as $a): ?>
                            <tr
                                data-documents="<?= htmlspecialchars(normalizeStatus($a['documents']['text']), ENT_QUOTES, 'UTF-8') ?>"
                                data-application="<?= htmlspecialchars(normalizeStatus($a['application']['text']), ENT_QUOTES, 'UTF-8') ?>"
                                data-date="<?= date('Y-m-d', strtotime($a['submissionDate'])) ?>"
                            >
                                <td><input type="checkbox" class="row-checkbox"></td>
                                <td>#<?= str_pad($a['id'],3,'0',STR_PAD_LEFT) ?></td>
                                <td><strong><?= $a['name'] ?></strong></td>
                                <td><span class="status-dot status-<?= $a['passport']['status'] ?>"></span> <?= $a['passport']['number'] ?></td>
                                <td><?= $a['country'] ?></td>
                                <td><span class="badge rounded-pill <?= badgeClassForStatus($a['documents']['text']) ?>"><?= $a['documents']['text'] ?></span></td>
                                <td><span class="badge rounded-pill <?= badgeClassForStatus($a['application']['text']) ?>"><?= $a['application']['text'] ?></span></td>
                                <td class="priority-<?= strtolower($a['priority']) ?>"><?= $a['priority'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button>
                                    <button class="btn btn-sm btn-success"><i class="fa fa-upload"></i></button>
                                    <button class="btn btn-sm btn-info"><i class="fa fa-search"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- FOOTER: entries info + pagination -->
            <div class="table-footer d-flex justify-content-between align-items-center">
                <div class="entries-info">Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?= count($applicants) ?></span> entries</div>
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
<script src="js/passport-visa.js"></script>

</body>
</html>

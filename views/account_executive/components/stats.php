<div class="overview-cards">
    <div class="overview-card"><div class="icon-box"><i class="fa fa-credit-card"></i></div><div><small>Paid Customers</small><h4><?= (int) $stats['paid'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-user-check"></i></div><div><small>Customer Admitted</small><h4><?= (int) $stats['admitted'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Pending Admission</small><h4><?= (int) $stats['pending'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-exclamation-circle"></i></div><div><small>Unpaid Customers</small><h4><?= (int) $stats['unpaid'] ?></h4></div></div>
</div>

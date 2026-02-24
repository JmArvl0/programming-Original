<div class="overview-cards">
    <div class="overview-card"><div class="icon-box"><i class="fa fa-users"></i></div><div><small>Total</small><h4><?= (int) $stats['total'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-check-circle"></i></div><div><small>Approved</small><h4><?= (int) $stats['approved'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Under Review</small><h4><?= (int) $stats['review'] ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-user-plus"></i></div><div><small>New (7 days)</small><h4><?= (int) $stats['new'] ?></h4></div></div>
</div>

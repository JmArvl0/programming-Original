<div class="crm-grid">
    <div class="crm-card">
        <div class="crm-title">Total Customers <i class="fa fa-users"></i></div>
        <div class="crm-value"><?= (int) $stats['total'] ?></div>
        <div class="crm-sub">Active Customer Base</div>
        <div class="crm-progress"><div class="crm-bar bar-vip" style="width:100%"></div></div>
    </div>

    <div class="crm-card">
        <div class="crm-title">Active Travelers <i class="fa fa-plane"></i></div>
        <div class="crm-value" style="color:var(--green)"><?= (int) $stats['activePercent'] ?>%</div>
        <div class="crm-sub"><?= (int) $stats['active'] ?> active customers</div>
        <div class="crm-progress"><div class="crm-bar bar-gold" style="width:<?= (float) $stats['activePercent'] ?>%"></div></div>
    </div>

    <div class="crm-card">
        <div class="crm-title">Tier Distribution <i class="fa fa-layer-group"></i></div>
        <div class="tier-strip">
            <div style="width:<?= (float) $stats['vipPercent'] ?>%;background:var(--vip)"></div>
            <div style="width:<?= (float) $stats['goldPercent'] ?>%;background:var(--gold)"></div>
            <div style="width:<?= (float) $stats['silverPercent'] ?>%;background:var(--silver)"></div>
            <div style="width:<?= (float) $stats['newPercent'] ?>%;background:var(--new)"></div>
        </div>
        <small class="tier-legend">
            <span class="tier-legend-item tier-legend-vip"><span class="tier-legend-icon">&#9733;</span> VIP <?= (int) $stats['vip'] ?></span> &middot;
            <span class="tier-legend-item tier-legend-gold"><span class="tier-legend-icon">&#9670;</span> Gold <?= (int) $stats['gold'] ?></span> &middot;
            <span class="tier-legend-item tier-legend-silver"><span class="tier-legend-icon">&#9679;</span> Silver <?= (int) $stats['silver'] ?></span> &middot;
            <span class="tier-legend-item tier-legend-new"><span class="tier-legend-icon">&#10148;</span> New <?= (int) $stats['new'] ?></span>
        </small>
    </div>

    <div class="crm-card feedback-card">
        <div class="crm-title">Customer Feedback <i class="fa fa-star"></i></div>
        <div class="crm-value"><?= number_format((float) $stats['rating'], 1) ?></div>
        *****
        <div class="crm-progress">
            <div class="crm-bar bar-silver" style="width:<?= ((float) $stats['rating'] / 5) * 100 ?>%"></div>
        </div>
    </div>
</div>

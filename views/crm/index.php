<div class="section-header">
    <h2 class="crm-section-title">Client Overview</h2>
    <div class="table-controls">
        <div class="left-controls">
            <input type="text" class="form-control search-input" placeholder="Search name or destination...">
            <select class="form-select filter-select">
                <option>All</option>
                <option>Paid</option>
                <option>Unpaid</option>
                <option>Overdue</option>
                <option>Partially Paid</option>
            </select>
        </div>
        <div class="crm-table-toolbar">
            <div class="crm-toolbar-right">
                <button class="crm-btn crm-btn-news"><i class="fa fa-envelope"></i> Send Newsletter</button>
                <button class="crm-btn crm-btn-export"><i class="fa fa-file-export"></i> Export Report</button>
            </div>
        </div>
    </div>
</div>

<div class="crm-grid">
    <div class="crm-card">
        <div class="crm-title">Total Customers <i class="fa fa-users"></i></div>
        <div class="crm-value"><?php echo $stats['total']; ?></div>
        <div class="crm-sub">Active Customer Base</div>
        <div class="crm-progress"><div class="crm-bar bar-vip" style="width:100%"></div></div>
    </div>

    <div class="crm-card">
        <div class="crm-title">Active Travelers <i class="fa fa-plane"></i></div>
        <div class="crm-value" style="color:var(--green)"><?php echo $stats['activePercent']; ?>%</div>
        <div class="crm-sub"><?php echo $stats['active']; ?> active customers</div>
        <div class="crm-progress"><div class="crm-bar bar-gold" style="width:<?php echo $stats['activePercent']; ?>%"></div></div>
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
            <span class="tier-legend-item tier-legend-vip"><span class="tier-legend-icon">&#9733;</span> VIP <?php echo $stats['vip']; ?></span> ·
            <span class="tier-legend-item tier-legend-gold"><span class="tier-legend-icon">&#9670;</span> Gold <?php echo $stats['gold']; ?></span> ·
            <span class="tier-legend-item tier-legend-silver"><span class="tier-legend-icon">&#9679;</span> Silver <?php echo $stats['silver']; ?></span> ·
            <span class="tier-legend-item tier-legend-new"><span class="tier-legend-icon">&#10148;</span> New <?php echo $stats['new']; ?></span>
        </small>
    </div>

    <div class="crm-card feedback-card">
        <div class="crm-title">Customer Feedback <i class="fa fa-star"></i></div>
        <div class="crm-value"><?php echo $stats['rating']; ?></div>
        *****
        <div class="crm-progress">
            <div class="crm-bar bar-silver" style="width:<?php echo ($stats['rating'] / 5) * 100; ?>%"></div>
        </div>
    </div>
</div>

<div class="crm-table-wrapper">
    

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
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><input type="checkbox"></td>
                    <td>
                        <strong><?php echo htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </td>
                    <td>
                        <span class="badge <?php echo $customer['tierClass']; ?> loyalty-tier-badge">
                            <span class="tier-badge-icon tier-badge-icon-<?php echo strtolower($customer['tier']); ?>"><?php echo $customer['tierIcon']; ?></span>
                            <?php echo htmlspecialchars($customer['tier'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>PHP <?php echo number_format($customer['lifetimeValue'], 0); ?></td>
                    <td><?php echo (int) $customer['totalTrips']; ?></td>
                    <td><?php echo (int) $customer['lastContactedDays']; ?> days ago</td>
                    <td>
                        <div class="crm-actions">
                            <button class="btn-icon btn-view" title="View Profile"><i class="fa fa-eye"></i></button>
                            <button class="btn-icon btn-contact" title="Contact"><i class="fa fa-phone"></i></button>
                            <button class="btn-icon btn-notes" title="Notes"><i class="fa fa-sticky-note"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

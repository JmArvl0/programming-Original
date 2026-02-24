<div class="col-md-4 mb-4" data-widget="crm_trends" data-lazy-ready="true">
    <div class="metric-card">
        <div class="card-body">
            <div class="card-header-section crm-trends-header mb-3">
                <div class="metric-title">CRM Trends</div>
                <div class="crm-vip-indicator">
                    VIP <?= isset($crmTrends[0]['percentage']) ? (float) $crmTrends[0]['percentage'] : 0 ?>%
                </div>
            </div>
            <div class="crm-trends-grid mt-3">
                <?php foreach ($crmTrends as $trend): ?>
                <div class="crm-tier-box">
                    <div class="crm-tier-top">
                        <span class="crm-tier-count"><?= (float) $trend['percentage'] ?>% Customers</span>
                        <span class="crm-tier-label"><?= htmlspecialchars((string) $trend['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="crm-tier-icon crm-tier-icon-<?= htmlspecialchars((string) $trend['tierSlug'], ENT_QUOTES, 'UTF-8') ?>"><?= $crmTierIcons[$trend['label']] ?? '&#9679;' ?></div>
                    <div class="progress crm-tier-progress">
                        <div class="progress-bar crm-tier-progress-<?= htmlspecialchars((string) $trend['tierSlug'], ENT_QUOTES, 'UTF-8') ?>" style="width: <?= htmlspecialchars((string) $trend['percentageWidth'], ENT_QUOTES, 'UTF-8') ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

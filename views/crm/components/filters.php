<div class="section-header">
    <h2 class="crm-section-title">Client Overview</h2>
    <div class="table-controls">
        <form method="GET" action="" class="left-controls">
            <input type="hidden" name="tier" value="<?= htmlspecialchars($selectedTier, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon" aria-hidden="true"></i>
                <input type="text" name="q" class="form-control search-input" placeholder="Search name or destination..." value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </form>
        <div class="crm-table-toolbar">
            <div class="crm-toolbar-right">
                <button class="crm-btn crm-btn-news" type="button"><i class="fa fa-envelope"></i> Send Newsletter</button>
                <button class="crm-btn crm-btn-export" type="button"><i class="fa fa-file-export"></i> Export Report</button>
            </div>
        </div>
    </div>
</div>

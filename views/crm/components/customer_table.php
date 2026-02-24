<div class="crm-table-wrapper">
    <?php
        $currentPage = (int) $pagination['page'];
        $totalPages = (int) $pagination['totalPages'];
        $maxPageLinks = 10;
        $startPage = max(1, $currentPage - (int) floor($maxPageLinks / 2));
        $endPage = min($totalPages, $startPage + $maxPageLinks - 1);

        if (($endPage - $startPage + 1) < $maxPageLinks) {
            $startPage = max(1, $endPage - $maxPageLinks + 1);
        }
    ?>
    <div class="crm-table-filters">
        <ul class="nav nav-tabs filter-tabs mb-3" id="crmFilterTabs" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link <?= $selectedTier === 'all' ? 'active' : '' ?>" <?= $selectedTier === 'all' ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($buildCrmUrl(['tier' => 'all', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-filter="all" role="tab" aria-selected="<?= $selectedTier === 'all' ? 'true' : 'false' ?>">All</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link <?= $selectedTier === 'new' ? 'active' : '' ?>" <?= $selectedTier === 'new' ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($buildCrmUrl(['tier' => 'new', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-filter="new" role="tab" aria-selected="<?= $selectedTier === 'new' ? 'true' : 'false' ?>">New Customers</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link <?= $selectedTier === 'silver' ? 'active' : '' ?>" <?= $selectedTier === 'silver' ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($buildCrmUrl(['tier' => 'silver', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-filter="silver" role="tab" aria-selected="<?= $selectedTier === 'silver' ? 'true' : 'false' ?>">Silver</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link <?= $selectedTier === 'gold' ? 'active' : '' ?>" <?= $selectedTier === 'gold' ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($buildCrmUrl(['tier' => 'gold', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-filter="gold" role="tab" aria-selected="<?= $selectedTier === 'gold' ? 'true' : 'false' ?>">Gold</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link <?= $selectedTier === 'vip' ? 'active' : '' ?>" <?= $selectedTier === 'vip' ? 'aria-current="page"' : '' ?> href="<?= htmlspecialchars($buildCrmUrl(['tier' => 'vip', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-filter="vip" role="tab" aria-selected="<?= $selectedTier === 'vip' ? 'true' : 'false' ?>">VIP</a></li>
        </ul>
    </div>

    <div class="table-responsive table-scroll">
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
                <tr class="crm-customer-row" data-tier="<?= strtolower((string) $customer['tier']) ?>">
                    <td><input type="checkbox"></td>
                    <td>
                        <strong><?= htmlspecialchars((string) $customer['name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars((string) $customer['email'], ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td>
                        <span class="badge <?= htmlspecialchars((string) $customer['tierClass'], ENT_QUOTES, 'UTF-8') ?> loyalty-tier-badge">
                            <span class="tier-badge-icon tier-badge-icon-<?= strtolower((string) $customer['tier']) ?>"><?= $customer['tierIcon'] ?></span>
                            <?= htmlspecialchars((string) $customer['tier'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>PHP <?= number_format((int) $customer['lifetimeValue'], 0) ?></td>
                    <td><?= (int) $customer['totalTrips'] ?></td>
                    <td><?= (int) $customer['lastContactedDays'] ?> days ago</td>
                    <td>
                        <div class="crm-actions">
                            <button class="btn-icon btn-view" title="View Profile"><i class="fa fa-eye"></i></button>
                            <button class="btn-icon btn-contact" title="Contact"><i class="fa fa-phone"></i></button>
                            <button class="btn-icon btn-notes" title="Notes"><i class="fa fa-sticky-note"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($customers === []): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No customers found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-footer d-flex justify-content-between align-items-center">
        <div class="entries-info">
            Showing <span id="entriesStart"><?= (int) $pagination['start'] ?></span>-<span id="entriesEnd"><?= (int) $pagination['end'] ?></span> of <span id="entriesTotal"><?= (int) $pagination['totalItems'] ?></span> entries
        </div>
        <div class="pagination-wrap">
            <nav aria-label="...">
                <ul class="pagination mb-0" id="tablePagination">
                    <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildCrmUrl(['page' => max(1, (int) $pagination['page'] - 1)]), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                    </li>
                    <?php for ($pageNum = $startPage; $pageNum <= $endPage; $pageNum++): ?>
                    <li class="page-item <?= (int) $pagination['page'] === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildCrmUrl(['page' => $pageNum]), ENT_QUOTES, 'UTF-8') ?>" <?= (int) $pagination['page'] === $pageNum ? 'aria-current="page"' : '' ?>><?= $pageNum ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= (int) $pagination['page'] >= (int) $pagination['totalPages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($buildCrmUrl(['page' => min((int) $pagination['totalPages'], (int) $pagination['page'] + 1)]), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="table-footer d-flex justify-content-between align-items-center">
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
    <div class="entries-info">
        Showing <span id="entriesStart"><?= (int) $pagination['start'] ?></span>-<span id="entriesEnd"><?= (int) $pagination['end'] ?></span> of <span id="entriesTotal"><?= (int) $pagination['totalItems'] ?></span> entries
    </div>
    <div class="pagination-wrap">
        <nav aria-label="...">
            <ul class="pagination mb-0" id="tablePagination">
                <li class="page-item <?= (int) $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($buildPassportUrl(['page' => max(1, (int) $pagination['page'] - 1)]), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                </li>
                <?php for ($pageNum = $startPage; $pageNum <= $endPage; $pageNum++): ?>
                <li class="page-item <?= (int) $pagination['page'] === $pageNum ? 'active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($buildPassportUrl(['page' => $pageNum]), ENT_QUOTES, 'UTF-8') ?>" <?= (int) $pagination['page'] === $pageNum ? 'aria-current="page"' : '' ?>><?= $pageNum ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= (int) $pagination['page'] >= (int) $pagination['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($buildPassportUrl(['page' => min((int) $pagination['totalPages'], (int) $pagination['page'] + 1)]), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

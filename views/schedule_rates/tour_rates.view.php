<div class="tour-rates-view-wrap">
    <div class="table-header">
        <h2 class="section-title" style="margin: 0;">Tour Rates &amp; Availability</h2>
    </div>
    <div class="table-section tour-rates-section">
        <div class="table-wrapper">
            <table class="tour-rates-table">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Rate</th>
                        <th>Availability</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rateTours as $tour): ?>
                    <tr class="tour-rate-row">
                        <td>
                            <strong><?= htmlspecialchars($tour['name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($tour['destination']) ?></small>
                        </td>
                        <td>PHP<?= htmlspecialchars($tour['rate']) ?></td>
                        <td>
                            <div class="progress-container">
                                <span><?= (int) $tour['available'] ?>/<?= (int) $tour['capacity'] ?></span>
                                <div class="progress-bar" style="flex: 1; max-width: 100px; margin-left: 10px;">
                                    <div class="progress-fill" style="width: <?= (float) $tour['progressPercent'] ?>%; background-color: <?= htmlspecialchars($tour['availabilityColor']) ?>;"></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($tour['duration']) ?></td>
                        <td>
                            <span class="status-badge badge-<?= htmlspecialchars($tour['statusColor']) ?>">
                                <span class="status-dot status-<?= htmlspecialchars($tour['statusColor']) ?>"></span>
                                <?= htmlspecialchars($tour['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view js-tour-action" data-action="view" data-tour-id="<?= (int) $tour['id'] ?>"><i class="fas fa-eye"></i> View</button>
                                <button class="btn-manage js-tour-action" data-action="manage" data-tour-id="<?= (int) $tour['id'] ?>"><i class="fas fa-cog"></i> Manage</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer d-flex justify-content-between align-items-center">
            <div class="entries-info">
                Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?= count($rateTours) ?></span> entries
            </div>
            <div class="pagination-wrap">
                <nav aria-label="...">
                    <ul class="pagination mb-0" id="tablePagination">
                        <li class="page-item disabled"><a class="page-link">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#" aria-current="page">1</a></li>
                        <li class="page-item"><a class="page-link">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="col-md-8 mb-4" data-widget="urgent_queue" data-lazy-ready="true">
    <div class="metric-card">
        <div class="card-body">
            <div class="card-header-section mb-3">
                <div>
                    <div class="metric-title">Urgent Action Queue</div>
                </div>
                <div class="badge bg-danger"><?= (int) $urgentCount ?> Pending</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urgentActions as $action): ?>
                        <tr class="clickable-row" data-action="view" data-id="<?= htmlspecialchars((string) $action['rowId'], ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars((string) $action['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $action['type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="status-dot status-<?= htmlspecialchars((string) $action['status']['color'], ENT_QUOTES, 'UTF-8') ?>"></span>
                                <?= htmlspecialchars((string) $action['status']['status'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><?= htmlspecialchars((string) $action['deadline'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-action view-btn"
                                        data-name="<?= htmlspecialchars((string) $action['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-type="<?= htmlspecialchars((string) $action['type'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-status="<?= htmlspecialchars((string) $action['status']['status'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-deadline="<?= htmlspecialchars((string) $action['deadline'], ENT_QUOTES, 'UTF-8') ?>">
                                    View
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

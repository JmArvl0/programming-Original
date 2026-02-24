<div class="col-md-8 mb-4" data-widget="operations_overview" data-lazy-ready="true">
    <div class="metric-card">
        <div class="card-body">
            <div class="card-header-section mb-3">
                <div class="metric-title">Operations Overview</div>
            </div>
            <div class="map-container text-center" data-widget="map" data-lazy-ready="true">
                <div class="fs-1 mb-2">🗺️</div>
                <p class="fw-bold">Live Map View</p>
                <p class="small text-muted">Interactive map with transport and staff locations</p>
                <button class="btn btn-primary btn-sm mt-2" id="refreshMapBtn">Refresh Locations</button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-hover operations-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Staff On-Duty</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($operations as $operation): ?>
                        <tr class="clickable-row"
                            data-action="track"
                            data-id="<?= htmlspecialchars((string) $operation['rowId'], ENT_QUOTES, 'UTF-8') ?>"
                            data-name="<?= htmlspecialchars((string) $operation['name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-staff="<?= htmlspecialchars((string) $operation['staff'], ENT_QUOTES, 'UTF-8') ?>"
                            data-type="<?= htmlspecialchars((string) $operation['type'], ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars((string) $operation['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $operation['staff'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $operation['type'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

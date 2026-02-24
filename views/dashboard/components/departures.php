<div class="col-md-4 mb-4" data-widget="departures" data-lazy-ready="true">
    <div class="metric-card">
        <div class="card-body">
            <div class="card-header-section mb-3">
                <div>
                    <div class="metric-title">Upcoming Departures</div>
                </div>
                <div class="badge bg-warning"><?= (int) $departuresCount ?> Today</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Departure</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingDepartures as $departure): ?>
                        <tr class="clickable-row" data-action="remind" data-id="<?= htmlspecialchars((string) $departure['rowId'], ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars((string) $departure['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $departure['departure'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-action remind-btn"
                                        data-name="<?= htmlspecialchars((string) $departure['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-departure="<?= htmlspecialchars((string) $departure['departure'], ENT_QUOTES, 'UTF-8') ?>">
                                    Remind
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

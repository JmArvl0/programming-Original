<div class="tour-rates-view-wrap">
    <div class="table-section tour-rates-section">
        <?php if (!empty($rateTours)): ?>
        <div class="tour-cards-grid">
            <?php foreach ($rateTours as $tour): ?>
            <?php
                $img = !empty($tour['image']) ? htmlspecialchars($tour['image']) : '';
                $destLabel = isset($tour['destination']) ? (string) $tour['destination'] : '';
                $destEsc = htmlspecialchars($destLabel);
            ?>
            <?php $isDuplicate = ($destLabel !== '' && isset($tour['name']) && stripos((string)$tour['name'], $destLabel) !== false) ? 1 : 0; ?>
            <div class="tour-card" data-duplicate="<?= $isDuplicate ?>">
                <div class="tour-thumb" role="group" aria-label="Thumbnail for <?= $destLabel ?>">
                    <?php if ($img): ?>
                        <img class="tour-thumb-img" src="<?= $img ?>" alt="<?= $destEsc ?>">
                    <?php else: ?>
                        <div class="tour-thumb-placeholder" aria-hidden="true"></div>
                    <?php endif; ?>
                    <?php if (! $isDuplicate && $destEsc !== ''): ?>
                        <div class="thumb-overlay"><span class="thumb-destination"><?= $destEsc ?></span></div>
                    <?php endif; ?>
                </div>
                <div class="card-header">
                    <div class="card-title">
                        <?php
                            $rawName = isset($tour['name']) ? (string) $tour['name'] : '';
                            $displayName = $rawName;
                            if ($destLabel !== '' && stripos($rawName, $destLabel) !== false) {
                                $pat = '/[,\-–:\s]*' . preg_quote($destLabel, '/') . '/iu';
                                $displayName = preg_replace($pat, '', $rawName);
                                $displayName = trim($displayName, " \t\n\r\0\x0B-–,:;");
                                if ($displayName === '') { $displayName = $rawName; }
                            }
                        ?>
                        <span class="tour-name"><?= htmlspecialchars($displayName) ?></span>
                        <?php if (! $isDuplicate && $destEsc !== ''): ?>
                            <div class="card-subtitle"><?= $destEsc ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-rate">PHP <span class="rate-amount"><?= htmlspecialchars($tour['rate']) ?></span></div>
                </div>
                <div class="card-body">
                    <div class="card-meta">
                        <div class="meta-item">
                            <div class="meta-label">Availability</div>
                            <div class="meta-value">
                                <div class="progress-container">
                                    <span class="avail-count"><?= (int) $tour['available'] ?>/<?= (int) $tour['capacity'] ?></span>
                                    <div class="progress-bar small">
                                        <div class="progress-fill" style="width: <?= (float) $tour['progressPercent'] ?>%; background-color: <?= htmlspecialchars($tour['availabilityColor']) ?>;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Duration</div>
                            <div class="meta-value"><?= htmlspecialchars($tour['duration']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="status-wrap">
                        <span class="status-badge badge-<?= htmlspecialchars($tour['statusColor']) ?>">
                            <span class="status-dot status-<?= htmlspecialchars($tour['statusColor']) ?>"></span>
                            <?= htmlspecialchars($tour['status']) ?>
                        </span>
                    </div>
                    <div class="card-actions action-buttons">
                        <button class="btn-view js-tour-action" data-action="view" data-tour-id="<?= (int) $tour['id'] ?>"><i class="fas fa-eye"></i></button>
                        <button class="btn-manage js-tour-action" data-action="manage" data-tour-id="<?= (int) $tour['id'] ?>"><i class="fas fa-cog"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-guests-message">No tour rates available for the current filters.</div>
        <?php endif; ?>
        <div class="tour-rates-footer">
            <div class="entries-info">
                Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?= count($rateTours) ?></span> entries
            </div>
        </div>
    </div>
</div>

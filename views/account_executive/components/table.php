<div class="table-scroll">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Destination</th>
                <th>Last Contacted</th>
                <th>
                    <div class="dropdown payment-header-dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" id="paymentHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Payment
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="paymentHeaderDropdown">
                            <li><a class="dropdown-item <?= $selectedPayment === 'all' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildAccountExecutiveUrl(['payment' => 'all', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-payment="all">All Payments</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= $selectedPayment === 'paid' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildAccountExecutiveUrl(['payment' => 'paid', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-payment="paid"><span class="badge-dot bg-success me-2 ms-2"></span> Paid</a></li>
                            <li><a class="dropdown-item <?= $selectedPayment === 'unpaid' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildAccountExecutiveUrl(['payment' => 'unpaid', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-payment="unpaid"><span class="badge-dot bg-danger me-2 ms-2"></span> Unpaid</a></li>
                            <li><a class="dropdown-item <?= $selectedPayment === 'overdue' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildAccountExecutiveUrl(['payment' => 'overdue', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-payment="overdue"><span class="badge-dot bg-danger me-2 ms-2" style="background: #dc3545 !important;"></span> Overdue</a></li>
                            <li><a class="dropdown-item <?= $selectedPayment === 'partially paid' ? 'active' : '' ?>" href="<?= htmlspecialchars($buildAccountExecutiveUrl(['payment' => 'partially paid', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>" data-payment="partially paid"><span class="badge-dot bg-warning me-2 ms-2"></span> Partially Paid</a></li>
                        </ul>
                    </div>
                </th>
                <th>Progress</th>
                <th>Documents</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr
                data-payment-status="<?= htmlspecialchars($customer['paymentStatusNormalized'], ENT_QUOTES, 'UTF-8') ?>"
                data-status="<?= htmlspecialchars($customer['statusNormalized'], ENT_QUOTES, 'UTF-8') ?>"
                data-documents-status="<?= htmlspecialchars($customer['documentsStatusNormalized'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                data-progress="<?= (int) $customer['progressWidth'] ?>"
                data-date="<?= htmlspecialchars($customer['createdDate'], ENT_QUOTES, 'UTF-8') ?>"
                data-last-contacted="<?= htmlspecialchars($customer['lastContactedDate'], ENT_QUOTES, 'UTF-8') ?>"
                data-refund="<?= htmlspecialchars($customer['refund'], ENT_QUOTES, 'UTF-8') ?>"
                data-id="<?= (int) $customer['id'] ?>"
            >
                <td><input type="checkbox" class="row-checkbox"></td>
                <td>#<?= str_pad((string) $customer['id'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($customer['destination'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($customer['lastContacted'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge rounded-pill <?= $customer['paymentBadgeClass'] ?>"><?= htmlspecialchars($customer['paymentStatus'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <?= (int) $customer['progressWidth'] ?>%
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width:<?= (int) $customer['progressWidth'] ?>%"></div>
                    </div>
                </td>
                    <td><span class="badge rounded-pill <?= htmlspecialchars(document_status_badge_class($customer['documentsStatus'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(document_status_label($customer['documentsStatus'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary js-customer-action" data-action="view-customer" data-id="<?= (int) $customer['id'] ?>" data-name="<?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-eye"></i></button>
                    <button type="button" class="btn btn-sm btn-success js-customer-action" data-action="edit-customer" data-id="<?= (int) $customer['id'] ?>" data-name="<?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-danger js-customer-action" data-action="delete-customer" data-id="<?= (int) $customer['id'] ?>" data-name="<?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../config/database.php';

$action = $_GET['action'] ?? 'view';
$id = (int)($_GET['id'] ?? 0);

$conn = getDBConnection();
if (!($conn instanceof mysqli)) {
    echo '<div class="alert alert-danger">Database connection error</div>';
    exit;
}

// First, get the customer data (needed for both view and edit)
$customer = null;
$passport_status = 'not_started';

$sql = "SELECT c.*, 
               COALESCE(pa.documents_status, 'not_started') as passport_status,
               CASE 
                   WHEN c.payment_status = 'paid' THEN 'bg-success'
                   WHEN c.payment_status = 'partially paid' THEN 'bg-warning'
                   WHEN c.payment_status = 'overdue' THEN 'bg-danger'
                   ELSE 'bg-secondary'
               END as payment_badge_class,
               CASE 
                   WHEN c.status = 'finished' THEN 'bg-success'
                   WHEN c.status = 'processing' THEN 'bg-primary'
                   WHEN c.status = 'pending' THEN 'bg-warning'
                   WHEN c.status = 'cancelled' THEN 'bg-danger'
                   ELSE 'bg-info'
               END as status_badge_class
        FROM customers c
        LEFT JOIN passport_applications pa ON c.id = pa.customer_id
        WHERE c.id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $passport_status = $customer['passport_status'] ?? 'not_started';
    $stmt->close();
}

if (!$customer) {
    echo '<div class="alert alert-warning">Customer not found</div>';
    exit;
}

if ($action === 'view') {
    // Get interactions
    $interactions = [];
    $intSql = "SELECT ci.* FROM account_executive ae
JOIN bookings b ON ae.booking_id = b.id
JOIN customers c ON b.customer_id = c.id
LEFT JOIN crm_interactions ci ON ci.customer_id = c.id
WHERE c.id = ? ORDER BY ci.created_at DESC LIMIT 5";
    $intStmt = $conn->prepare($intSql);
    if ($intStmt) {
        $intStmt->bind_param('i', $id);
        $intStmt->execute();
        $intRes = $intStmt->get_result();
        while ($r = $intRes->fetch_assoc()) {
            $interactions[] = $r;
        }
        $intStmt->close();
    }
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h6 class="border-bottom pb-2">Personal Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Full Name:</th>
                        <td><?= htmlspecialchars($customer['full_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= htmlspecialchars($customer['email'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Destination:</th>
                        <td><?= htmlspecialchars($customer['destination'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Tier:</th>
                        <td>
                            <span class="badge <?= ($customer['tier'] ?? 'new') === 'vip' ? 'bg-warning' : 'bg-info' ?>">
                                <?= strtoupper($customer['tier'] ?? 'NEW') ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="border-bottom pb-2">Status Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge <?= htmlspecialchars($customer['status_badge_class'] ?? 'bg-secondary') ?>">
                                <?= htmlspecialchars(ucfirst($customer['status'] ?? '')) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <span class="badge <?= htmlspecialchars($customer['payment_badge_class'] ?? 'bg-secondary') ?>">
                                <?= htmlspecialchars(ucfirst($customer['payment_status'] ?? '')) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Admission Status:</th>
                        <td><?= htmlspecialchars(ucfirst($customer['admission_status'] ?? 'pending')) ?></td>
                    </tr>
                    <tr>
                        <th>Progress:</th>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2"><?= (int)($customer['progress'] ?? 0) ?>%</span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?= (int)($customer['progress'] ?? 0) ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Passport Status:</th>
                        <td>
                            <span class="badge <?= $passport_status === 'approved' ? 'bg-success' : 'bg-warning' ?>">
                                <?= htmlspecialchars(ucfirst($passport_status)) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h6 class="border-bottom pb-2">Recent Interactions</h6>
                <?php if (empty($interactions)): ?>
                    <p class="text-muted">No interactions recorded</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($interactions as $interaction): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars(ucfirst($interaction['interaction_type'] ?? '')) ?></strong>
                                    <small class="text-muted">
                                        <?= isset($interaction['created_at']) ? date('M d, Y', strtotime($interaction['created_at'])) : '' ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($interaction['details'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php

} elseif ($action === 'edit') {
    // Customer data already fetched above
    ?>
    <form id="editCustomerForm" onsubmit="return false;">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="full_name" 
                       value="<?= htmlspecialchars($customer['full_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" 
                       value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" 
                       value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Destination</label>
                <input type="text" class="form-control" name="destination" 
                       value="<?= htmlspecialchars($customer['destination'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="pending" <?= ($customer['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= ($customer['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="finished" <?= ($customer['status'] ?? '') === 'finished' ? 'selected' : '' ?>>Finished</option>
                    <option value="cancelled" <?= ($customer['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Payment Status</label>
                <select class="form-select" name="payment_status">
                    <option value="unpaid" <?= ($customer['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="partially paid" <?= ($customer['payment_status'] ?? '') === 'partially paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="paid" <?= ($customer['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="overdue" <?= ($customer['payment_status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Progress (%)</label>
                <input type="number" class="form-control" name="progress" min="0" max="100" 
                       value="<?= (int)($customer['progress'] ?? 0) ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tier</label>
                <select class="form-select" name="tier">
                    <option value="new" <?= ($customer['tier'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="silver" <?= ($customer['tier'] ?? '') === 'silver' ? 'selected' : '' ?>>Silver</option>
                    <option value="gold" <?= ($customer['tier'] ?? '') === 'gold' ? 'selected' : '' ?>>Gold</option>
                    <option value="vip" <?= ($customer['tier'] ?? '') === 'vip' ? 'selected' : '' ?>>VIP</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Refund Flag</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="refund_flag" value="1" 
                           <?= !empty($customer['refund_flag']) ? 'checked' : '' ?>>
                    <label class="form-check-label">Mark for refund</label>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" onclick="saveCustomerEdit(<?= $id ?>)">Save Changes</button>
        </div>
    </form>
    <?php
}

$conn->close();
?>
<?php
require_once '../config/database.php';

$action = $_GET['action'] ?? 'view';
$id = $_GET['id'] ?? 0;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=beyond_the_map", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($action === 'view') {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   COALESCE(pa.documents_status, 'not_started') as passport_status
            FROM customers c
            LEFT JOIN passport_applications pa ON c.id = pa.customer_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Personal Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Full Name:</th>
                            <td><?= htmlspecialchars($customer['full_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($customer['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Destination:</th>
                            <td><?= htmlspecialchars($customer['destination']) ?></td>
                        </tr>
                        <tr>
                            <th>Tier:</th>
                            <td><span class="badge bg-<?= $customer['tier'] === 'vip' ? 'warning' : 'info' ?>"><?= strtoupper($customer['tier']) ?></span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Status Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge <?= $customer['status_badge_class'] ?>"><?= ucfirst($customer['status']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td><span class="badge <?= $customer['payment_badge_class'] ?>"><?= ucfirst($customer['payment_status']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Admission Status:</th>
                            <td><?= ucfirst($customer['admission_status']) ?></td>
                        </tr>
                        <tr>
                            <th>Progress:</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= $customer['progress'] ?>%</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $customer['progress'] ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Passport Status:</th>
                            <td><span class="badge bg-<?= $customer['passport_status'] === 'approved' ? 'success' : 'warning' ?>"><?= ucfirst($customer['passport_status']) ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Recent Interactions</h6>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT * FROM crm_interactions 
                        WHERE customer_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$id]);
                    $interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <?php if (empty($interactions)): ?>
                        <p class="text-muted">No interactions recorded</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($interactions as $interaction): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= ucfirst($interaction['interaction_type']) ?></strong>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($interaction['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($interaction['details']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    <?php } elseif ($action === 'edit') { 
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        
        <form id="editCustomerForm" onsubmit="return false;">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Destination</label>
                    <input type="text" class="form-control" name="destination" value="<?= htmlspecialchars($customer['destination']) ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="pending" <?= $customer['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $customer['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="finished" <?= $customer['status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                        <option value="cancelled" <?= $customer['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Status</label>
                    <select class="form-select" name="payment_status">
                        <option value="unpaid" <?= $customer['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        <option value="partially paid" <?= $customer['payment_status'] === 'partially paid' ? 'selected' : '' ?>>Partially Paid</option>
                        <option value="paid" <?= $customer['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= $customer['payment_status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Progress (%)</label>
                    <input type="number" class="form-control" name="progress" min="0" max="100" value="<?= $customer['progress'] ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tier</label>
                    <select class="form-select" name="tier">
                        <option value="new" <?= $customer['tier'] === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="silver" <?= $customer['tier'] === 'silver' ? 'selected' : '' ?>>Silver</option>
                        <option value="gold" <?= $customer['tier'] === 'gold' ? 'selected' : '' ?>>Gold</option>
                        <option value="vip" <?= $customer['tier'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Refund Flag</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="refund_flag" value="1" <?= $customer['refund_flag'] ? 'checked' : '' ?>>
                        <label class="form-check-label">Mark for refund</label>
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="saveCustomerEdit(<?= $id ?>)">Save Changes</button>
            </div>
        </form>
        
    <?php }
    
} catch(Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
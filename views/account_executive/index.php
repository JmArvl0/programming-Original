<?php
// Start session
session_start();

// Include database connection
require_once 'config/database.php';

// Get filter parameters
$selectedStatus = $_GET['status'] ?? 'all';
$selectedPayment = $_GET['payment'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);

// Get statistics
try {
    // Paid customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status = 'paid'");
    $paidCount = $stmt->fetchColumn();
    
    // Admitted customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'admitted'");
    $admittedCount = $stmt->fetchColumn();
    
    // Pending admission count
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE admission_status = 'pending'");
    $pendingAdmissionCount = $stmt->fetchColumn();
    
    // Unpaid customers count (unpaid + overdue + partially paid)
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers WHERE payment_status IN ('unpaid', 'overdue', 'partially paid')");
    $unpaidCount = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    $paidCount = $admittedCount = $pendingAdmissionCount = $unpaidCount = 0;
}

// Function to build URL with parameters
function buildUrl($params) {
    $currentParams = $_GET;
    $newParams = array_merge($currentParams, $params);
    return '?' . http_build_query($newParams);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Executive Dashboard - Beyond the Map</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
        .progress {
            height: 6px;
            margin-top: 4px;
        }
        .toast-container {
            z-index: 9999;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Account Executive</h1>
                <p class="text-muted">Handles Customer Processing</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="paidCount"><?= $paidCount ?></div>
                    <div class="stat-label">Paid Customers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="admittedCount"><?= $admittedCount ?></div>
                    <div class="stat-label">Customer Admitted</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="pendingAdmissionCount"><?= $pendingAdmissionCount ?></div>
                    <div class="stat-label">Pending Admission</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="unpaidCount"><?= $unpaidCount ?></div>
                    <div class="stat-label">Unpaid Customers</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="searchCustomer" 
                           placeholder="Search name, email, or destination..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="button" id="searchBtn">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-success" id="sendReminderBtn">
                    <i class="fa fa-bell me-2"></i>Send Reminder
                </button>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card bg-light border">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <span class="me-3"><i class="fa fa-tasks me-1"></i>Bulk Actions:</span>
                            <select class="form-select form-select-sm w-auto me-2" id="bulkActionSelect">
                                <option value="">Select Action</option>
                                <option value="delete">Delete Selected</option>
                                <option value="send-reminder">Send Reminder</option>
                                <option value="mark-followup">Mark for Follow-up</option>
                            </select>
                            <button class="btn btn-sm btn-primary" id="bulkActionBtn">
                                <i class="fa fa-play me-1"></i>Apply
                            </button>
                            <span class="ms-3 text-muted" id="selectedCount">0 selected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'all' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'all', 'page' => 1]) ?>" 
                   data-status="all">All Customers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'new' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'new', 'page' => 1]) ?>" 
                   data-status="new">New</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'for-followup' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'for-followup', 'page' => 1]) ?>" 
                   data-status="for-followup">For Follow-up</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'ongoing' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'ongoing', 'page' => 1]) ?>" 
                   data-status="ongoing">Ongoing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'payment-issues' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'payment-issues', 'page' => 1]) ?>" 
                   data-status="payment-issues">Payment Issues</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'finished' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'finished', 'page' => 1]) ?>" 
                   data-status="finished">Finished</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $selectedStatus === 'refund' ? 'active' : '' ?>" 
                   href="<?= buildUrl(['status' => 'refund', 'page' => 1]) ?>" 
                   data-status="refund">Refund</a>
            </li>
        </ul>

        <!-- Customer Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Destination</th>
                        <th>Last Contacted</th>
                        <th>
                            <div class="dropdown">
                                <button class="btn btn-link dropdown-toggle text-dark" type="button" id="paymentHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Payment <i class="fa fa-chevron-down ms-1"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="paymentHeaderDropdown">
                                    <li><a class="dropdown-item <?= $selectedPayment === 'all' ? 'active' : '' ?>" href="<?= buildUrl(['payment' => 'all', 'page' => 1]) ?>" data-payment="all">All Payments</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item <?= $selectedPayment === 'paid' ? 'active' : '' ?>" href="<?= buildUrl(['payment' => 'paid', 'page' => 1]) ?>" data-payment="paid"><span class="badge-dot bg-success me-2"></span> Paid</a></li>
                                    <li><a class="dropdown-item <?= $selectedPayment === 'unpaid' ? 'active' : '' ?>" href="<?= buildUrl(['payment' => 'unpaid', 'page' => 1]) ?>" data-payment="unpaid"><span class="badge-dot bg-danger me-2"></span> Unpaid</a></li>
                                    <li><a class="dropdown-item <?= $selectedPayment === 'overdue' ? 'active' : '' ?>" href="<?= buildUrl(['payment' => 'overdue', 'page' => 1]) ?>" data-payment="overdue"><span class="badge-dot bg-danger me-2" style="background: #dc3545 !important;"></span> Overdue</a></li>
                                    <li><a class="dropdown-item <?= $selectedPayment === 'partially paid' ? 'active' : '' ?>" href="<?= buildUrl(['payment' => 'partially paid', 'page' => 1]) ?>" data-payment="partially paid"><span class="badge-dot bg-warning me-2"></span> Partially Paid</a></li>
                                </ul>
                            </div>
                        </th>
                        <th>Progress</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    <?php
                    // Fetch customers for initial load
                    try {
                        $limit = 9;
                        $offset = ($page - 1) * $limit;
                        
                        $sql = "SELECT 
                                    c.*,
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
                                    END as status_badge_class,
                                    DATE_FORMAT(c.last_contacted_at, '%m/%d/%Y - %h:%i %p') as last_contacted_formatted
                                FROM customers c
                                ORDER BY c.id ASC
                                LIMIT :limit OFFSET :offset";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();
                        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($customers as $customer):
                    ?>
                    <tr data-id="<?= $customer['id'] ?>" 
                        data-payment="<?= $customer['payment_status'] ?>"
                        data-status="<?= $customer['status'] ?>"
                        data-refund="<?= $customer['refund_flag'] ?>">
                        <td><input type="checkbox" class="row-checkbox" value="<?= $customer['id'] ?>"></td>
                        <td>#<?= str_pad($customer['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($customer['full_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($customer['destination']) ?></td>
                        <td><?= $customer['last_contacted_formatted'] ?? 'Never' ?></td>
                        <td>
                            <span class="badge rounded-pill <?= $customer['payment_badge_class'] ?>">
                                <?= ucfirst($customer['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2"><?= $customer['progress'] ?>%</span>
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width:<?= $customer['progress'] ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill <?= $customer['status_badge_class'] ?>">
                                <?= ucfirst($customer['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary view-customer" 
                                    data-id="<?= $customer['id'] ?>" 
                                    data-name="<?= htmlspecialchars($customer['full_name']) ?>"
                                    title="View">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success edit-customer" 
                                    data-id="<?= $customer['id'] ?>" 
                                    data-name="<?= htmlspecialchars($customer['full_name']) ?>"
                                    title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger delete-customer" 
                                    data-id="<?= $customer['id'] ?>" 
                                    data-name="<?= htmlspecialchars($customer['full_name']) ?>"
                                    title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    } catch(PDOException $e) {
                        echo '<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing <?= $offset + 1 ?>-<?= min($offset + $limit, 9) ?> of 9 entries
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildUrl(['page' => $page - 1]) ?>" data-page="<?= $page - 1 ?>">Previous</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item <?= $page >= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= buildUrl(['page' => $page + 1]) ?>" data-page="<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modals -->
    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="editModalFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveEditBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    Are you sure you want to delete this customer?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fa fa-info-circle me-2" id="toastIcon"></i>
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <small>just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/customer-buttons.js"></script>
</body>
</html>
<div class="section-header">
    <h2 class="section-title">Customer List</h2>
    <div class="table-controls">
        <div class="left-controls">
            <input type="text" class="form-control search-input" placeholder="Search name or destination...">
            <select class="form-select filter-select">
                <option>All</option>
                <option>Paid</option>
                <option>Unpaid</option>
                <option>Overdue</option>
                <option>Partially Paid</option>
            </select>
        </div>
        <button id="sendPaymentReminderBtn" class="btn btn-warning">
            <i class="fa fa-bell"></i> Send Reminder
        </button>
    </div>
</div>

<div class="overview-cards">
    <div class="overview-card"><div class="icon-box"><i class="fa fa-credit-card"></i></div><div><small>Paid Customers</small><h4><?php echo $stats['paid']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-user-check"></i></div><div><small>Customer Admitted</small><h4><?php echo $stats['admitted']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Pending Admission</small><h4><?php echo $stats['pending']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-exclamation-circle"></i></div><div><small>Unpaid Customers</small><h4><?php echo $stats['unpaid']; ?></h4></div></div>
</div>

<div class="table-section">
    <ul class="nav nav-tabs mb-3" id="aeStatusTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="#" data-filter="all" role="tab" aria-selected="true">All</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="new" role="tab" aria-selected="false">New</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="for-follow-up" role="tab" aria-selected="false">For Follow-up</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="ongoing" role="tab" aria-selected="false">Ongoing</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="payment-issues" role="tab" aria-selected="false">Payment Issues</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="finished" role="tab" aria-selected="false">Finished</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="refund" role="tab" aria-selected="false">Refund</a></li>
    </ul>

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
                                <li><a class="dropdown-item" href="#" data-payment="all">All Payments</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" data-payment="Paid"><span class="badge-dot bg-success me-2 ms-2"></span> Paid</a></li>
                                <li><a class="dropdown-item" href="#" data-payment="Unpaid"><span class="badge-dot bg-danger me-2 ms-2"></span> Unpaid</a></li>
                                <li><a class="dropdown-item" href="#" data-payment="Overdue"><span class="badge-dot bg-danger me-2 ms-2" style="background: #dc3545 !important;"></span> Overdue</a></li>
                                <li><a class="dropdown-item" href="#" data-payment="Partially Paid"><span class="badge-dot bg-warning me-2 ms-2"></span> Partially Paid</a></li>
                            </ul>
                        </div>
                    </th>
                    <th>Progress</th>
                    <th>
                        <div class="dropdown status-header-dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="statusHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Status
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusHeaderDropdown">
                                <li><a class="dropdown-item" href="#" data-status="all">All Status</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" data-status="Finished"><span class="badge-dot bg-success me-2 ms-2"></span> Finished</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Processing"><span class="badge-dot bg-info me-2 ms-2"></span> Processing</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Pending"><span class="badge-dot bg-warning me-2 ms-2"></span> Pending</a></li>
                                <li><a class="dropdown-item" href="#" data-status="Cancelled"><span class="badge-dot bg-danger me-2 ms-2"></span> Cancelled</a></li>
                            </ul>
                        </div>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr
                    data-payment="<?php echo htmlspecialchars($customer['paymentStatusNormalized'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-status="<?php echo htmlspecialchars($customer['statusNormalized'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-progress="<?php echo (int) $customer['progress']; ?>"
                    data-date="<?php echo htmlspecialchars($customer['createdDate'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-last-contacted="<?php echo htmlspecialchars($customer['lastContactedDate'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-refund="<?php echo htmlspecialchars($customer['refund'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <td><input type="checkbox" class="row-checkbox"></td>
                    <td>#<?php echo str_pad((string) $customer['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['destination'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['lastContacted'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge rounded-pill <?php echo $customer['paymentBadgeClass']; ?>"><?php echo htmlspecialchars($customer['paymentStatus'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td>
                        <?php echo (int) $customer['progress']; ?>%
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width:<?php echo (int) $customer['progress']; ?>%"></div>
                        </div>
                    </td>
                    <td><span class="badge rounded-pill <?php echo $customer['statusBadgeClass']; ?>"><?php echo htmlspecialchars($customer['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm btn-success"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-footer d-flex justify-content-between align-items-center">
        <div class="entries-info">
            Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?php echo count($customers); ?></span> entries
        </div>
        <div class="pagination-wrap">
            <nav aria-label="...">
                <ul class="pagination mb-0" id="tablePagination">
                    <li class="page-item disabled"><a class="page-link">Previous</a></li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item active"><a class="page-link" href="#" aria-current="page">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

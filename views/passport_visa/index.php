<div class="section-header">
    <h2 class="section-title">Applicant List</h2>
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
    <div class="overview-card"><div class="icon-box"><i class="fa fa-users"></i></div><div><small>Total</small><h4><?php echo $stats['total']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-check-circle"></i></div><div><small>Approved</small><h4><?php echo $stats['approved']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-clock"></i></div><div><small>Under Review</small><h4><?php echo $stats['review']; ?></h4></div></div>
    <div class="overview-card"><div class="icon-box"><i class="fa fa-user-plus"></i></div><div><small>New (7 days)</small><h4><?php echo $stats['new']; ?></h4></div></div>
</div>

<div class="table-section">
    <ul class="nav nav-tabs filter-tabs mb-3" id="applicantFilterTabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link active" aria-current="page" href="#" data-filter="all" role="tab" aria-selected="true">All</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="new" role="tab" aria-selected="false">New</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="documents-issue" role="tab" aria-selected="false">Documents Issue</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="under-processing" role="tab" aria-selected="false">Under Processing</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="for-action" role="tab" aria-selected="false">For Action</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="approved" role="tab" aria-selected="false">Approved</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" href="#" data-filter="completed" role="tab" aria-selected="false">Completed</a></li>
    </ul>

    <div class="table-responsive table-scroll">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Passport</th>
                    <th>Country</th>
                    <th>Documents</th>
                    <th>Application</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $applicant): ?>
                <tr
                    data-documents="<?php echo htmlspecialchars($applicant['documentsNormalized'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-application="<?php echo htmlspecialchars($applicant['applicationNormalized'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-date="<?php echo htmlspecialchars($applicant['submissionDateIso'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <td><input type="checkbox" class="row-checkbox"></td>
                    <td>#<?php echo str_pad((string) $applicant['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><strong><?php echo htmlspecialchars($applicant['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><span class="status-dot status-<?php echo htmlspecialchars($applicant['passport']['status'], ENT_QUOTES, 'UTF-8'); ?>"></span> <?php echo htmlspecialchars($applicant['passport']['number'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($applicant['country'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge rounded-pill <?php echo $applicant['documentsBadgeClass']; ?>"><?php echo htmlspecialchars($applicant['documents']['text'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><span class="badge rounded-pill <?php echo $applicant['applicationBadgeClass']; ?>"><?php echo htmlspecialchars($applicant['application']['text'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td class="priority-<?php echo strtolower(htmlspecialchars($applicant['priority'], ENT_QUOTES, 'UTF-8')); ?>"><?php echo htmlspecialchars($applicant['priority'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button>
                        <button class="btn btn-sm btn-success"><i class="fa fa-upload"></i></button>
                        <button class="btn btn-sm btn-info"><i class="fa fa-search"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-footer d-flex justify-content-between align-items-center">
        <div class="entries-info">Showing <span id="entriesStart">1</span>-<span id="entriesEnd">10</span> of <span id="entriesTotal"><?php echo count($applicants); ?></span> entries</div>
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

// Customer Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // ========== STATE MANAGEMENT ==========
    let currentStatus = getUrlParameter('status') || 'all';
    let currentPayment = getUrlParameter('payment') || 'all';
    let currentPage = parseInt(getUrlParameter('page')) || 1;
    let totalPages = 1;
    let searchTimeout;
    
    // ========== INITIALIZATION ==========
    initializeEventListeners();
    loadTableData();
    updateStats();
    
    // ========== EVENT LISTENERS ==========
    function initializeEventListeners() {
        // Tab clicks
        document.querySelectorAll('.nav-link[data-status]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.dataset.status;
                updateUrlParameter('status', status);
                updateUrlParameter('page', 1);
                currentStatus = status;
                currentPage = 1;
                loadTableData();
            });
        });
        
        // Payment filter clicks
        document.querySelectorAll('[data-payment]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const payment = this.dataset.payment;
                updateUrlParameter('payment', payment);
                updateUrlParameter('page', 1);
                currentPayment = payment;
                currentPage = 1;
                loadTableData();
                
                // Update active state
                document.querySelectorAll('[data-payment]').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Search input
        const searchInput = document.getElementById('searchCustomer');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadTableData();
                }, 500);
            });
        }
        
        // Select all checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
        
        // Send reminder toggle
        const reminderToggle = document.getElementById('sendReminder');
        if (reminderToggle) {
            reminderToggle.addEventListener('change', function() {
                if (this.checked) {
                    sendBulkReminders();
                }
            });
        }
    }
    
    // ========== LOAD TABLE DATA ==========
    function loadTableData() {
        const tableBody = document.getElementById('customerTableBody');
        const search = document.getElementById('searchCustomer')?.value || '';
        
        // Show loading
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Build URL
        let url = `api/get-customers.php?page=${currentPage}&status=${currentStatus}&payment=${currentPayment}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderTable(data.data);
                    updatePagination(data.pagination);
                    updateEntriesInfo(data.pagination);
                } else {
                    throw new Error(data.message || 'Failed to load data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center text-danger py-4">
                            Failed to load data: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }
    
    // ========== RENDER TABLE ==========
    function renderTable(customers) {
        const tableBody = document.getElementById('customerTableBody');
        
        if (!customers || customers.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4">
                        No customers found
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        customers.forEach(customer => {
            html += `
                <tr data-id="${customer.id}" 
                    data-payment="${customer.paymentStatusNormalized || ''}" 
                    data-status="${customer.statusNormalized || ''}"
                    data-progress="${customer.progressWidth || 0}"
                    data-date="${customer.createdDate || ''}"
                    data-last-contacted="${customer.lastContactedDate || ''}"
                    data-refund="${customer.refund || 0}">
                    <td><input type="checkbox" class="row-checkbox" value="${customer.id}"></td>
                    <td>#${String(customer.id).padStart(3, '0')}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold">${escapeHtml(customer.name)}</span>
                            ${customer.tier ? `<span class="badge bg-${getTierBadge(customer.tier)} ms-2">${customer.tier.toUpperCase()}</span>` : ''}
                        </div>
                        <small class="text-muted">${escapeHtml(customer.email || '')}</small>
                    </td>
                    <td>${escapeHtml(customer.destination || '')}</td>
                    <td>${customer.lastContacted || 'Never'}</td>
                    <td><span class="badge rounded-pill ${customer.payment_badge_class || 'bg-secondary'}">${customer.paymentStatus || 'Unknown'}</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="me-2">${customer.progressWidth || 0}%</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width:${customer.progressWidth || 0}%"></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge rounded-pill ${customer.status_badge_class || 'bg-info'}">${customer.status || 'Unknown'}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary js-customer-action" 
                                    data-action="view" 
                                    data-id="${customer.id}" 
                                    data-name="${escapeHtml(customer.name)}"
                                    title="View">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-success js-customer-action" 
                                    data-action="edit" 
                                    data-id="${customer.id}" 
                                    data-name="${escapeHtml(customer.name)}"
                                    title="Edit">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-danger js-customer-action" 
                                    data-action="delete" 
                                    data-id="${customer.id}" 
                                    data-name="${escapeHtml(customer.name)}"
                                    title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        
        // Re-attach event listeners
        attachActionListeners();
        attachCheckboxListeners();
    }
    
    // ========== ACTION BUTTON HANDLERS ==========
    function attachActionListeners() {
        document.querySelectorAll('.js-customer-action').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const action = this.dataset.action;
                const customerId = this.dataset.id;
                const customerName = this.dataset.name;
                
                switch(action) {
                    case 'view':
                        viewCustomer(customerId, customerName);
                        break;
                    case 'edit':
                        editCustomer(customerId, customerName);
                        break;
                    case 'delete':
                        deleteCustomer(customerId, customerName);
                        break;
                }
            });
        });
    }
    
    function attachCheckboxListeners() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (selectAll) {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = !allChecked && anyChecked;
                }
            });
        });
    }
    
    // ========== VIEW CUSTOMER ==========
    function viewCustomer(id, name) {
        showNotification('info', `Loading customer: ${name}...`);
        // Implement view functionality
        console.log('View customer:', id, name);
    }
    
    // ========== EDIT CUSTOMER ==========
    function editCustomer(id, name) {
        showNotification('info', `Editing customer: ${name}...`);
        // Implement edit functionality
        console.log('Edit customer:', id, name);
    }
    
    // ========== DELETE CUSTOMER ==========
    function deleteCustomer(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            return;
        }
        
        const deleteBtn = document.querySelector(`button[data-id="${id}"][data-action="delete"]`);
        const originalHtml = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
        
        fetch('api/delete-customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', `"${name}" deleted successfully`);
                
                // Remove row with animation
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updatePaginationAfterDelete();
                        updateStats();
                    }, 300);
                }
            } else {
                throw new Error(data.message || 'Delete failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Delete failed: ' + error.message);
        })
        .finally(() => {
            deleteBtn.innerHTML = originalHtml;
            deleteBtn.disabled = false;
        });
    }
    
    // ========== BULK REMINDERS ==========
    function sendBulkReminders() {
        const selectedIds = getSelectedCustomerIds();
        
        if (selectedIds.length === 0) {
            showNotification('warning', 'Please select customers to send reminders');
            document.getElementById('sendReminder').checked = false;
            return;
        }
        
        fetch('api/bulk-customer-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'send-reminder',
                ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
            } else {
                throw new Error(data.message || 'Failed to send reminders');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to send reminders: ' + error.message);
        })
        .finally(() => {
            document.getElementById('sendReminder').checked = false;
        });
    }
    
    // ========== UPDATE STATISTICS ==========
    function updateStats() {
        fetch('api/get-stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.stats-paid').textContent = data.paidCount || 0;
                    document.querySelector('.stats-admitted').textContent = data.admittedCount || 0;
                }
            })
            .catch(error => console.error('Error updating stats:', error));
    }
    
    // ========== PAGINATION ==========
    function updatePagination(pagination) {
        if (!pagination) return;
        
        const paginationElement = document.querySelector('.pagination');
        if (!paginationElement) return;
        
        totalPages = pagination.total_pages;
        currentPage = pagination.current_page;
        
        let html = '';
        
        // Previous button
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        // Next button
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;
        
        paginationElement.innerHTML = html;
        
        // Add click handlers
        paginationElement.querySelectorAll('a.page-link[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page >= 1 && page <= totalPages && page !== currentPage) {
                    currentPage = page;
                    updateUrlParameter('page', currentPage);
                    loadTableData();
                }
            });
        });
    }
    
    function updateEntriesInfo(pagination) {
        const entriesElement = document.querySelector('.entries-info');
        if (!entriesElement || !pagination) return;
        
        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(start + pagination.per_page - 1, pagination.total);
        
        entriesElement.textContent = `Showing ${start}–${end} of ${pagination.total} entries`;
    }
    
    function updatePaginationAfterDelete() {
        const remainingRows = document.querySelectorAll('#customerTableBody tr').length;
        if (remainingRows === 0 && currentPage > 1) {
            currentPage--;
            loadTableData();
        }
    }
    
    // ========== UTILITY FUNCTIONS ==========
    function getSelectedCustomerIds() {
        const selectedIds = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });
        return selectedIds;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function getTierBadge(tier) {
        const badges = {
            'vip': 'warning',
            'gold': 'success',
            'silver': 'info',
            'new': 'primary'
        };
        return badges[tier?.toLowerCase()] || 'secondary';
    }
    
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    
    function updateUrlParameter(key, value) {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.pushState({}, '', url);
    }
    
    // ========== NOTIFICATION SYSTEM ==========
    function showNotification(type, message) {
        // Check if notification container exists
        let notificationContainer = document.getElementById('notificationContainer');
        
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notificationContainer';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(notificationContainer);
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.role = 'alert';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        notificationContainer.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
});
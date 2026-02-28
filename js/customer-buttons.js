/**
 * Customer Management Buttons Functionality
 * Handles View, Edit, Delete, Select All, Bulk Actions
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // ========== INITIALIZE MODALS ==========
    const viewModal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const toast = new bootstrap.Toast(document.getElementById('liveToast'));
    
    // ========== STATE MANAGEMENT ==========
    let currentCustomerId = null;
    let currentCustomerName = '';
    let selectedIds = [];
    
    // ========== VIEW BUTTON FUNCTIONALITY ==========
    document.querySelectorAll('.view-customer').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            viewCustomer(id, name);
        });
    });
    
    function viewCustomer(id, name) {
        if (!id) {
            showToast('Error', 'Invalid customer ID', 'error');
            return;
        }
        
        // Show modal with loading
        document.getElementById('viewModalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted">Loading customer details for ${escapeHtml(name)}...</p>
            </div>
        `;
        viewModal.show();
        
        // Fetch customer data
        fetch(`api/get-customer.php?id=${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayCustomerDetails(data.customer);
                } else {
                    throw new Error(data.message || 'Failed to load customer');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('viewModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Error loading customer: ${error.message}
                    </div>
                `;
            });
    }
    
    function displayCustomerDetails(customer) {
        const modalBody = document.getElementById('viewModalBody');
        
        // Format dates
        const createdDate = customer.created_at ? new Date(customer.created_at).toLocaleString() : 'N/A';
        const lastContacted = customer.last_contacted_at ? new Date(customer.last_contacted_at).toLocaleString() : 'Never';
        
        // Get badge classes
        const paymentBadge = {
            'paid': 'bg-success',
            'unpaid': 'bg-danger',
            'overdue': 'bg-danger',
            'partially paid': 'bg-warning'
        }[customer.payment_status] || 'bg-secondary';
        
        const statusBadge = {
            'pending': 'bg-warning',
            'processing': 'bg-primary',
            'finished': 'bg-success',
            'cancelled': 'bg-danger'
        }[customer.status] || 'bg-info';
        
        modalBody.innerHTML = `
            <div class="container-fluid">
                <!-- Customer Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fa fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">${escapeHtml(customer.full_name)}</h4>
                                <div>
                                    <span class="badge ${paymentBadge} me-2">${customer.payment_status}</span>
                                    <span class="badge ${statusBadge} me-2">${customer.status}</span>
                                    <span class="badge bg-${customer.tier === 'vip' ? 'warning' : 'info'}">${customer.tier.toUpperCase()}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Details Grid -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fa fa-address-card me-2"></i>Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Email:</td>
                                        <td><strong>${escapeHtml(customer.email || 'N/A')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td><strong>${escapeHtml(customer.phone || 'N/A')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Destination:</td>
                                        <td><strong>${escapeHtml(customer.destination || 'N/A')}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Admission:</td>
                                        <td><strong>${escapeHtml(customer.admission_status || 'pending')}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fa fa-chart-line me-2"></i>Progress</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="text-muted">Overall Progress</label>
                                    <div class="d-flex align-items-center">
                                        <span class="h4 mb-0 me-3">${customer.progress}%</span>
                                        <div class="progress flex-grow-1" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: ${customer.progress}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-muted">Refund Status</label>
                                    <div>
                                        <span class="badge ${customer.refund_flag ? 'bg-danger' : 'bg-secondary'}">
                                            ${customer.refund_flag ? 'Refund Requested' : 'No Refund'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fa fa-clock me-2"></i>Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Customer Since:</strong></p>
                                        <p>${createdDate}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Last Contacted:</strong></p>
                                        <p>${lastContacted}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // ========== EDIT BUTTON FUNCTIONALITY ==========
    document.querySelectorAll('.edit-customer').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            editCustomer(id, name);
        });
    });
    
    function editCustomer(id, name) {
        if (!id) {
            showToast('Error', 'Invalid customer ID', 'error');
            return;
        }
        
        currentCustomerId = id;
        currentCustomerName = name;
        
        // Show modal with loading
        document.getElementById('editModalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted">Loading edit form for ${escapeHtml(name)}...</p>
            </div>
        `;
        editModal.show();
        
        // Fetch customer data
        fetch(`api/get-customer.php?id=${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayEditForm(data.customer);
                } else {
                    throw new Error(data.message || 'Failed to load customer');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('editModalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Error loading customer: ${error.message}
                    </div>
                `;
                document.getElementById('editModalFooter').style.display = 'none';
            });
    }
    
    function displayEditForm(customer) {
        const modalBody = document.getElementById('editModalBody');
        const modalFooter = document.getElementById('editModalFooter');
        
        // Format datetime for input
        let lastContactedValue = '';
        if (customer.last_contacted_at) {
            const date = new Date(customer.last_contacted_at);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            lastContactedValue = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
        
        modalBody.innerHTML = `
            <form id="editCustomerForm">
                <input type="hidden" name="id" value="${customer.id}">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="${escapeHtml(customer.full_name)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="${escapeHtml(customer.email)}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" 
                               value="${escapeHtml(customer.phone)}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" class="form-control" name="destination" 
                               value="${escapeHtml(customer.destination)}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="pending" ${customer.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="processing" ${customer.status === 'processing' ? 'selected' : ''}>Processing</option>
                            <option value="finished" ${customer.status === 'finished' ? 'selected' : ''}>Finished</option>
                            <option value="cancelled" ${customer.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status">
                            <option value="unpaid" ${customer.payment_status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
                            <option value="partially paid" ${customer.payment_status === 'partially paid' ? 'selected' : ''}>Partially Paid</option>
                            <option value="paid" ${customer.payment_status === 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="overdue" ${customer.payment_status === 'overdue' ? 'selected' : ''}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Admission Status</label>
                        <select class="form-select" name="admission_status">
                            <option value="pending" ${customer.admission_status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="admitted" ${customer.admission_status === 'admitted' ? 'selected' : ''}>Admitted</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Progress (%)</label>
                        <input type="number" class="form-control" name="progress" 
                               min="0" max="100" value="${customer.progress}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tier</label>
                        <select class="form-select" name="tier">
                            <option value="new" ${customer.tier === 'new' ? 'selected' : ''}>New</option>
                            <option value="silver" ${customer.tier === 'silver' ? 'selected' : ''}>Silver</option>
                            <option value="gold" ${customer.tier === 'gold' ? 'selected' : ''}>Gold</option>
                            <option value="vip" ${customer.tier === 'vip' ? 'selected' : ''}>VIP</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Last Contacted</label>
                        <input type="datetime-local" class="form-control" name="last_contacted_at" 
                               value="${lastContactedValue}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="refund_flag" 
                                   id="refundFlag" value="1" ${customer.refund_flag ? 'checked' : ''}>
                            <label class="form-check-label" for="refundFlag">Mark for Refund</label>
                        </div>
                    </div>
                </div>
            </form>
        `;
        
        modalFooter.style.display = 'block';
    }
    
    // Save Edit Button
    document.getElementById('saveEditBtn').addEventListener('click', function() {
        saveCustomerEdit();
    });
    
    function saveCustomerEdit() {
        const form = document.getElementById('editCustomerForm');
        const formData = new FormData(form);
        
        // Show loading state
        const saveBtn = document.getElementById('saveEditBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Saving...';
        saveBtn.disabled = true;
        
        fetch('api/update-customer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', 'Customer updated successfully', 'success');
                editModal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Update failed: ' + error.message, 'error');
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    // ========== DELETE BUTTON FUNCTIONALITY ==========
    document.querySelectorAll('.delete-customer').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            showDeleteConfirmation(id, name);
        });
    });
    
    function showDeleteConfirmation(id, name) {
        currentCustomerId = id;
        currentCustomerName = name;
        
        document.getElementById('deleteModalBody').innerHTML = `
            <p>Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?</p>
            <p class="text-danger mb-0"><small>This action cannot be undone. All associated records will be deleted.</small></p>
        `;
        
        deleteModal.show();
    }
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentCustomerId) {
            performDelete(currentCustomerId, currentCustomerName);
        }
    });
    
    function performDelete(id, name) {
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Deleting...';
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
                deleteModal.hide();
                showToast('Success', `"${name}" deleted successfully`, 'success');
                
                // Remove row with animation
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        row.remove();
                        updateStats();
                        updateSelectedCount();
                    }, 300);
                }
            } else {
                throw new Error(data.message || 'Delete failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Delete failed: ' + error.message, 'error');
        })
        .finally(() => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        });
    }
    
    // ========== SELECT ALL FUNCTIONALITY ==========
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectAllState();
            updateSelectedCount();
        });
    });
    
    function updateSelectAllState() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        
        if (selectAll) {
            selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }
    
    function updateSelectedCount() {
        selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(cb => cb.value);
        
        const selectedCountEl = document.getElementById('selectedCount');
        if (selectedCountEl) {
            selectedCountEl.textContent = selectedIds.length === 0 ? '0 selected' : `${selectedIds.length} selected`;
        }
    }
    
    // ========== BULK ACTIONS ==========
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', function() {
            const action = document.getElementById('bulkActionSelect').value;
            
            if (!action) {
                showToast('Warning', 'Please select an action', 'warning');
                return;
            }
            
            if (selectedIds.length === 0) {
                showToast('Warning', 'Please select at least one customer', 'warning');
                return;
            }
            
            let confirmMessage = '';
            switch(action) {
                case 'delete':
                    confirmMessage = `Are you sure you want to delete ${selectedIds.length} customer(s)?`;
                    break;
                case 'send-reminder':
                    confirmMessage = `Send reminders to ${selectedIds.length} customer(s)?`;
                    break;
                case 'mark-followup':
                    confirmMessage = `Mark ${selectedIds.length} customer(s) for follow-up?`;
                    break;
            }
            
            if (!confirm(confirmMessage)) return;
            
            // Show loading
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Processing...';
            btn.disabled = true;
            
            fetch('api/bulk-customer-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Action failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Action failed: ' + error.message, 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
    
    // ========== SEND REMINDER BUTTON ==========
    const sendReminderBtn = document.getElementById('sendReminderBtn');
    if (sendReminderBtn) {
        sendReminderBtn.addEventListener('click', function() {
            if (selectedIds.length === 0) {
                showToast('Warning', 'Please select customers to send reminders', 'warning');
                return;
            }
            
            if (!confirm(`Send reminders to ${selectedIds.length} selected customer(s)?`)) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Sending...';
            btn.disabled = true;
            
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
                    showToast('Success', data.message, 'success');
                } else {
                    throw new Error(data.message || 'Failed to send reminders');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to send reminders: ' + error.message, 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
    
    // ========== SEARCH FUNCTIONALITY ==========
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            performSearch();
        });
    }
    
    const searchInput = document.getElementById('searchCustomer');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    function performSearch() {
        const searchTerm = document.getElementById('searchCustomer').value;
        const url = new URL(window.location);
        url.searchParams.set('search', searchTerm);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
    
    // ========== UPDATE STATS ==========
    function updateStats() {
        fetch('api/get-stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const paidEl = document.getElementById('paidCount');
                    const admittedEl = document.getElementById('admittedCount');
                    const pendingEl = document.getElementById('pendingAdmissionCount');
                    const unpaidEl = document.getElementById('unpaidCount');
                    
                    if (paidEl) paidEl.textContent = data.paidCount || 0;
                    if (admittedEl) admittedEl.textContent = data.admittedCount || 0;
                    if (pendingEl) pendingEl.textContent = data.pendingAdmissionCount || 0;
                    if (unpaidEl) unpaidEl.textContent = data.unpaidCount || 0;
                }
            })
            .catch(error => console.error('Error updating stats:', error));
    }
    
    // ========== TOAST NOTIFICATION ==========
    function showToast(title, message, type = 'info') {
        const toastEl = document.getElementById('liveToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        
        if (!toastEl || !toastTitle || !toastMessage || !toastIcon) return;
        
        // Set icon and color based on type
        switch(type) {
            case 'success':
                toastIcon.className = 'fa fa-check-circle text-success me-2';
                break;
            case 'error':
                toastIcon.className = 'fa fa-exclamation-circle text-danger me-2';
                break;
            case 'warning':
                toastIcon.className = 'fa fa-exclamation-triangle text-warning me-2';
                break;
            default:
                toastIcon.className = 'fa fa-info-circle text-info me-2';
        }
        
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        
        toast.show();
    }
    
    // ========== UTILITY FUNCTIONS ==========
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize selected count
    updateSelectedCount();
});
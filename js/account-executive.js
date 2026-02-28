// Customer Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // ========== SELECT ALL CHECKBOXES ==========
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
        
        // Update "select all" when individual checkboxes change
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && anyChecked;
            });
        });
    }
    
    // ========== CUSTOMER ACTION BUTTONS ==========
    const actionButtons = document.querySelectorAll('.js-customer-action');
    
        // Use event delegation for action buttons so dynamically added rows still work
        document.addEventListener('click', function (evt) {
            const btn = evt.target.closest && evt.target.closest('.js-customer-action');
            if (!btn) return;
            evt.preventDefault();

            (async function () {
                try {
                    const action = btn.dataset.action || '';
                    const customerId = btn.dataset.id || '';
                    const customerName = btn.dataset.name || 'Customer';
                    const row = btn.closest('tr');

                    if (action === 'view-customer' || action === 'edit-customer') {
                        const modal = document.getElementById('customerModal');
                        if (!modal) { alert('Customer modal not found.'); return; }

                        const idInput = document.getElementById('customerModalId');
                        const nameInput = document.getElementById('customerModalName');
                        const destInput = document.getElementById('customerModalDestination');
                        const paymentSelect = document.getElementById('customerModalPayment');
                        const progressInput = document.getElementById('customerModalProgress');
                        const statusSelect = document.getElementById('customerModalStatus');
                        const editBtn = document.getElementById('customerModalEditBtn');
                        const saveBtn = document.getElementById('customerModalSaveBtn');

                        idInput.value = customerId;
                        nameInput.value = customerName;
                        destInput.value = row ? (row.cells[3] ? row.cells[3].innerText.trim() : '') : '';
                        paymentSelect.value = row ? (row.dataset.paymentStatus || '') : '';
                        progressInput.value = row ? (row.dataset.progress || 0) : 0;
                        statusSelect.value = row ? (row.dataset.status || '') : '';

                        [nameInput, destInput, progressInput].forEach(i => i && (i.readOnly = true));
                        [paymentSelect, statusSelect].forEach(s => s && (s.disabled = true));
                        editBtn.classList.remove('d-none');
                        saveBtn.classList.add('d-none');

                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();

                        editBtn.onclick = function () {
                            [nameInput, destInput, progressInput].forEach(i => i && (i.readOnly = false));
                            [paymentSelect, statusSelect].forEach(s => s && (s.disabled = false));
                            editBtn.classList.add('d-none');
                            saveBtn.classList.remove('d-none');
                        };

                        saveBtn.onclick = async function () {
                            const payload = {
                                customer_id: idInput.value,
                                name: nameInput.value.trim(),
                                destination: destInput.value.trim(),
                                payment: paymentSelect.value,
                                progress: parseInt(progressInput.value || '0', 10),
                                status: statusSelect.value
                            };

                            try {
                                const result = await postAjax('edit-customer', payload);
                                alert(result.message || 'Customer updated.');
                                if (row) {
                                    row.cells[2].innerText = payload.name;
                                    row.cells[3].innerText = payload.destination;
                                    row.dataset.paymentStatus = payload.payment;
                                    row.dataset.progress = String(payload.progress);
                                    row.dataset.status = payload.status;
                                    const progressCell = row.cells[6];
                                    if (progressCell) {
                                        progressCell.innerHTML = payload.progress + '%\\n' +
                                            '<div class="progress">' +
                                            '<div class="progress-bar bg-success" style="width:' + payload.progress + '%"></div>' +
                                            '</div>';
                                    }
                                    const statusCell = row.cells[7];
                                    if (statusCell) {
                                        const badgeClass = payload.status === 'finished' ? 'bg-success' : (payload.status === 'processing' ? 'bg-primary' : (payload.status === 'cancelled' ? 'bg-danger' : 'bg-secondary'));
                                        statusCell.innerHTML = '<span class="badge rounded-pill ' + badgeClass + '">' + payload.status + '</span>';
                                    }
                                }
                            } catch (err) {
                                alert(err.message || 'Update failed');
                            } finally {
                                try { bsModal.hide(); } catch (e) {}
                            }
                        };

                        return;
                    }

                    if (action === 'delete-customer') {
                        if (!window.confirm('Delete customer #' + customerId + '?')) return;
                        try {
                            const result = await postAjax(action, { customer_id: customerId });
                            alert(result.message || 'Customer deleted.');
                            if (row && row.parentNode) row.parentNode.removeChild(row);
                        } catch (error) {
                            alert(error.message || 'Delete failed');
                        }
                        return;
                    }
                } catch (e) {
                    console.error('Action handler error', e);
                    alert('An error occurred. See console for details.');
                }
            })();
        });
    
    // ========== VIEW CUSTOMER FUNCTION ==========
    function viewCustomer(id, name) {
        console.log(`Viewing customer: ${name} (ID: ${id})`);
        
        // Option 1: Redirect to customer detail page
        // window.location.href = `/customer/view.php?id=${id}`;
        
        // Option 2: Open in modal (if you have a modal system)
        showCustomerModal('view', id, name);
        
        // Option 3: Fetch and display customer data
        fetchCustomerData('view', id, name);
    }
    
    // ========== EDIT CUSTOMER FUNCTION ==========
    function editCustomer(id, name) {
        console.log(`Editing customer: ${name} (ID: ${id})`);
        
        // Option 1: Redirect to edit page
        // window.location.href = `/customer/edit.php?id=${id}`;
        
        // Option 2: Open edit modal
        showCustomerModal('edit', id, name);
        
        // Option 3: Fetch and populate edit form
        fetchCustomerData('edit', id, name);
    }
    
    // ========== DELETE CUSTOMER FUNCTION ==========
    function deleteCustomer(id, name) {
        console.log(`Attempting to delete customer: ${name} (ID: ${id})`);
        
        // Show confirmation dialog
        if (confirm(`Are you sure you want to delete customer "${name}"? This action cannot be undone.`)) {
            
            // Show loading state
            const deleteBtn = document.querySelector(`button[data-id="${id}"][data-action="delete-customer"]`);
            const originalHtml = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            deleteBtn.disabled = true;
            
            // Make AJAX request to delete customer
            fetch('/api/delete-customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    name: name
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from table
                    const customerRow = document.querySelector(`tr[data-id="${id}"]`);
                    if (customerRow) {
                        customerRow.remove();
                    }
                    
                    // Show success message
                    showNotification('success', `Customer "${name}" deleted successfully`);
                    
                    // Update select all checkbox if needed
                    updateSelectAllState();
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', `Failed to delete customer: ${error.message}`);
                
                // Restore button state
                deleteBtn.innerHTML = originalHtml;
                deleteBtn.disabled = false;
            });
        }
    }
    
    // ========== FETCH CUSTOMER DATA ==========
    function fetchCustomerData(action, id, name) {
        // Show loading indicator
        showNotification('info', `Loading ${name}...`);
        
        fetch(`/api/get-customer.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'view') {
                        displayCustomerDetails(data.customer);
                    } else if (action === 'edit') {
                        populateEditForm(data.customer);
                    }
                } else {
                    throw new Error(data.message || 'Failed to load customer data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', `Failed to load customer data: ${error.message}`);
            });
    }
    
    // ========== MODAL HANDLING ==========
    function showCustomerModal(action, id, name) {
        // Check if modal container exists, if not create it
        let modalContainer = document.getElementById('customerModal');
        
        if (!modalContainer) {
            modalContainer = document.createElement('div');
            modalContainer.id = 'customerModal';
            modalContainer.className = 'modal fade';
            modalContainer.setAttribute('tabindex', '-1');
            modalContainer.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modalContainer);
        }
        
        // Set modal title based on action
        const modalTitle = modalContainer.querySelector('.modal-title');
        modalTitle.textContent = action === 'view' ? `View Customer: ${name}` : `Edit Customer: ${name}`;
        
        // Show modal
        const modal = new bootstrap.Modal(modalContainer);
        modal.show();
        
        // Load content based on action
        loadModalContent(action, id);
    }
    
    function loadModalContent(action, id) {
        const modalBody = document.querySelector('#customerModal .modal-body');
        
        // Fetch content for modal
        fetch(`/api/customer-modal-content.php?action=${action}&id=${id}`)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
                
                // Initialize any form handlers if in edit mode
                if (action === 'edit') {
                    initializeEditForm();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load customer data</div>';
            });
    }
    
    // ========== PAYMENT FILTER DROPDOWN ==========
    // Handle payment filter links only (avoid row-level data-payment attributes)
    const paymentLinks = document.querySelectorAll('.payment-header-dropdown .dropdown-item[data-payment]');
    
    paymentLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove active class from all links
            paymentLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Navigate using server-rendered filter URL from the clicked dropdown item
            if (this.href) {
                window.location.href = this.href;
            }
        });
    });
    
    // ========== TABLE DATA RELOAD ==========
    function loadTableData() {
        // This module currently uses server-rendered table responses.
        // Fallback: refresh the page instead of calling a missing API endpoint.
        window.location.reload();
    }
    
    function initializeTableListeners() {
        // Re-attach checkbox listeners
        // Re-attach button listeners
        // This function would reinitialize all event listeners
        // You might want to call your DOMContentLoaded logic here
    }
    
    // ========== BULK ACTIONS ==========
    // Add bulk action buttons handler
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    if (bulkActionBtn) {
        bulkActionBtn.addEventListener('click', function() {
            const selectedIds = getSelectedCustomerIds();
            
            if (selectedIds.length === 0) {
                showNotification('warning', 'Please select at least one customer');
                return;
            }
            
            const action = document.getElementById('bulkActionSelect').value;
            
            if (!action) {
                showNotification('warning', 'Please select an action');
                return;
            }
            
            performBulkAction(action, selectedIds);
        });
    }
    
    function getSelectedCustomerIds() {
        const selectedIds = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row) {
                selectedIds.push(row.dataset.id);
            }
        });
        return selectedIds;
    }
    
    function performBulkAction(action, ids) {
        if (!confirm(`Are you sure you want to ${action} ${ids.length} customer(s)?`)) {
            return;
        }
        
        fetch('/api/bulk-customer-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                ids: ids
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', `Bulk action completed successfully`);
                loadTableData();
            } else {
                throw new Error(data.message || 'Bulk action failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', `Bulk action failed: ${error.message}`);
        });
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
    
    function updateSelectAllState() {
        if (selectAllCheckbox) {
            const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && anyChecked;
        }
    }
    
    // Helper functions for customer data display (you can expand these)
    function displayCustomerDetails(customer) {
        console.log('Displaying customer details:', customer);
        // Implement customer details display logic
    }
    
    function populateEditForm(customer) {
        console.log('Populating edit form with:', customer);
        // Implement edit form population logic
    }
    
    function initializeEditForm() {
        console.log('Initializing edit form handlers');
        // Implement edit form initialization logic
    }
    
    // ========== INITIALIZE ==========
    // Make sure Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap JS is not loaded. Modal functionality may not work.');
    }
});

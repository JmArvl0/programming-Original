document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalActionBtn = document.getElementById('modalActionBtn');
    
    // Animate progress bars and charts
    function animateCharts() {
        // Animate donut chart
        const donutChart = document.querySelector('.donut-chart-progress');
        if (donutChart) {
            // Get percentage from inline style or data attribute
            const styleAttr = donutChart.getAttribute('style') || '';
            const match = styleAttr.match(/--percentage:\s*(\d+)/);
            const percentage = match ? parseInt(match[1]) : (parseInt(donutChart.getAttribute('data-percentage')) || 0);
            donutChart.style.setProperty('--percentage', percentage);
        }
        
        // Animate progress bars
        document.querySelectorAll('.progress-fill-custom').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 300);
        });
        
        // Animate rating bars
        const ratingBars = document.querySelectorAll('.rating-bar');
        ratingBars.forEach((bar, index) => {
            const height = bar.style.height;
            bar.style.height = '0%';
            setTimeout(() => {
                bar.style.height = height;
            }, 300 + (index * 100));
        });
    }
    
    // Dashboard-specific JavaScript
    
    // View button functionality
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click from firing
            const name = this.getAttribute('data-name');
            const type = this.getAttribute('data-type');
            const status = this.getAttribute('data-status');
            const deadline = this.getAttribute('data-deadline');
            
            modalTitle.textContent = `View Details: ${name}`;
            modalContent.innerHTML = `
                <div class="mb-3">
                    <strong>Name:</strong> ${name}
                </div>
                <div class="mb-3">
                    <strong>Type:</strong> ${type}
                </div>
                <div class="mb-3">
                    <strong>Status:</strong> <span class="badge bg-warning">${status}</span>
                </div>
                <div class="mb-3">
                    <strong>Deadline:</strong> ${deadline}
                </div>
                <div class="alert alert-warning">
                    <small>⚠️ This item requires immediate attention. Please review and take appropriate action.</small>
                </div>
            `;
            
            modalActionBtn.textContent = 'Mark as Reviewed';
            modalActionBtn.className = 'btn btn-primary';
            modalActionBtn.onclick = function() {
                alert(`Marked "${name}" as reviewed`);
                detailsModal.hide();
            };
            
            detailsModal.show();
        });
    });
    
    // Remind button functionality
    document.querySelectorAll('.remind-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const name = this.getAttribute('data-name');
            const departure = this.getAttribute('data-departure');
            
            modalTitle.textContent = `Send Reminder`;
            modalContent.innerHTML = `
                <div class="mb-3">
                    <strong>Traveler:</strong> ${name}
                </div>
                <div class="mb-3">
                    <strong>Departure:</strong> ${departure}
                </div>
                <div class="form-group mb-3">
                    <label for="reminderMessage" class="form-label">Custom Message (optional):</label>
                    <textarea class="form-control" id="reminderMessage" rows="3" placeholder="Add a custom message..."></textarea>
                </div>
                <div class="alert alert-info">
                    <small>📧 A reminder email and SMS will be sent to the traveler.</small>
                </div>
            `;
            
            modalActionBtn.textContent = 'Send Reminder';
            modalActionBtn.className = 'btn btn-warning';
            modalActionBtn.onclick = function() {
                const message = document.getElementById('reminderMessage').value;
                alert(`Reminder sent to ${name}${message ? ' with message: ' + message : ''}`);
                detailsModal.hide();
            };
            
            detailsModal.show();
        });
    });
    
    // Clickable row functionality
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const name = this.cells[0].textContent;
            
            if (action === 'view') {
                // Simulate clicking the view button
                const viewBtn = this.querySelector('.view-btn');
                if (viewBtn) viewBtn.click();
            } else if (action === 'remind') {
                // Simulate clicking the remind button
                const remindBtn = this.querySelector('.remind-btn');
                if (remindBtn) remindBtn.click();
            } else if (action === 'track') {
                // Show tracking info
                modalTitle.textContent = `Track Operation: ${name}`;
                modalContent.innerHTML = `
                    <div class="mb-3">
                        <strong>Customer:</strong> ${name}
                    </div>
                    <div class="mb-3">
                        <strong>Staff On-Duty:</strong> ${this.cells[1].textContent}
                    </div>
                    <div class="mb-3">
                        <strong>Type:</strong> ${this.cells[2].textContent}
                    </div>
                    <div class="alert alert-success">
                        <small>✅ Operation is currently active and being tracked.</small>
                    </div>
                    <div id="liveStatus" class="text-center mt-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading live status...</span>
                        </div>
                        <p class="mt-2">Fetching real-time location...</p>
                    </div>
                `;
                
                // Simulate fetching live data
                setTimeout(() => {
                    document.getElementById('liveStatus').innerHTML = `
                        <div class="text-success mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                        </div>
                        <p><strong>Location:</strong> Downtown Area</p>
                        <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                        <p><strong>ETA:</strong> 15 minutes</p>
                    `;
                }, 1500);
                
                modalActionBtn.textContent = 'Open Full Tracking';
                modalActionBtn.className = 'btn btn-primary';
                modalActionBtn.onclick = function() {
                    alert(`Opening full tracking for ${name}`);
                    detailsModal.hide();
                };
                
                detailsModal.show();
            }
        });
    });
    
    // Refresh Map Button
    document.getElementById('refreshMapBtn')?.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.textContent;
        
        btn.textContent = 'Refreshing...';
        btn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Generate new random operations
            const operationsTable = document.querySelector('.operations-table tbody');
            if (operationsTable) {
                // Clear and add new random data
                operationsTable.innerHTML = '';
                const staffNames = ['J. Rizz', 'M. Manuel', 'J. Paul', 'A. Rodriguez', 'S. Johnson', 'P. Martinez'];
                const operationTypes = ['Transport', 'Accommodation', 'Tour Guide', 'Logistics'];
                
                for (let i = 0; i < 3; i++) {
                    const randomName = generateRandomName();
                    const row = document.createElement('tr');
                    row.className = 'clickable-row';
                    row.setAttribute('data-action', 'track');
                    row.innerHTML = `
                        <td>${randomName}</td>
                        <td>${staffNames[Math.floor(Math.random() * staffNames.length)]}</td>
                        <td>${operationTypes[Math.floor(Math.random() * operationTypes.length)]}</td>
                    `;
                    operationsTable.appendChild(row);
                }
                
                // Reattach click events to new rows
                attachRowClickEvents();
            }
            
            btn.textContent = originalText;
            btn.disabled = false;
            alert('Map locations refreshed with new data!');
        }, 1000);
    });
    
    // Helper function to generate random names (client-side)
    function generateRandomName() {
        const firstNames = window.dashboardData?.firstNames || ['John', 'Jane', 'Robert', 'Maria', 'Michael', 'Sarah', 'David', 'Lisa', 'James', 'Jennifer'];
        const lastNames = window.dashboardData?.lastNames || ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        return firstNames[Math.floor(Math.random() * firstNames.length)] + ' ' + 
               lastNames[Math.floor(Math.random() * lastNames.length)];
    }
    
    // Function to reattach click events
    function attachRowClickEvents() {
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const name = this.cells[0].textContent;
                
                if (action === 'track') {
                    modalTitle.textContent = `Track Operation: ${name}`;
                    modalContent.innerHTML = `
                        <div class="alert alert-success">
                            Newly refreshed data for ${name}
                        </div>
                        <div class="mb-3">
                            <strong>Customer:</strong> ${name}
                        </div>
                        <div class="mb-3">
                            <strong>Staff On-Duty:</strong> ${this.cells[1].textContent}
                        </div>
                        <div class="mb-3">
                            <strong>Type:</strong> ${this.cells[2].textContent}
                        </div>
                    `;
                    modalActionBtn.textContent = 'Track Location';
                    detailsModal.show();
                }
            });
        });
    }
    
    // Make tables responsive on mobile
    function handleResponsiveTables() {
        const tableResponsives = document.querySelectorAll('.table-responsive');
        tableResponsives.forEach(table => {
            if (window.innerWidth < 768) {
                table.style.overflowX = 'auto';
            } else {
                table.style.overflowX = 'visible';
            }
        });
    }
    
    // Initial setup
    animateCharts();
    handleResponsiveTables();
    
    // Update on resize
    window.addEventListener('resize', function() {
        handleResponsiveTables();
    });
});

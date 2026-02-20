

// Navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle navigation clicks
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            // Add active class to clicked link
            this.classList.add('active');
        });
    });

    // Handle table row selection
    const checkboxes = document.querySelectorAll('table input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.style.backgroundColor = '#e0f2fe';
            } else {
                row.style.backgroundColor = '';
            }
        });
    });

    // Handle "Select All" checkbox in table headers
    const headerCheckboxes = document.querySelectorAll('thead input[type="checkbox"]');
    headerCheckboxes.forEach(headerCheckbox => {
        headerCheckbox.addEventListener('change', function() {
            const table = this.closest('table');
            const rowCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest('tr');
                row.style.backgroundColor = this.checked ? '#e0f2fe' : '';
            });
        });
    });

    // Handle action buttons
    const actionLinks = document.querySelectorAll('.action-link');
    actionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.textContent.trim();
            const row = this.closest('tr');
            const name = row.querySelector('td:first-child')?.textContent || 'Customer';
            
            // Show alert or handle action
            if (action === 'View' || action === 'Review Document') {
                alert(`Viewing details for: ${name}`);
            } else if (action === 'Remind') {
                alert(`Sending reminder to: ${name}`);
            }
        });
    });

    // Calendar functionality
    const calendarDays = document.querySelectorAll('.calendar-day:not(.calendar-day-name)');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            // Remove selected class from all days
            calendarDays.forEach(d => d.classList.remove('selected'));
            // Add selected class to clicked day
            this.classList.add('selected');
            
            // Update selected date details if exists
            const dateText = this.textContent.trim();
            updateSelectedDate(dateText);
        });
    });

    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.table-section')?.querySelector('table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });

    // Filter button functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Filter options will be available here');
        });
    });

    // Notification icons
    const notificationIcons = document.querySelectorAll('.notification-icon');
    notificationIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            alert('You have new notifications');
        });
    });

    // Profile click
    const profilePics = document.querySelectorAll('.profile-pic, .user-profile');
    profilePics.forEach(pic => {
        pic.addEventListener('click', function() {
            alert('Profile menu');
        });
    });

    // Main action buttons
    const mainButtons = document.querySelectorAll('.btn-primary, .btn-success, .btn-warning');
    mainButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const buttonText = this.textContent.trim();
            if (buttonText === 'Request all Information') {
                alert('Requesting information from all selected customers...');
            } else if (buttonText === 'Send Payment Reminder') {
                alert('Sending payment reminders to selected customers...');
            } else {
                alert(`Action: ${buttonText}`);
            }
        });
    });

    // Update progress bars animation
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width || bar.getAttribute('data-width') || '0%';
        bar.style.width = width;
    });

    // Animate progress bars on page load
    setTimeout(() => {
        progressBars.forEach(bar => {
            const computedWidth = window.getComputedStyle(bar).width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = computedWidth;
            }, 100);
        });
    }, 200);

    // Handle table row click (select row when clicking anywhere on it)
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on a link or button
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') {
                return;
            }
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });
});

// Update selected date details
function updateSelectedDate(date) {
    const dateDetails = document.querySelector('.selected-date-details');
    if (dateDetails) {
        const dateText = dateDetails.querySelector('.date-text');
        if (dateText) {
            dateText.textContent = `September ${date}, 2025`;
        }
    }
}

// Initialize charts (placeholder for real chart library)
function initCharts() {
    // This would be replaced with actual chart library like Chart.js
    console.log('Charts initialized');
}

// Export functions for use in other scripts
window.travelApp = {
    updateSelectedDate,
    initCharts
};


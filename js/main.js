

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

    const messagesBadge = document.getElementById('headerMessagesBadge');
    const notificationsBadge = document.getElementById('headerNotificationsBadge');
    const messagesCountLabel = document.getElementById('headerMessagesCountLabel');
    const notificationsCountLabel = document.getElementById('headerNotificationsCountLabel');
    const markAllMessagesReadHeaderBtn = document.getElementById('markAllMessagesReadHeaderBtn');
    const markAllMessagesReadPageBtn = document.getElementById('markAllMessagesReadBtn');
    const markAllNotificationsReadBtn = document.getElementById('markAllNotificationsReadBtn');
    const messagesList = document.getElementById('headerMessagesList');
    const notificationsList = document.getElementById('headerNotificationsList');

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setBadgeCount(el, count) {
        if (!el) {
            return;
        }
        const safe = Number.isFinite(count) ? Math.max(0, count) : 0;
        el.textContent = String(safe);
        if (safe > 0) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    }

    async function callMessagesAjax(action, payload) {
        const url = new URL(window.location.href);
        url.pathname = url.pathname.replace(/[^/]*$/, 'messages.php');
        url.search = '';
        url.searchParams.set('ajax', action);
        const response = await fetch(url.toString(), {
            method: payload ? 'POST' : 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : undefined
        });
        if (!response.ok) {
            throw new Error('Request failed');
        }
        return response.json();
    }

    async function refreshHeaderCounts() {
        try {
            const result = await callMessagesAjax('header-summary');
            if (!result || !result.ok) {
                return;
            }
            const messagesUnread = Number(result.messages_unread || 0);
            const notificationsUnread = Number(result.notifications_unread || 0);

            setBadgeCount(messagesBadge, messagesUnread);
            setBadgeCount(notificationsBadge, notificationsUnread);
            if (messagesCountLabel) {
                messagesCountLabel.textContent = String(messagesUnread);
            }
            if (notificationsCountLabel) {
                notificationsCountLabel.textContent = String(notificationsUnread);
            }
        } catch (error) {
            // Ignore polling failures to keep UX stable.
        }
    }

    async function refreshRecentMessages() {
        if (!messagesList) {
            return;
        }
        try {
            const result = await callMessagesAjax('recent-messages');
            if (!result || !result.ok) {
                return;
            }
            const items = Array.isArray(result.items) ? result.items : [];
            if (!items.length) {
                messagesList.innerHTML = '<div class="dropdown-item-text text-muted small">No recent messages.</div>';
                return;
            }

            messagesList.innerHTML = items.map((item) => {
                const senderRole = escapeHtml((item.sender_role || '').toUpperCase());
                const moduleOrigin = escapeHtml(item.module_origin || 'General');
                const messageText = escapeHtml(item.message_text || '');
                const isUnread = Number(item.is_read || 0) === 0;
                return `
                    <div class="dropdown-item-text border-bottom py-2">
                        <div class="d-flex justify-content-between">
                            <small class="fw-semibold text-uppercase">${senderRole}</small>
                            ${isUnread ? '<small class="badge bg-danger">New</small>' : ''}
                        </div>
                        <div class="small text-muted">${moduleOrigin}</div>
                        <div class="small">${messageText}</div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            // Ignore dropdown refresh failures.
        }
    }

    async function refreshRecentNotifications() {
        if (!notificationsList) {
            return;
        }
        try {
            const result = await callMessagesAjax('recent-notifications');
            if (!result || !result.ok) {
                return;
            }

            const grouped = result.items && typeof result.items === 'object' ? result.items : {};
            const modules = Object.keys(grouped);
            if (!modules.length) {
                notificationsList.innerHTML = '<div class="dropdown-item-text text-muted small">No recent notifications.</div>';
                return;
            }

            notificationsList.innerHTML = modules.map((moduleName) => {
                const items = Array.isArray(grouped[moduleName]) ? grouped[moduleName] : [];
                const moduleSafe = escapeHtml(moduleName);
                const moduleLink = moduleName.toLowerCase() === 'passport' || moduleName.toLowerCase() === 'passport visa'
                    ? 'passport_visa.php'
                    : moduleName.toLowerCase() === 'crm'
                        ? 'crm.php'
                        : moduleName.toLowerCase() === 'schedule'
                            ? 'schedule_rates.php'
                            : moduleName.toLowerCase() === 'facilities'
                                ? 'facilities.php'
                                : moduleName.toLowerCase() === 'financial'
                                    ? 'account_executive.php'
                                    : 'index.php';

                const links = items.map((item) => {
                    const message = escapeHtml(item.message || '');
                    return `<a href="${moduleLink}" class="d-block small text-decoration-none mb-1">${message}</a>`;
                }).join('');

                return `
                    <div class="dropdown-item-text border-bottom py-2">
                        <div class="small fw-semibold mb-1">${moduleSafe}</div>
                        ${links}
                    </div>
                `;
            }).join('');
        } catch (error) {
            // Ignore dropdown refresh failures.
        }
    }

    async function markAllMessagesRead() {
        try {
            const result = await callMessagesAjax('mark-all-messages-read', {});
            if (result && result.ok) {
                await refreshHeaderCounts();
                await refreshRecentMessages();
                if (window.location.pathname.toLowerCase().endsWith('/messages.php')) {
                    window.location.reload();
                }
            }
        } catch (error) {
            alert('Unable to mark all messages as read.');
        }
    }

    async function markAllNotificationsRead() {
        try {
            const result = await callMessagesAjax('mark-all-notifications-read', {});
            if (result && result.ok) {
                await refreshHeaderCounts();
                await refreshRecentNotifications();
            }
        } catch (error) {
            alert('Unable to mark all notifications as read.');
        }
    }

    if (markAllMessagesReadHeaderBtn) {
        markAllMessagesReadHeaderBtn.addEventListener('click', function () {
            markAllMessagesRead();
        });
    }
    if (markAllMessagesReadPageBtn) {
        markAllMessagesReadPageBtn.addEventListener('click', function () {
            markAllMessagesRead();
        });
    }
    if (markAllNotificationsReadBtn) {
        markAllNotificationsReadBtn.addEventListener('click', function () {
            markAllNotificationsRead();
        });
    }

    const messagesDropdownBtn = document.getElementById('messagesDropdownBtn');
    if (messagesDropdownBtn) {
        messagesDropdownBtn.addEventListener('click', function () {
            refreshRecentMessages();
        });
    }

    const notificationsDropdownBtn = document.getElementById('notificationsDropdownBtn');
    if (notificationsDropdownBtn) {
        notificationsDropdownBtn.addEventListener('click', function () {
            refreshRecentNotifications();
        });
    }

    refreshHeaderCounts();
    setInterval(refreshHeaderCounts, 45000);
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


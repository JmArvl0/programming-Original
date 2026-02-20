document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('table.table tbody tr'));
    if (!rows.length) return;

    const selectAll = document.getElementById('selectAll');
    const sendBtn = document.getElementById('sendPaymentReminderBtn');
    const searchInput = document.querySelector('.search-input');
    const filterSelect = document.querySelector('.filter-select');
    const tabs = Array.from(document.querySelectorAll('#aeStatusTabs .nav-link'));
    const paymentDropdownItems = Array.from(document.querySelectorAll('.payment-header-dropdown .dropdown-item'));
    const statusDropdownItems = Array.from(document.querySelectorAll('.status-header-dropdown .dropdown-item'));
    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');
    const paginationEl = document.getElementById('tablePagination');

    const pageSize = 10;
    let currentPage = 1;
    let activeTab = 'all';
    let headerPaymentFilter = 'all';
    let headerStatusFilter = 'all';

    const now = new Date();
    now.setHours(0, 0, 0, 0);

    const normalize = (value) => (value || '').toString().trim().toLowerCase();

    function parseDate(dateString) {
        if (!dateString) return null;
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return null;
        date.setHours(0, 0, 0, 0);
        return date;
    }

    function daysDiff(dateString) {
        const date = parseDate(dateString);
        if (!date) return Infinity;
        return (now - date) / (1000 * 60 * 60 * 24);
    }

    function isWithinLast7Days(dateString) {
        const diff = daysDiff(dateString);
        return diff >= 0 && diff <= 7;
    }

    function hasPaymentAlreadyMade(paymentStatus) {
        const payment = normalize(paymentStatus);
        return payment === 'paid' || payment === 'partially paid';
    }

    function matchesTab(row) {
        const payment = normalize(row.dataset.payment);
        const status = normalize(row.dataset.status);
        const progress = Number(row.dataset.progress) || 0;
        const lastContactedDays = daysDiff(row.dataset.lastContacted);
        const refundFlag = normalize(row.dataset.refund) === 'true';

        switch (activeTab) {
            case 'new':
                return isWithinLast7Days(row.dataset.date);
            case 'for-follow-up':
                return status === 'pending' && progress < 50 && lastContactedDays > 3;
            case 'ongoing':
                return status === 'processing' && progress >= 20 && progress <= 99;
            case 'payment-issues':
                return payment === 'unpaid' || payment === 'partially paid' || payment === 'overdue';
            case 'finished':
                return status === 'finished' || progress === 100;
            case 'refund':
                return refundFlag || (status === 'cancelled' && hasPaymentAlreadyMade(payment));
            case 'all':
            default:
                return true;
        }
    }

    function matchesSearch(row) {
        const term = normalize(searchInput ? searchInput.value : '');
        if (!term) return true;
        return normalize(row.textContent).includes(term);
    }

    function matchesControlFilters(row) {
        const payment = normalize(row.dataset.payment);
        const status = normalize(row.dataset.status);

        const selectValue = normalize(filterSelect ? filterSelect.value : 'all');
        const selectMatches = selectValue === 'all' || payment === selectValue;

        const paymentDropdownMatches = normalize(headerPaymentFilter) === 'all' || payment === normalize(headerPaymentFilter);
        const statusDropdownMatches = normalize(headerStatusFilter) === 'all' || status === normalize(headerStatusFilter);

        return selectMatches && paymentDropdownMatches && statusDropdownMatches;
    }

    function getFilteredRows() {
        return rows.filter((row) => matchesTab(row) && matchesSearch(row) && matchesControlFilters(row));
    }

    function buildPagination(pageCount) {
        if (!paginationEl) return;
        paginationEl.innerHTML = '';

        if (pageCount === 0) {
            const item = document.createElement('li');
            item.className = 'page-item disabled';
            item.innerHTML = '<span class="page-link">No results</span>';
            paginationEl.appendChild(item);
            return;
        }

        const prev = document.createElement('li');
        prev.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
        prev.innerHTML = '<a class="page-link" href="#">Previous</a>';
        prev.addEventListener('click', function(event) {
            event.preventDefault();
            if (currentPage > 1) renderPage(currentPage - 1);
        });
        paginationEl.appendChild(prev);

        for (let page = 1; page <= pageCount; page++) {
            const pageItem = document.createElement('li');
            pageItem.className = 'page-item ' + (page === currentPage ? 'active' : '');
            pageItem.innerHTML = `<a class="page-link" href="#">${page}</a>`;
            pageItem.addEventListener('click', function(event) {
                event.preventDefault();
                renderPage(page);
            });
            paginationEl.appendChild(pageItem);
        }

        const next = document.createElement('li');
        next.className = 'page-item ' + (currentPage === pageCount ? 'disabled' : '');
        next.innerHTML = '<a class="page-link" href="#">Next</a>';
        next.addEventListener('click', function(event) {
            event.preventDefault();
            if (currentPage < pageCount) renderPage(currentPage + 1);
        });
        paginationEl.appendChild(next);
    }

    function updateEntries(total, startIndex, endIndex) {
        if (entriesStart) entriesStart.textContent = total === 0 ? '0' : String(startIndex + 1);
        if (entriesEnd) entriesEnd.textContent = total === 0 ? '0' : String(Math.min(endIndex, total));
        if (entriesTotal) entriesTotal.textContent = String(total);
    }

    function updateSelectAllState() {
        if (!selectAll) return;
        const visibleCheckboxes = rows
            .filter((row) => row.style.display !== 'none')
            .map((row) => row.querySelector('.row-checkbox'))
            .filter(Boolean);

        if (!visibleCheckboxes.length) {
            selectAll.checked = false;
            return;
        }

        const checkedCount = visibleCheckboxes.filter((checkbox) => checkbox.checked).length;
        selectAll.checked = checkedCount === visibleCheckboxes.length;
    }

    function renderPage(page) {
        const filtered = getFilteredRows();
        const total = filtered.length;
        const pageCount = Math.max(1, Math.ceil(total / pageSize));
        currentPage = Math.min(Math.max(1, page), pageCount);

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const visibleRows = new Set(filtered.slice(startIndex, endIndex));

        rows.forEach((row) => {
            row.style.display = visibleRows.has(row) ? '' : 'none';
        });

        updateEntries(total, startIndex, endIndex);
        buildPagination(total === 0 ? 0 : pageCount);
        updateSelectAllState();
    }

    function activateTab(tab) {
        tabs.forEach((item) => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        activeTab = tab.dataset.filter || 'all';
        renderPage(1);
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', function(event) {
            event.preventDefault();
            activateTab(this);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            renderPage(1);
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            renderPage(1);
        });
    }

    if (paymentDropdownItems.length) {
        paymentDropdownItems.forEach((item) => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                paymentDropdownItems.forEach((entry) => entry.classList.remove('active'));
                this.classList.add('active');
                headerPaymentFilter = this.dataset.payment || 'all';
                renderPage(1);
            });
        });
    }

    if (statusDropdownItems.length) {
        statusDropdownItems.forEach((item) => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                statusDropdownItems.forEach((entry) => entry.classList.remove('active'));
                this.classList.add('active');
                headerStatusFilter = this.dataset.status || 'all';
                renderPage(1);
            });
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rows.forEach((row) => {
                if (row.style.display === 'none') return;
                const checkbox = row.querySelector('.row-checkbox');
                if (checkbox) checkbox.checked = this.checked;
            });
        });
    }

    document.addEventListener('change', function(event) {
        if (event.target.classList && event.target.classList.contains('row-checkbox')) {
            updateSelectAllState();
        }
    });

    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked'));
            if (!selected.length) {
                alert('No customers selected to send reminders.');
                return;
            }

            selected.forEach((checkbox) => {
                const row = checkbox.closest('tr');
                if (!row) return;
                row.classList.add('reminder-sent');
                const actions = row.querySelector('td:last-child');
                if (actions && !actions.querySelector('.reminder-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-warning ms-2 reminder-badge';
                    badge.textContent = 'Reminder Sent';
                    actions.appendChild(badge);
                }
            });

            sendBtn.innerHTML = `<i class="fa fa-bell"></i> Sent to ${selected.length}`;
            setTimeout(function() {
                sendBtn.innerHTML = '<i class="fa fa-bell"></i> Send Payment Reminder';
            }, 2500);
        });
    }

    renderPage(1);
});

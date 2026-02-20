document.addEventListener('DOMContentLoaded', function() {
    const tableBodyRows = Array.from(document.querySelectorAll('table.table tbody tr'));
    if (!tableBodyRows.length) return;

    const pageSize = 10;
    let currentPage = 1;
    let activeFilter = 'all';

    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');
    const paginationEl = document.getElementById('tablePagination');
    const searchInput = document.querySelector('.search-input');
    const filterTabs = Array.from(document.querySelectorAll('#applicantFilterTabs .nav-link'));
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const normalize = (value) => (value || '').toString().trim().toLowerCase();

    function isWithinLast7Days(dateString) {
        if (!dateString) return false;
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return false;
        date.setHours(0, 0, 0, 0);
        const diffDays = (today - date) / (1000 * 60 * 60 * 24);
        return diffDays >= 0 && diffDays <= 7;
    }

    function matchesTab(row) {
        const documentStatus = normalize(row.dataset.documents);
        const applicationStatus = normalize(row.dataset.application);

        switch (activeFilter) {
            case 'new':
                return isWithinLast7Days(row.dataset.date);
            case 'documents-issue':
                return documentStatus === 'missing' || documentStatus === 'rejected';
            case 'under-processing':
                return documentStatus === 'submitted' || applicationStatus === 'processing' || applicationStatus === 'under review';
            case 'for-action':
                return applicationStatus === 'action required' || applicationStatus === 'pending';
            case 'approved':
                return documentStatus === 'approved' || applicationStatus === 'approved';
            case 'completed':
                return applicationStatus === 'visa issued';
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

    function getFilteredRows() {
        return tableBodyRows.filter((row) => matchesTab(row) && matchesSearch(row));
    }

    function buildPagination(pageCount) {
        if (!paginationEl) return;
        paginationEl.innerHTML = '';

        if (pageCount === 0) {
            const emptyLi = document.createElement('li');
            emptyLi.className = 'page-item disabled';
            emptyLi.innerHTML = '<span class="page-link">No results</span>';
            paginationEl.appendChild(emptyLi);
            return;
        }

        const previous = document.createElement('li');
        previous.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
        previous.innerHTML = '<a class="page-link" href="#">Previous</a>';
        previous.addEventListener('click', function(event) {
            event.preventDefault();
            if (currentPage > 1) renderPage(currentPage - 1);
        });
        paginationEl.appendChild(previous);

        for (let pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
            const pageItem = document.createElement('li');
            pageItem.className = 'page-item ' + (pageNumber === currentPage ? 'active' : '');
            pageItem.innerHTML = `<a class="page-link" href="#">${pageNumber}</a>`;
            pageItem.addEventListener('click', function(event) {
                event.preventDefault();
                renderPage(pageNumber);
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

    function renderPage(page) {
        const filteredRows = getFilteredRows();
        const totalRows = filteredRows.length;
        const pageCount = Math.max(1, Math.ceil(totalRows / pageSize));

        currentPage = Math.min(Math.max(1, page), pageCount);

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const visibleRows = new Set(filteredRows.slice(startIndex, endIndex));

        tableBodyRows.forEach((row) => {
            row.style.display = visibleRows.has(row) ? '' : 'none';
        });

        if (entriesStart) entriesStart.textContent = totalRows === 0 ? '0' : String(startIndex + 1);
        if (entriesEnd) entriesEnd.textContent = totalRows === 0 ? '0' : String(Math.min(endIndex, totalRows));
        if (entriesTotal) entriesTotal.textContent = String(totalRows);

        buildPagination(totalRows === 0 ? 0 : pageCount);
    }

    filterTabs.forEach((tab) => {
        tab.addEventListener('click', function() {
            filterTabs.forEach((item) => {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            activeFilter = this.dataset.filter || 'all';
            renderPage(1);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            renderPage(1);
        });
    }

    const highlightBtn = document.getElementById('highlightExpiredBtn');
    if (highlightBtn) {
        highlightBtn.addEventListener('click', function() {
            let found = 0;
            tableBodyRows.forEach((row) => {
                const passportCell = row.querySelector('td:nth-child(4)');
                const statusDot = passportCell ? passportCell.querySelector('.status-dot') : null;
                if (statusDot && statusDot.classList.contains('status-red')) {
                    row.classList.toggle('expired-highlight');
                    found++;
                }
            });
            if (found === 0) alert('No expired passports found.');
        });
    }

    renderPage(1);
});

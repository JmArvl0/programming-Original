document.addEventListener('DOMContentLoaded', function () {
    const purposeSelect = document.getElementById('purposeSelect');
    if (purposeSelect) {
        purposeSelect.addEventListener('change', function () {
            const form = this.closest('form');
            const url = new URL(window.location.href);
            const purpose = this.value || 'schedule';

            url.searchParams.set('purpose', purpose);

            if (form) {
                const monthInput = form.querySelector('input[name="month"]');
                const yearInput = form.querySelector('input[name="year"]');
                const dayInput = form.querySelector('input[name="day"]');
                const searchInput = form.querySelector('input[name="search"]');

                if (monthInput && monthInput.value) {
                    url.searchParams.set('month', monthInput.value);
                }
                if (yearInput && yearInput.value) {
                    url.searchParams.set('year', yearInput.value);
                }
                if (dayInput && dayInput.value) {
                    url.searchParams.set('day', dayInput.value);
                }
                if (searchInput) {
                    const searchValue = searchInput.value.trim();
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }
                }
            }

            window.location.href = url.toString();
        });
    }

    const addNewTourBtn = document.getElementById('addNewTourBtn');
    if (addNewTourBtn) {
        addNewTourBtn.addEventListener('click', function () {
            alert('Opening new tour creation form');
        });
    }

    document.querySelectorAll('.js-tour-action').forEach((button) => {
        button.addEventListener('click', function () {
            const action = this.getAttribute('data-action') || 'view';
            const tourId = this.getAttribute('data-tour-id') || '';
            if (action === 'manage') {
                alert(`Managing tour #${tourId}`);
                return;
            }
            alert(`Viewing tour #${tourId}`);
        });
    });

    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const selectedDateText = document.getElementById('selectedDateText');
    const availableSlots = document.getElementById('availableSlots');
    const tourListItems = document.querySelectorAll('.tour-list-item');
    const bookedGuestItems = document.querySelectorAll('.booked-guest-item');
    const bookedGuestsTitle = document.getElementById('bookedGuestsTitle');
    const noBookingsMessage = document.getElementById('noBookingsMessage');

    function updateQueryParams(nextParams) {
        const url = new URL(window.location.href);
        Object.entries(nextParams).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
        });
        window.location.href = url.toString();
    }

    function handleDaySelection(dayElement) {
        const dayNum = dayElement.getAttribute('data-day');
        const slots = dayElement.getAttribute('data-slots');
        calendarDays.forEach((day) => day.classList.remove('selected'));
        dayElement.classList.add('selected');

        if (monthSelect && yearSelect && selectedDateText && availableSlots) {
            selectedDateText.textContent = `${monthSelect.value} ${dayNum}, ${yearSelect.value}`;
            availableSlots.textContent = slots || '0/0';
        }

        updateQueryParams({
            month: monthSelect ? monthSelect.value : null,
            year: yearSelect ? yearSelect.value : null,
            day: dayNum
        });
    }

    calendarDays.forEach((day) => {
        day.addEventListener('click', function () {
            handleDaySelection(this);
        });
    });

    if (prevMonthBtn && monthSelect) {
        prevMonthBtn.addEventListener('click', function () {
            const months = Array.from(monthSelect.options).map((opt) => opt.value);
            const currentIndex = months.indexOf(monthSelect.value);
            if (currentIndex > 0) {
                updateQueryParams({ month: months[currentIndex - 1], year: yearSelect ? yearSelect.value : null, day: 1 });
            } else if (yearSelect && Number(yearSelect.value) > Number(yearSelect.options[0].value)) {
                updateQueryParams({ month: months[months.length - 1], year: Number(yearSelect.value) - 1, day: 1 });
            }
        });
    }

    if (nextMonthBtn && monthSelect) {
        nextMonthBtn.addEventListener('click', function () {
            const months = Array.from(monthSelect.options).map((opt) => opt.value);
            const currentIndex = months.indexOf(monthSelect.value);
            if (currentIndex < months.length - 1) {
                updateQueryParams({ month: months[currentIndex + 1], year: yearSelect ? yearSelect.value : null, day: 1 });
            } else if (yearSelect && Number(yearSelect.value) < Number(yearSelect.options[yearSelect.options.length - 1].value)) {
                updateQueryParams({ month: months[0], year: Number(yearSelect.value) + 1, day: 1 });
            }
        });
    }

    if (monthSelect) {
        monthSelect.addEventListener('change', function () {
            updateQueryParams({ month: monthSelect.value, year: yearSelect ? yearSelect.value : null, day: 1 });
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            updateQueryParams({ month: monthSelect ? monthSelect.value : null, year: yearSelect.value, day: 1 });
        });
    }

    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    function filterBookedGuestsByTour(tourId, tourName) {
        let visibleCount = 0;
        bookedGuestItems.forEach((item) => {
            const itemTourId = item.getAttribute('data-tour-id') || '';
            const isVisible = itemTourId === tourId;
            item.style.display = isVisible ? '' : 'none';
            if (isVisible) {
                visibleCount++;
            }
        });

        if (bookedGuestsTitle) {
            bookedGuestsTitle.textContent = tourName ? `Booked Guests - ${tourName}` : 'Booked Guests';
        }

        if (noBookingsMessage) {
            noBookingsMessage.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    tourListItems.forEach((item) => {
        item.addEventListener('click', function () {
            const tourId = this.getAttribute('data-tour-id') || '';
            const tourNameEl = this.querySelector('.tour-list-name');
            const tourName = tourNameEl ? tourNameEl.textContent.trim() : '';
            tourListItems.forEach((btn) => btn.classList.remove('active'));
            this.classList.add('active');
            filterBookedGuestsByTour(tourId, tourName);
        });
    });

    if (tourListItems.length > 0) {
        const activeTour = document.querySelector('.tour-list-item.active') || tourListItems[0];
        const activeTourId = activeTour.getAttribute('data-tour-id') || '';
        const activeTourNameEl = activeTour.querySelector('.tour-list-name');
        const activeTourName = activeTourNameEl ? activeTourNameEl.textContent.trim() : '';
        activeTour.classList.add('active');
        filterBookedGuestsByTour(activeTourId, activeTourName);
    }

    const tourRows = Array.from(document.querySelectorAll('.tour-rate-row'));
    const paginationEl = document.getElementById('tablePagination');
    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');

    if (!tourRows.length || !paginationEl || !entriesStart || !entriesEnd || !entriesTotal) {
        return;
    }

    const pageSize = 10;
    let currentPage = 1;

    function buildPagination(totalPages) {
        paginationEl.innerHTML = '';

        const previous = document.createElement('li');
        previous.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        previous.innerHTML = '<a class="page-link" href="#">Previous</a>';
        previous.addEventListener('click', function (event) {
            event.preventDefault();
            if (currentPage > 1) {
                renderPage(currentPage - 1);
            }
        });
        paginationEl.appendChild(previous);

        for (let page = 1; page <= totalPages; page++) {
            const pageItem = document.createElement('li');
            pageItem.className = `page-item ${page === currentPage ? 'active' : ''}`;
            pageItem.innerHTML = `<a class="page-link" href="#">${page}</a>`;
            pageItem.addEventListener('click', function (event) {
                event.preventDefault();
                renderPage(page);
            });
            paginationEl.appendChild(pageItem);
        }

        const next = document.createElement('li');
        next.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        next.innerHTML = '<a class="page-link" href="#">Next</a>';
        next.addEventListener('click', function (event) {
            event.preventDefault();
            if (currentPage < totalPages) {
                renderPage(currentPage + 1);
            }
        });
        paginationEl.appendChild(next);
    }

    function renderPage(page) {
        const totalRows = tourRows.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
        currentPage = Math.min(Math.max(1, page), totalPages);

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalRows);

        tourRows.forEach((row, index) => {
            row.style.display = index >= startIndex && index < endIndex ? '' : 'none';
        });

        entriesStart.textContent = totalRows === 0 ? '0' : String(startIndex + 1);
        entriesEnd.textContent = totalRows === 0 ? '0' : String(endIndex);
        entriesTotal.textContent = String(totalRows);

        buildPagination(totalPages);
    }

    renderPage(1);
});

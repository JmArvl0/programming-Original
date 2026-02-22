document.addEventListener('DOMContentLoaded', function() {
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
        calendarDays.forEach(d => d.classList.remove('selected'));
        dayElement.classList.add('selected');

        if (monthSelect && yearSelect && selectedDateText && availableSlots) {
            selectedDateText.textContent = `${monthSelect.value} ${dayNum}, ${yearSelect.value}`;
            availableSlots.textContent = slots;
        }

        updateQueryParams({
            month: monthSelect ? monthSelect.value : null,
            year: yearSelect ? yearSelect.value : null,
            day: dayNum
        });
    }

    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            handleDaySelection(this);
        });
    });

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            const months = Array.from(monthSelect.options).map(opt => opt.value);
            const currentIndex = months.indexOf(monthSelect.value);
            if (currentIndex > 0) {
                updateQueryParams({ month: months[currentIndex - 1], year: yearSelect ? yearSelect.value : null, day: 1 });
            } else if (yearSelect && Number(yearSelect.value) > Number(yearSelect.options[0].value)) {
                updateQueryParams({ month: months[months.length - 1], year: Number(yearSelect.value) - 1, day: 1 });
            }
        });
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            const months = Array.from(monthSelect.options).map(opt => opt.value);
            const currentIndex = months.indexOf(monthSelect.value);
            if (currentIndex < months.length - 1) {
                updateQueryParams({ month: months[currentIndex + 1], year: yearSelect ? yearSelect.value : null, day: 1 });
            } else if (yearSelect && Number(yearSelect.value) < Number(yearSelect.options[yearSelect.options.length - 1].value)) {
                updateQueryParams({ month: months[0], year: Number(yearSelect.value) + 1, day: 1 });
            }
        });
    }

    if (monthSelect) {
        monthSelect.addEventListener('change', function() {
            updateQueryParams({ month: monthSelect.value, year: yearSelect ? yearSelect.value : null, day: 1 });
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            updateQueryParams({ month: monthSelect ? monthSelect.value : null, year: yearSelect.value, day: 1 });
        });
    }

    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    function filterBookedGuestsByTour(tourId, tourName) {
        let visibleCount = 0;
        bookedGuestItems.forEach(item => {
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

    tourListItems.forEach(item => {
        item.addEventListener('click', function() {
            const tourId = this.getAttribute('data-tour-id') || '';
            const tourNameEl = this.querySelector('.tour-list-name');
            const tourName = tourNameEl ? tourNameEl.textContent.trim() : '';
            tourListItems.forEach(btn => btn.classList.remove('active'));
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

    // Tour rates table pagination
    const tourRows = Array.from(document.querySelectorAll('.tour-rate-row'));
    const paginationList = document.getElementById('tablePagination');
    const prevBtn = document.getElementById('tablePrevPage');
    const nextBtn = document.getElementById('tableNextPage');
    const entriesStart = document.getElementById('entriesStart');
    const entriesEnd = document.getElementById('entriesEnd');
    const entriesTotal = document.getElementById('entriesTotal');

    if (tourRows.length > 0 && paginationList && prevBtn && nextBtn && entriesStart && entriesEnd && entriesTotal) {
        const pageSize = 6;
        const totalRows = tourRows.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
        let currentPage = 1;
        entriesTotal.textContent = String(totalRows);

        function renderPageNumbers() {
            paginationList.innerHTML = '';
            for (let page = 1; page <= totalPages; page++) {
                const li = document.createElement('li');
                li.className = `page-item${page === currentPage ? ' active' : ''}`;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'page-link';
                button.textContent = String(page);
                button.addEventListener('click', function() {
                    currentPage = page;
                    renderTablePage();
                });
                li.appendChild(button);
                paginationList.appendChild(li);
            }
        }

        function renderTablePage() {
            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = Math.min(startIndex + pageSize, totalRows);

            tourRows.forEach((row, index) => {
                row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
            });

            entriesStart.textContent = totalRows === 0 ? '0' : String(startIndex + 1);
            entriesEnd.textContent = String(endIndex);
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;
            renderPageNumbers();
        }

        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderTablePage();
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                renderTablePage();
            }
        });

        renderTablePage();
    }
});

function addNewTour() {
    alert('Opening new tour creation form');
}

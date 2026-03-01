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
            showInfo('Opening new tour creation form');
        });
    }

    document.querySelectorAll('.js-tour-action').forEach((button) => {
        button.addEventListener('click', function () {
            const action = this.getAttribute('data-action') || 'view';
            const tourId = this.getAttribute('data-tour-id') || '';
            if (action === 'manage') {
                showInfo(`Managing tour #${tourId}`);
                return;
            }
            showInfo(`Viewing tour #${tourId}`);
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
    let bookedGuestItems = document.querySelectorAll('.booked-guest-item');
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

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function handleDaySelection(dayElement) {
        const dayNum = dayElement.getAttribute('data-day');
        const slots = dayElement.getAttribute('data-slots');
        calendarDays.forEach((day) => day.classList.remove('selected'));
        dayElement.classList.add('selected');

        // Optimistically update selected date text and slots
        if (monthSelect && yearSelect && selectedDateText && availableSlots) {
            selectedDateText.textContent = `${monthSelect.value} ${dayNum}, ${yearSelect.value}`;
            availableSlots.textContent = slots || '0/0';
        }

        // Load tours for the selected date via AJAX and update the right pane
        (async function loadDate() {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('ajax', 'load-date');

                const body = {
                    month: monthSelect ? monthSelect.value : null,
                    year: yearSelect ? yearSelect.value : null,
                    day: dayNum
                };

                const resp = await fetch(url.toString(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });

                const data = await resp.json();
                if (!resp.ok || !data.ok) {
                    console.warn('Failed to load date:', data);
                    return;
                }

                // Update selected date label and metrics
                if (data.selectedDateLabel && selectedDateText) {
                    selectedDateText.textContent = data.selectedDateLabel;
                }
                if (data.selectedDateData && availableSlots) {
                    const ds = data.selectedDateData;
                    availableSlots.textContent = (ds.totalSlots || 0) > 0 ? (ds.availableSlots || 0) + '/' + (ds.totalSlots || 0) : (ds.availableSlots || 0) + '/' + (ds.totalSlots || 0);
                }

                // Update tours count badge
                const widgetTitle = document.querySelector('.widget-title');
                if (widgetTitle && typeof data.selectedDateTours !== 'undefined') {
                    const badge = widgetTitle.querySelector('.badge');
                    if (badge) badge.textContent = (data.selectedDateTours.length || 0) + ' tours';
                }

                // Replace tour list section content
                const tourListSection = document.querySelector('.tour-list-section');
                if (tourListSection) {
                    if (Array.isArray(data.selectedDateTours) && data.selectedDateTours.length > 0) {
                        const listHtml = ['<h4 class="panel-subtitle">Scheduled Tours</h4>', '<div class="tour-cards-grid tour-list" id="tourList">'];
                        data.selectedDateTours.forEach((tour, idx) => {
                            const tourId = tour.tour_id || ('tour-' + idx);
                            const status = tour.status || 'available';
                            const name = tour.tour_name || 'N/A';
                            const destination = tour.destination || 'N/A';
                            const departure = tour.departure_time ? ' | ' + tour.departure_time : '';
                            const booked = tour.booked || 0;
                            const capacity = tour.capacity || 0;
                            const availPct = capacity > 0 ? Math.round((booked / capacity) * 100) : 0;
                            listHtml.push(`<div role="button" tabindex="0" class="tour-card tour-list-item ${status} ${idx===0? 'active':''}" data-tour-id="${escapeHtml(String(tourId))}">`);
                            listHtml.push('<div class="tour-thumb"><div class="tour-thumb-placeholder"></div></div>');
                            listHtml.push('<div class="card-header">');
                            listHtml.push(`<div class="card-title"><span class="tour-list-name">${escapeHtml(String(name))}</span><div class="card-subtitle"><span class="tour-list-meta">${escapeHtml(String(destination))}${departure}</span></div></div>`);
                            listHtml.push(`<div class="card-rate"><span class="tour-list-count">${booked}/${capacity} booked</span></div>`);
                            listHtml.push('</div>');
                            listHtml.push('<div class="card-body"><div class="card-meta">');
                            listHtml.push(`<div class="meta-item"><div class="meta-label">Booked</div><div class="meta-value"><strong>${booked}</strong> / ${capacity}</div></div>`);
                            listHtml.push(`<div class="meta-item"><div class="meta-label">Fill</div><div class="meta-value"><div class="progress-bar small"><div class="progress-fill" style="width: ${availPct}%"></div></div></div></div>`);
                            listHtml.push('</div></div>');
                            listHtml.push('</div>');
                        });
                        listHtml.push('</div>');
                        tourListSection.innerHTML = listHtml.join('');

                        // rebind tour item click behavior
                        const newItems = tourListSection.querySelectorAll('.tour-list-item');
                        newItems.forEach((item) => {
                            item.addEventListener('click', function () {
                                const tourId = this.getAttribute('data-tour-id') || '';
                                const tourNameEl = this.querySelector('.tour-list-name');
                                const tourName = tourNameEl ? tourNameEl.textContent.trim() : '';
                                newItems.forEach((btn) => btn.classList.remove('active'));
                                this.classList.add('active');
                                filterBookedGuestsByTour(tourId, tourName);
                            });
                        });

                    } else {
                        tourListSection.innerHTML = '<h4 class="panel-subtitle">Scheduled Tours</h4><div class="no-guests-message">No tours scheduled for this date.</div>';
                    }
                }

                // Update booked guests list if provided
                if (Array.isArray(data.selectedDateBookings)) {
                    // rebuild booked guests list elements if present in DOM
                    const bookedContainer = document.querySelector('.booked-guests-list');
                    if (bookedContainer) {
                        if (data.selectedDateBookings.length > 0) {
                            const rows = data.selectedDateBookings.map((r) => {
                                const name = escapeHtml(String(r.guest_name || r.name || 'Guest'));
                                const tourName = escapeHtml(String(r.tour_name || ''));
                                return `<div class="booked-guest-item" data-tour-id="${escapeHtml(String(r.tour_id || ''))}"><div class="guest-name">${name}</div><div class="guest-meta">${tourName}</div></div>`;
                            });
                            bookedContainer.innerHTML = rows.join('');
                            bookedGuestItems = document.querySelectorAll('.booked-guest-item');
                        } else {
                            bookedContainer.innerHTML = '<div class="no-guests-message">No booked guests for this date yet.</div>';
                            bookedGuestItems = document.querySelectorAll('.booked-guest-item');
                        }
                    }
                }

            } catch (err) {
                console.error('Failed loading date data', err);
            }
        })();
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

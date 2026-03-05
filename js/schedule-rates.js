document.addEventListener('DOMContentLoaded', function () {
    const addNewTourBtn = document.getElementById('addNewTourBtn');
    if (addNewTourBtn) {
        addNewTourBtn.addEventListener('click', function () {
            showInfo('Opening new tour creation form');
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-tour-action');
        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const action = button.getAttribute('data-action') || 'view';
        const tourId = button.getAttribute('data-tour-id') || '';
        if (action === 'manage') {
            showInfo(`Managing tour #${tourId}`);
            return;
        }
        showInfo(`Viewing tour #${tourId}`);
    });

    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const selectedDateText = document.getElementById('selectedDateText');
    const availableSlots = document.getElementById('availableSlots');
    const tourListItems = document.querySelectorAll('.tour-list-item');

    function setActiveTourCard(card, items) {
        items.forEach((item) => {
            item.classList.remove('active');
            item.setAttribute('aria-selected', 'false');
        });
        card.classList.add('active');
        card.setAttribute('aria-selected', 'true');
    }

    function enhanceTourCards(items) {
        items.forEach((item, index) => {
            item.style.setProperty('--i', String(index));
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');
            if (!item.hasAttribute('aria-selected')) {
                item.setAttribute('aria-selected', item.classList.contains('active') ? 'true' : 'false');
            }

            item.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    item.click();
                }
            });

            item.addEventListener('click', function () {
                setActiveTourCard(item, items);
            });
        });
    }

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

                // Replace tour list section content
                const tourListSection = document.querySelector('.tour-list-section');
                if (tourListSection) {
                    if (Array.isArray(data.selectedDateTours) && data.selectedDateTours.length > 0) {
                        const visibleTours = data.selectedDateTours.slice(0, 5);
                        const listHtml = ['<h4 class="panel-subtitle">Scheduled Tours</h4>', '<div class="tour-cards-grid tour-list" id="tourList">'];
                        visibleTours.forEach((tour, idx) => {
                            const tourId = tour.tour_id || ('tour-' + idx);
                            const status = tour.status || 'available';
                            const name = tour.tour_name || 'N/A';
                            const destination = tour.destination || 'N/A';
                            const departure = tour.departure_time ? ' | ' + tour.departure_time : '';
                            const booked = tour.booked || 0;
                            const capacity = tour.capacity || 0;
                            const fillPct = capacity > 0 ? Math.round((booked / capacity) * 100) : 0;
                            const imageUrl = 'https://picsum.photos/seed/' + encodeURIComponent(String(tourId)) + '/640/360';
                            listHtml.push(`<div role="button" tabindex="0" class="tour-card tour-list-item ${status} ${idx===0? 'active':''}" data-tour-id="${escapeHtml(String(tourId))}">`);
                            listHtml.push(`<div class="tour-thumb"><img class="tour-thumb-img" src="${escapeHtml(imageUrl)}" alt="${escapeHtml(String(name))}" loading="lazy"></div>`);
                            listHtml.push('<div class="card-header">');
                            listHtml.push(`<div class="card-title"><span class="tour-list-name">${escapeHtml(String(name))}</span><div class="card-subtitle"><span class="tour-list-meta">${escapeHtml(String(destination))}${departure}</span></div></div>`);
                            listHtml.push(`<div class="card-rate"><span class="tour-list-count">${booked}/${capacity} booked</span></div>`);
                            listHtml.push('</div>');
                            listHtml.push('<div class="card-body"><div class="card-meta">');
                            listHtml.push(`<div class="meta-item"><div class="meta-label">Booked</div><div class="meta-value"><strong>${booked}</strong> / ${capacity}</div></div>`);
                            listHtml.push(`<div class="meta-item"><div class="meta-label">Fill</div><div class="meta-value"><div class="progress-bar small"><div class="progress-fill" style="width: ${fillPct}%"></div></div></div></div>`);
                            listHtml.push('</div></div>');
                            listHtml.push('</div>');
                        });
                        listHtml.push('</div>');
                        tourListSection.innerHTML = listHtml.join('');

                        // rebind tour item click behavior
                        const newItems = Array.from(tourListSection.querySelectorAll('.tour-list-item'));
                        enhanceTourCards(newItems);
                        if (newItems.length > 0) {
                            const activeTour = tourListSection.querySelector('.tour-list-item.active') || newItems[0];
                            setActiveTourCard(activeTour, newItems);
                        }

                    } else {
                        tourListSection.innerHTML = '<h4 class="panel-subtitle">Scheduled Tours</h4><div class="no-guests-message">No tours scheduled for this date.</div>';
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

    const initialItems = Array.from(tourListItems);
    enhanceTourCards(initialItems);

    if (initialItems.length > 0) {
        const activeTour = document.querySelector('.tour-list-item.active') || initialItems[0];
        setActiveTourCard(activeTour, initialItems);
    }

    // Trigger card animations by adding .play class to tour-cards-grid
    function triggerCardAnimations() {
        const tourGrids = document.querySelectorAll('.tour-cards-grid');
        tourGrids.forEach((grid) => {
            grid.classList.add('play');
        });
    }

    // Trigger animations on initial page load
    triggerCardAnimations();

    // Also trigger animations after AJAX updates
    const observer = new MutationObserver(function() {
        triggerCardAnimations();
    });

    const tourCardsContainer = document.querySelector('.tour-rates-view-wrap') || document.querySelector('.tour-booking-panel');
    if (tourCardsContainer) {
        observer.observe(tourCardsContainer, { childList: true, subtree: true });
    }
});

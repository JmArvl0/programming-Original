document.addEventListener('DOMContentLoaded', function() {
    // Calendar functionality
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    const availabilityDays = document.querySelectorAll('.availability-day-week');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const selectedDateText = document.getElementById('selectedDateText');
    const availableSlots = document.getElementById('availableSlots');

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
    
    // Update selected date when calendar day is clicked
    if (calendarDays.length > 0) {
        calendarDays.forEach(day => {
            day.addEventListener('click', function() {
                const dayNum = this.getAttribute('data-day');
                const slots = this.getAttribute('data-slots');
                
                // Update selection
                calendarDays.forEach(d => d.classList.remove('selected'));
                availabilityDays.forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                
                // Update availability calendar selection
                const matchingDay = document.querySelector(`.availability-day-week[data-day="${dayNum}"]`);
                if (matchingDay) {
                    matchingDay.classList.add('selected');
                }
                
                // Update date text and slots
                if (monthSelect && yearSelect && selectedDateText && availableSlots) {
                    selectedDateText.textContent = `${monthSelect.value} ${dayNum}, ${yearSelect.value}`;
                    availableSlots.textContent = slots;
                }
                
                // Update guest list based on selected date
                updateGuestListForDate(dayNum);
                updateQueryParams({
                    month: monthSelect ? monthSelect.value : null,
                    year: yearSelect ? yearSelect.value : null,
                    day: dayNum
                });
            });
        });
    }
    
    // Availability day click
    if (availabilityDays.length > 0) {
        availabilityDays.forEach(day => {
            day.addEventListener('click', function() {
                const dayNum = this.getAttribute('data-day');
                const slots = this.getAttribute('data-slots');
                
                // Update selection
                calendarDays.forEach(d => d.classList.remove('selected'));
                availabilityDays.forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                
                // Update small calendar selection
                const matchingDay = document.querySelector(`.calendar-day[data-day="${dayNum}"]`);
                if (matchingDay) {
                    matchingDay.classList.add('selected');
                }
                
                // Update date text and slots
                if (monthSelect && yearSelect && selectedDateText && availableSlots) {
                    selectedDateText.textContent = `${monthSelect.value} ${dayNum}, ${yearSelect.value}`;
                    availableSlots.textContent = slots;
                }
                
                // Update guest list based on selected date
                updateGuestListForDate(dayNum);
                updateQueryParams({
                    month: monthSelect ? monthSelect.value : null,
                    year: yearSelect ? yearSelect.value : null,
                    day: dayNum
                });
            });
        });
    }
    
    // Calendar navigation
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            const currentMonth = monthSelect.value;
            const months = Array.from(monthSelect.options).map(opt => opt.value);
            const currentIndex = months.indexOf(currentMonth);
            if (currentIndex > 0) {
                updateQueryParams({
                    month: months[currentIndex - 1],
                    year: yearSelect ? yearSelect.value : null,
                    day: 1
                });
            } else if (yearSelect && Number(yearSelect.value) > Number(yearSelect.options[0].value)) {
                updateQueryParams({
                    month: months[months.length - 1],
                    year: Number(yearSelect.value) - 1,
                    day: 1
                });
            }
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            const currentMonth = monthSelect.value;
            const months = Array.from(monthSelect.options).map(opt => opt.value);
            const currentIndex = months.indexOf(currentMonth);
            if (currentIndex < months.length - 1) {
                updateQueryParams({
                    month: months[currentIndex + 1],
                    year: yearSelect ? yearSelect.value : null,
                    day: 1
                });
            } else if (yearSelect && Number(yearSelect.value) < Number(yearSelect.options[yearSelect.options.length - 1].value)) {
                updateQueryParams({
                    month: months[0],
                    year: Number(yearSelect.value) + 1,
                    day: 1
                });
            }
        });
    }
    
    if (monthSelect) {
        monthSelect.addEventListener('change', function() {
            updateQueryParams({
                month: monthSelect.value,
                year: yearSelect ? yearSelect.value : null,
                day: 1
            });
        });
    }
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            updateQueryParams({
                month: monthSelect ? monthSelect.value : null,
                year: yearSelect.value,
                day: 1
            });
        });
    }
    
    // Auto-submit search on enter
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const form = this.closest('form');
                if (form) form.submit();
            }
        });
    }
    
    // Function to update guest list based on selected date
    function updateGuestListForDate(day) {
        if (monthSelect && yearSelect) {
            console.log(`Loading guests for selected date: ${monthSelect.value} ${day}, ${yearSelect.value}`);
            // In real app, this would filter the guest list by date
        }
    }
    
    // Weekly calendar functionality
    const prevWeekBtn = document.getElementById('prevWeek');
    const nextWeekBtn = document.getElementById('nextWeek');
    const weekDays = document.querySelectorAll('.availability-day-week');
    let currentWeekStart = new Date(); // Start with current week

    // Initialize week navigation
    if (prevWeekBtn) {
        prevWeekBtn.addEventListener('click', function() {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            updateWeekCalendar();
        });
    }

    if (nextWeekBtn) {
        nextWeekBtn.addEventListener('click', function() {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            updateWeekCalendar();
        });
    }

    // Update week calendar
    function updateWeekCalendar() {
        console.log('Updating week calendar to week starting:', currentWeekStart);
        // In real app, this would fetch data for the selected week
        
        // Update week title
        const weekTitleBadge = document.querySelector('.widget-title .badge');
        if (weekTitleBadge && weekTitleBadge.previousSibling) {
            const weekStart = formatDate(currentWeekStart);
            const weekEnd = formatDate(new Date(currentWeekStart.getTime() + 6 * 24 * 60 * 60 * 1000));
            weekTitleBadge.previousSibling.textContent = `Week of ${weekStart} - ${weekEnd} `;
        }
    }

    function formatDate(date) {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // Week day click functionality
    weekDays.forEach(day => {
        day.addEventListener('click', function() {
            const dayNum = this.getAttribute('data-day');
            const date = this.getAttribute('data-date');
            const slots = this.getAttribute('data-slots');
            
            // Update selection
            weekDays.forEach(d => d.classList.remove('selected'));
            this.classList.add('selected');
            
            // Update small calendar selection if matching day exists
            const matchingDay = document.querySelector(`.calendar-day[data-day="${dayNum}"]`);
            if (matchingDay) {
                document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                matchingDay.classList.add('selected');
            }
            
            // Update date text and slots
            if (selectedDateText && availableSlots) {
                const dateObj = new Date(date);
                const dateString = dateObj.toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                selectedDateText.textContent = dateString;
                availableSlots.textContent = slots;
            }
            
            // Update guest list based on selected date
            updateGuestListForDate(dayNum);
            updateQueryParams({
                month: monthSelect ? monthSelect.value : null,
                year: yearSelect ? yearSelect.value : null,
                day: dayNum
            });
        });
    });

    // Initialize week calendar
    updateWeekCalendar();

    // Make tables responsive
    function handleResponsiveTables() {
        const tableWrappers = document.querySelectorAll('.table-wrapper');
        tableWrappers.forEach(wrapper => {
            if (window.innerWidth < 768) {
                wrapper.style.overflowX = 'auto';
            } else {
                wrapper.style.overflowX = 'visible';
            }
        });
    }
    
    // Initial setup
    handleResponsiveTables();
    window.addEventListener('resize', handleResponsiveTables);
    
    // Add tooltips for availability days
    availabilityDays.forEach(day => {
        day.addEventListener('mouseenter', function() {
            const dayNum = this.getAttribute('data-day');
            const slots = this.getAttribute('data-slots');
            this.setAttribute('title', `Day ${dayNum}: ${slots} slots available`);
        });
    });
});

// Action functions
function viewGuest(id) {
    alert(`Viewing guest details for ID: ${id}`);
    // window.location.href = `guest_view.php?id=${id}`;
}

function editGuest(id) {
    alert(`Editing guest for ID: ${id}`);
    // window.location.href = `guest_edit.php?id=${id}`;
}

function viewTour(id) {
    alert(`Viewing tour details for ID: ${id}`);
    // window.location.href = `tour_view.php?id=${id}`;
}

function manageTour(id) {
    alert(`Managing tour for ID: ${id}`);
    // window.location.href = `tour_manage.php?id=${id}`;
}

function viewAllGuests() {
    alert('Showing all guests in detailed view');
    // window.location.href = 'guests_all.php';
}

function viewAllTours() {
    alert('Showing all tours in detailed view');
    // window.location.href = 'tours_all.php';
}

function addNewTour() {
    alert('Opening new tour creation form');
    // window.location.href = 'tour_create.php';
}

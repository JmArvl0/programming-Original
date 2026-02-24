document.addEventListener('DOMContentLoaded', function () {
    const moduleViewOptions = document.querySelectorAll('.module-view-option');
    if (moduleViewOptions.length > 0) {
        moduleViewOptions.forEach((option) => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                const form = this.closest('form');
                const selectedView = this.getAttribute('data-view') || 'reservation_requests';
                const url = new URL(window.location.href);

                url.searchParams.set('view', selectedView);

                if (form) {
                    const searchInput = form.querySelector('input[name="search"]');
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
        });
    }

    const moduleViewDropdown = document.getElementById('moduleViewDropdown');
    if (moduleViewDropdown) {
        moduleViewDropdown.addEventListener('click', function (event) {
            if (typeof bootstrap === 'undefined') {
                event.preventDefault();
            }
        });
    }

    const facilitiesSearchInput = document.querySelector('.facilities-search-wrap .search-input');
    if (facilitiesSearchInput) {
        facilitiesSearchInput.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    function handleTableResponsive() {
        const tableWrappers = document.querySelectorAll('.table-wrapper');
        tableWrappers.forEach(wrapper => {
            if (window.innerWidth < 768) {
                wrapper.style.overflowX = 'auto';
            } else {
                wrapper.style.overflowX = 'visible';
            }
        });
    }

    handleTableResponsive();
    window.addEventListener('resize', handleTableResponsive);
});

function viewReservationRequest(id) {
    alert(`Viewing reservation request details for ID: ${id}`);
}

function updateReservationStatus(id) {
    alert(`Updating reservation request status for ID: ${id}`);
}

function addReservationRequest() {
    alert('Adding new facility reservation request...');
}

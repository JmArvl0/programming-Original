document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const searchInput = document.querySelector('.search-input');
    const paymentSelect = document.querySelector('.filter-select');
    const reminderBtn = document.getElementById('sendPaymentReminderBtn');

    function getCurrentUrl() {
        return new URL(window.location.href);
    }

    function submitSearchFilters() {
        const url = getCurrentUrl();
        url.searchParams.set('q', searchInput ? searchInput.value.trim() : '');
        url.searchParams.set('payment', paymentSelect ? paymentSelect.value : 'all');
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    async function postAjax(action, payload) {
        const url = getCurrentUrl();
        url.searchParams.set('ajax', action);

        const response = await fetch(url.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });

        let data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = { ok: false, message: 'Invalid JSON response.' };
        }

        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Request failed.');
        }

        return data;
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitSearchFilters();
            }
        });
    }

    if (paymentSelect) {
        paymentSelect.addEventListener('change', function () {
            submitSearchFilters();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    document.querySelectorAll('.js-customer-action').forEach(function (button) {
        button.addEventListener('click', async function () {
            const action = this.dataset.action || '';
            const customerId = this.dataset.id || '';
            const customerName = this.dataset.name || 'Customer';

            if (action === 'view-customer') {
                alert('Viewing profile: ' + customerName + ' (#' + customerId + ')');
                return;
            }

            if (action === 'delete-customer' && !window.confirm('Delete customer #' + customerId + '?')) {
                return;
            }

            try {
                const result = await postAjax(action, {
                    customer_id: customerId
                });
                alert(result.message || 'Action completed.');
            } catch (error) {
                alert(error.message);
            }
        });
    });

    if (reminderBtn) {
        reminderBtn.addEventListener('click', async function () {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                .map(function (checkbox) {
                    const row = checkbox.closest('tr');
                    return row ? row.dataset.id : null;
                })
                .filter(Boolean);

            if (!selectedIds.length) {
                alert('No customers selected to send reminders.');
                return;
            }

            try {
                const result = await postAjax('send-reminder', { customer_ids: selectedIds });
                alert(result.message || 'Reminders sent.');
            } catch (error) {
                alert(error.message);
            }
        });
    }
});

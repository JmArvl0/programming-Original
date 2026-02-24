document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.left-controls');
    const searchInput = searchForm ? searchForm.querySelector('.search-input') : null;

    if (searchInput && searchForm) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                searchForm.submit();
            }
        });
    }

    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
        });
    }

    async function callAjaxAction(action, payload) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', action);

        const response = await fetch(url.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload || {})
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }
        return response.json();
    }

    document.querySelectorAll('.js-applicant-action').forEach((button) => {
        button.addEventListener('click', async function () {
            const action = this.getAttribute('data-action') || '';
            const applicantId = this.getAttribute('data-id') || '';

            if (action === 'view') {
                alert(`Viewing applicant #${applicantId}`);
                return;
            }

            try {
                const result = await callAjaxAction(action, { applicantId });
                alert(result.message || 'Action completed.');
            } catch (error) {
                alert('Unable to process request right now.');
            }
        });
    });

    const reminderBtn = document.getElementById('sendPaymentReminderBtn');
    if (reminderBtn) {
        reminderBtn.addEventListener('click', async function () {
            try {
                const result = await callAjaxAction('send-reminder', { scope: 'filtered-list' });
                alert(result.message || 'Reminder sent.');
            } catch (error) {
                alert('Unable to send reminder right now.');
            }
        });
    }
});

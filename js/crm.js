document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }

    const newsletterBtn = document.querySelector('.crm-btn-news');
    if (newsletterBtn) {
        newsletterBtn.addEventListener('click', function () {
            alert('Sending newsletter to selected segment...');
        });
    }

    const exportBtn = document.querySelector('.crm-btn-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            alert('Exporting CRM report...');
        });
    }
});

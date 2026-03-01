document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const openBtn = document.getElementById('sidebarHamburgerHeader');
    const sidebar = document.getElementById('sidebar');
    const header = document.querySelector('.header');

    // Sidebar fixed (no toggle)
    body.classList.remove('sidebar-collapsed');

    // Mobile behavior removed intentionally (system not supported on phones)

    // Logo fallback
    const logoImg = document.querySelector('.company-logo img');
    const fallbackLogo = document.querySelector('.fallback-logo');

    if (logoImg) {
        logoImg.onerror = function () {
            this.style.display = 'none';
            if (fallbackLogo) fallbackLogo.style.display = 'block';
        };

        setTimeout(() => {
            if (logoImg.complete && logoImg.naturalHeight === 0) {
                logoImg.style.display = 'none';
                if (fallbackLogo) fallbackLogo.style.display = 'block';
            }
        }, 1000);
    }

    // Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});

function confirmLogout() {
    showInfo('Logging out...');
    bootstrap.Modal
        .getInstance(document.getElementById('logoutModal'))
        .hide();
}

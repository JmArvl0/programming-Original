document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('detailsModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalActionBtn = document.getElementById('modalActionBtn');
    const detailsModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    function showModal(config) {
        if (!detailsModal || !modalTitle || !modalContent || !modalActionBtn) {
            return;
        }
        modalTitle.textContent = config.title;
        modalContent.innerHTML = config.content;
        modalActionBtn.textContent = config.actionLabel || 'Take Action';
        modalActionBtn.className = config.actionClass || 'btn btn-primary';
        modalActionBtn.onclick = function () {
            if (typeof config.onAction === 'function') {
                config.onAction();
            }
            detailsModal.hide();
        };
        detailsModal.show();
    }

    function animateCharts() {
        document.querySelectorAll('.donut-chart-progress').forEach((donutChart) => {
            const percentage = parseInt(donutChart.getAttribute('data-percentage') || '0', 10) || 0;
            donutChart.style.setProperty('--percentage', String(percentage));
        });

        document.querySelectorAll('.progress-fill-custom').forEach((bar) => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 250);
        });

        document.querySelectorAll('.rating-bar').forEach((bar, index) => {
            const height = bar.style.height;
            bar.style.height = '0%';
            setTimeout(() => {
                bar.style.height = height;
            }, 250 + index * 80);
        });
    }

    document.querySelectorAll('.view-btn').forEach((btn) => {
        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            const name = this.getAttribute('data-name') || '';
            const type = this.getAttribute('data-type') || '';
            const status = this.getAttribute('data-status') || '';
            const deadline = this.getAttribute('data-deadline') || '';

            showModal({
                title: `View Details: ${name}`,
                content: `
                    <div class="mb-3"><strong>Name:</strong> ${name}</div>
                    <div class="mb-3"><strong>Type:</strong> ${type}</div>
                    <div class="mb-3"><strong>Status:</strong> <span class="badge bg-warning">${status}</span></div>
                    <div class="mb-3"><strong>Deadline:</strong> ${deadline}</div>
                    <div class="alert alert-warning"><small>This item requires immediate attention.</small></div>
                `,
                actionLabel: 'Mark as Reviewed',
                actionClass: 'btn btn-primary',
                onAction: function () {
                    alert(`Marked "${name}" as reviewed`);
                }
            });
        });
    });

    document.querySelectorAll('.remind-btn').forEach((btn) => {
        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            const name = this.getAttribute('data-name') || '';
            const departure = this.getAttribute('data-departure') || '';

            showModal({
                title: 'Send Reminder',
                content: `
                    <div class="mb-3"><strong>Traveler:</strong> ${name}</div>
                    <div class="mb-3"><strong>Departure:</strong> ${departure}</div>
                    <div class="form-group mb-3">
                        <label for="reminderMessage" class="form-label">Custom Message (optional):</label>
                        <textarea class="form-control" id="reminderMessage" rows="3" placeholder="Add a custom message..."></textarea>
                    </div>
                    <div class="alert alert-info"><small>A reminder email and SMS will be sent.</small></div>
                `,
                actionLabel: 'Send Reminder',
                actionClass: 'btn btn-warning',
                onAction: function () {
                    const messageInput = document.getElementById('reminderMessage');
                    const message = messageInput ? messageInput.value : '';
                    alert(`Reminder sent to ${name}${message ? ' with message: ' + message : ''}`);
                }
            });
        });
    });

    document.querySelectorAll('.clickable-row').forEach((row) => {
        row.addEventListener('click', function () {
            const action = this.getAttribute('data-action') || '';
            if (action === 'view') {
                const viewBtn = this.querySelector('.view-btn');
                if (viewBtn) {
                    viewBtn.click();
                }
                return;
            }
            if (action === 'remind') {
                const remindBtn = this.querySelector('.remind-btn');
                if (remindBtn) {
                    remindBtn.click();
                }
                return;
            }
            if (action === 'track') {
                const name = this.getAttribute('data-name') || (this.cells[0] ? this.cells[0].textContent : '');
                const staff = this.getAttribute('data-staff') || (this.cells[1] ? this.cells[1].textContent : '');
                const type = this.getAttribute('data-type') || (this.cells[2] ? this.cells[2].textContent : '');

                showModal({
                    title: `Track Operation: ${name}`,
                    content: `
                        <div class="mb-3"><strong>Customer:</strong> ${name}</div>
                        <div class="mb-3"><strong>Staff On-Duty:</strong> ${staff}</div>
                        <div class="mb-3"><strong>Type:</strong> ${type}</div>
                        <div class="alert alert-success"><small>Operation is currently active and being tracked.</small></div>
                    `,
                    actionLabel: 'Open Full Tracking',
                    actionClass: 'btn btn-primary',
                    onAction: function () {
                        alert(`Opening full tracking for ${name}`);
                    }
                });
            }
        });
    });

    const refreshMapBtn = document.getElementById('refreshMapBtn');
    if (refreshMapBtn) {
        refreshMapBtn.addEventListener('click', function () {
            const originalText = this.textContent;
            this.textContent = 'Refreshing...';
            this.disabled = true;

            setTimeout(() => {
                this.textContent = originalText;
                this.disabled = false;
                alert('Map locations refreshed');
            }, 900);
        });
    }

    document.querySelectorAll('[data-lazy-ready="true"]').forEach((widget) => {
        widget.setAttribute('data-widget-ready', '1');
    });

    animateCharts();
});

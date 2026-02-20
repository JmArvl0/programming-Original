document.addEventListener('DOMContentLoaded', function() {
    // Make tables responsive
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
    
    // Add hover effects to map cells
    const mapCells = document.querySelectorAll('.map-cell:not(:empty)');
    mapCells.forEach(cell => {
        cell.addEventListener('mouseenter', function() {
            const type = this.classList.contains('transport') ? 'Transport' : 
                        this.classList.contains('staff') ? 'Staff' : 'Lounge';
            const count = this.querySelector('span:last-child')?.textContent || '1';
            this.setAttribute('title', `${type} Unit (${count})`);
        });
    });
});

// Action functions
function viewLiveMap() {
    alert('Opening detailed live map view...');
    // window.location.href = 'live_map.php';
}

function viewService(id) {
    alert(`Viewing service details for ID: ${id}`);
    // window.location.href = `service_details.php?id=${id}`;
}

function updateService(id) {
    alert(`Updating service for ID: ${id}`);
    // Open modal for updating service status
}

function addNewService() {
    alert('Adding new service request...');
    // window.location.href = 'service_create.php';
}

function viewAsset(id) {
    alert(`Viewing asset details for ID: ${id}`);
    // window.location.href = `asset_details.php?id=${id}`;
}

function trackAsset(id) {
    alert(`Tracking asset location for ID: ${id}`);
    // window.location.href = `asset_track.php?id=${id}`;
}

function updateMaintenance(id) {
    alert(`Updating maintenance for asset ID: ${id}`);
    // Open maintenance update modal
}

function reserveAsset(id) {
    alert(`Reserving asset ID: ${id}`);
    // Open reservation form modal
}

function addNewAsset() {
    alert('Adding new asset to inventory...');
    // window.location.href = 'asset_create.php';
}

function exportAssets() {
    alert('Exporting asset data...\nThis would generate a CSV file with all asset information.');
    // window.location.href = 'export_assets.php';
}

function scheduleMaintenance() {
    alert('Opening maintenance scheduling...');
    // window.location.href = 'maintenance_schedule.php';
}

// Animate progress bars on load
window.addEventListener('load', function() {
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const originalWidth = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = originalWidth;
        }, 100);
    });
});

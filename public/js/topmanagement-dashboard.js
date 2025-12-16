// topmanagement-dashboard.js
// Connects dashboard cards and buttons to backend API endpoints for live data

document.addEventListener('DOMContentLoaded', function () {
    // Example: Fetch and update dashboard cards
    fetch('/api/reports/inventory/summary')
        .then(res => res.json())
        .then(data => {
            if (data && data.data) {
                document.querySelector('.card-title.text-success').textContent =
                    '$' + parseFloat(data.data.total_value).toLocaleString();
                document.querySelector('.card-title.text-primary').textContent =
                    data.data.total_items;
            }
        });
    fetch('/api/reports/ar/outstanding')
        .then(res => res.json())
        .then(data => {
            if (data && data.data) {
                document.querySelector('.card-title.text-danger').textContent =
                    '$' + parseFloat(data.data.outstanding_total).toLocaleString();
            }
        });
    // Add event listeners for report buttons
    document.querySelectorAll('a.btn.btn-primary').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            let text = this.textContent.trim();
            if (text === 'View Reports') {
                window.location.href = '/api/reports/ap/outstanding';
            } else if (text === 'View Inventory') {
                window.location.href = '/api/reports/inventory/summary';
            } else if (text === 'View Procurement') {
                window.location.href = '/api/reports/ap/outstanding';
            } else if (text === 'View Logs') {
                window.location.href = '/api/reports/ar/history';
            }
        });
    });
});

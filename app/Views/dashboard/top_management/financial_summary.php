<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Financial Summary') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-coins text-primary"></i> Financial Summary</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-success me-3"><?= session('user_role') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 text-center text-white mb-3">
                <h5><i class="fas fa-bars"></i> Navigation</h5>
            </div>
            <a href="<?= site_url('top-management') ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= site_url('top-management/pr-approvals') ?>">
                <i class="fas fa-file-signature"></i> PR Approvals
            </a>
            <a href="<?= site_url('top-management/po-approvals') ?>">
                <i class="fas fa-file-invoice"></i> PO Approvals
            </a>
            <a href="<?= site_url('top-management/kpis') ?>">
                <i class="fas fa-gauge"></i> KPIs
            </a>
            <a href="<?= site_url('top-management/inventory-summary') ?>">
                <i class="fas fa-building"></i> Inventory Summary
            </a>
            <a href="<?= site_url('top-management/financial-summary') ?>" class="active">
                <i class="fas fa-coins"></i> Financial Summary
            </a>
            <a href="<?= site_url('top-management/export/warehouse-summary.csv') ?>">
                <i class="fas fa-file-csv"></i> Export Warehouse CSV
            </a>
            <a href="<?= site_url('top-management/audit-logs') ?>">
                <i class="fas fa-clipboard-list"></i> Audit Logs
            </a>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="col-md-10 p-4">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="dashboard-widget stat-card bg-success text-white">
                        <h6 class="text-uppercase">AP Outstanding</h6>
                        <h3><?= esc(number_format((float) ($apStats['total_outstanding'] ?? 0), 2)) ?></h3>
                        <small>Payables</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-widget stat-card bg-secondary text-white">
                        <h6 class="text-uppercase">AR Outstanding</h6>
                        <h3><?= esc(number_format((float) ($arOutstanding ?? 0), 2)) ?></h3>
                        <small>Receivables</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dashboard-widget stat-card bg-info text-white">
                        <h6 class="text-uppercase">AP Total Invoices</h6>
                        <h3><?= esc($apStats['total_invoices'] ?? 0) ?></h3>
                        <small>All statuses</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="dashboard-widget">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> AR Status Distribution</h5>
                            <a href="<?= site_url('top-management') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
                        </div>
                        <div style="height: 340px;">
                            <canvas id="arStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-chart-column"></i> AP Counts</h5>
                        <div style="height: 340px;">
                            <canvas id="apCountChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-widget">
                <h5 class="mb-3"><i class="fas fa-receipt"></i> Accounts Payable (Quick Stats)</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Total Invoices</td><td><?= esc($apStats['total_invoices'] ?? 0) ?></td></tr>
                            <tr><td>Pending</td><td><?= esc($apStats['pending_count'] ?? 0) ?></td></tr>
                            <tr><td>Partial</td><td><?= esc($apStats['partial_count'] ?? 0) ?></td></tr>
                            <tr><td>Paid</td><td><?= esc($apStats['paid_count'] ?? 0) ?></td></tr>
                            <tr><td>Overdue</td><td><?= esc($apStats['overdue_count'] ?? 0) ?></td></tr>
                            <tr><td>Total Outstanding</td><td><?= esc(number_format((float) ($apStats['total_outstanding'] ?? 0), 2)) ?></td></tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Read-only financial analytics for Top Management.</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const arStatusCounts = <?= json_encode($arStatusCounts ?? []) ?>;
const arLabels = Object.keys(arStatusCounts);
const arValues = arLabels.map(k => arStatusCounts[k]);

const arCtx = document.getElementById('arStatusChart');
new Chart(arCtx, {
    type: 'doughnut',
    data: {
        labels: arLabels,
        datasets: [{
            data: arValues,
            backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#dc3545', '#6c757d']
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

const apStats = <?= json_encode($apStats ?? []) ?>;
const apCtx = document.getElementById('apCountChart');
new Chart(apCtx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Partial', 'Paid', 'Overdue'],
        datasets: [{
            label: 'Invoice Count',
            data: [
                parseInt(apStats.pending_count || 0),
                parseInt(apStats.partial_count || 0),
                parseInt(apStats.paid_count || 0),
                parseInt(apStats.overdue_count || 0)
            ],
            backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>

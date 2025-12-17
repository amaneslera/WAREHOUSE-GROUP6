<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'KPIs') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-gauge text-primary"></i> KPIs</h2>
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
            <a href="<?= site_url('top-management/kpis') ?>" class="active">
                <i class="fas fa-gauge"></i> KPIs
            </a>
            <a href="<?= site_url('top-management/inventory-summary') ?>">
                <i class="fas fa-building"></i> Inventory Summary
            </a>
            <a href="<?= site_url('top-management/financial-summary') ?>">
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
            <?php $k = $kpis ?? []; ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-warning text-white">
                        <h6 class="text-uppercase">Pending PR Approvals</h6>
                        <h3><?= esc($k['pending_pr'] ?? 0) ?></h3>
                        <small>Submitted PRs</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-info text-white">
                        <h6 class="text-uppercase">Pending PO Approvals</h6>
                        <h3><?= esc($k['pending_po'] ?? 0) ?></h3>
                        <small>Awaiting decision</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-danger text-white">
                        <h6 class="text-uppercase">Low Stock Items</h6>
                        <h3><?= esc($k['low_stock'] ?? 0) ?></h3>
                        <small>At/below minimum</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-secondary text-white">
                        <h6 class="text-uppercase">AR Outstanding</h6>
                        <h3><?= esc(number_format((float) ($k['ar_outstanding'] ?? 0), 2)) ?></h3>
                        <small>Open receivables</small>
                    </div>
                </div>
            </div>

            <div class="dashboard-widget">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-column"></i> KPI Overview</h5>
                    <a href="<?= site_url('top-management') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
                </div>
                <div style="height: 360px;">
                    <canvas id="kpiChart"></canvas>
                </div>
                <small class="text-muted">Read-only KPIs for Top Management (no operational actions).</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const kpis = {
    pending_pr: <?= json_encode((int) ($kpis['pending_pr'] ?? 0)) ?>,
    pending_po: <?= json_encode((int) ($kpis['pending_po'] ?? 0)) ?>,
    low_stock: <?= json_encode((int) ($kpis['low_stock'] ?? 0)) ?>,
    ap_outstanding: <?= json_encode((float) ($kpis['ap_outstanding'] ?? 0)) ?>,
    ar_outstanding: <?= json_encode((float) ($kpis['ar_outstanding'] ?? 0)) ?>
};

const ctx = document.getElementById('kpiChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Pending PR', 'Pending PO', 'Low Stock', 'AP Outstanding', 'AR Outstanding'],
        datasets: [{
            label: 'KPI Values',
            data: [kpis.pending_pr, kpis.pending_po, kpis.low_stock, kpis.ap_outstanding, kpis.ar_outstanding],
            backgroundColor: ['#ffc107', '#0dcaf0', '#dc3545', '#198754', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>

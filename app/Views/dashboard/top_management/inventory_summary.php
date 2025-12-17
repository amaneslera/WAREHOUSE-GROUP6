<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Inventory Summary') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-building text-primary"></i> Inventory Summary</h2>
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
            <a href="<?= site_url('top-management/inventory-summary') ?>" class="active">
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
            <div class="dashboard-widget">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Warehouse Inventory Value</h5>
                    <div class="d-flex gap-2">
                        <a href="<?= site_url('top-management/export/warehouse-summary.csv') ?>" class="btn btn-sm btn-outline-secondary">Export CSV</a>
                        <a href="<?= site_url('top-management') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
                    </div>
                </div>

                <div style="height: 360px;" class="mb-3">
                    <canvas id="warehouseValueChart"></canvas>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Warehouse</th>
                                <th>Total Items</th>
                                <th>Total Qty</th>
                                <th>Total Value</th>
                                <th>Low Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($warehouseSummary) && is_array($warehouseSummary)): ?>
                                <?php foreach ($warehouseSummary as $w): ?>
                                    <tr>
                                        <td><?= esc($w['warehouse_name'] ?? '') ?></td>
                                        <td><?= esc($w['total_items'] ?? 0) ?></td>
                                        <td><?= esc($w['total_quantity'] ?? 0) ?></td>
                                        <td><?= esc(number_format((float) ($w['total_value'] ?? 0), 2)) ?></td>
                                        <td><?= esc($w['low_stock_count'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const warehouseRows = <?= json_encode($warehouseSummary ?? []) ?>;
const labels = warehouseRows.map(r => r.warehouse_name || '');
const values = warehouseRows.map(r => parseFloat(r.total_value || 0));

const ctx = document.getElementById('warehouseValueChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Total Value',
            data: values,
            backgroundColor: '#0d6efd'
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

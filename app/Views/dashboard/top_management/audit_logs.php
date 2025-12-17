<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Audit Logs') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        <h2 class="mb-0"><i class="fas fa-clipboard-list text-primary"></i> Audit Logs</h2>
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
            <a href="<?= site_url('top-management/financial-summary') ?>">
                <i class="fas fa-coins"></i> Financial Summary
            </a>
            <a href="<?= site_url('top-management/export/warehouse-summary.csv') ?>">
                <i class="fas fa-file-csv"></i> Export Warehouse CSV
            </a>
            <a href="<?= site_url('top-management/audit-logs') ?>" class="active">
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
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Audit Logs (Read-only)</h5>
                </div>

                <form method="get" action="<?= site_url('top-management/audit-logs') ?>" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="module" class="form-control form-control-sm" placeholder="Module" value="<?= esc($filters['module'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="action" class="form-control form-control-sm" placeholder="Action" value="<?= esc($filters['action'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="user_id" class="form-control form-control-sm" placeholder="User ID" value="<?= esc($filters['user_id'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('top-management/audit-logs') ?>">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Record</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (isset($logs) && is_array($logs) && count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><small><?= esc($log['created_at'] ?? '') ?></small></td>
                                    <td>
                                        <div><strong><?= esc(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?></strong></div>
                                        <small class="text-muted"><?= esc($log['email'] ?? '') ?></small>
                                    </td>
                                    <td><?= esc($log['module'] ?? '') ?></td>
                                    <td><?= esc($log['action'] ?? '') ?></td>
                                    <td><small><?= esc($log['description'] ?? '') ?></small></td>
                                    <td><small>#<?= esc($log['record_id'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No audit logs found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'PR Approvals') ?></title>
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
        <h2 class="mb-0"><i class="fas fa-file-signature text-primary"></i> PR Approvals</h2>
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
            <a href="<?= site_url('top-management/pr-approvals') ?>" class="active">
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
            <a href="<?= site_url('top-management/audit-logs') ?>">
                <i class="fas fa-clipboard-list"></i> Audit Logs
            </a>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="col-md-10 p-4">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="dashboard-widget">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-clock text-warning"></i> Submitted PRs (Pending Approval)</h5>
                    <a href="<?= site_url('top-management') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>PR #</th>
                                <th>Requested By</th>
                                <th>Warehouse</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($prs) && is_array($prs)): ?>
                                <?php foreach ($prs as $pr): ?>
                                    <tr>
                                        <td><?= esc($pr['pr_number'] ?? '') ?></td>
                                        <td><?= esc(($pr['first_name'] ?? '') . ' ' . ($pr['last_name'] ?? '')) ?></td>
                                        <td><?= esc($pr['warehouse_name'] ?? '-') ?></td>
                                        <td><span class="badge bg-warning"><?= esc(ucfirst($pr['status'] ?? '')) ?></span></td>
                                        <td><small><?= esc($pr['created_at'] ?? '') ?></small></td>
                                        <td>
                                            <?php $prId = (int) ($pr['id'] ?? 0); ?>
                                            <?php $items = $itemsByPr[$prId] ?? []; ?>
                                            <?php if (! empty($items)): ?>
                                                <ul class="mb-0">
                                                    <?php foreach ($items as $it): ?>
                                                        <li>
                                                            <?= esc($it['item_name'] ?? '') ?>
                                                            - Qty: <?= esc($it['quantity'] ?? '') ?>
                                                            <?= esc($it['unit_of_measure'] ?? '') ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">No items</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" action="<?= site_url('top-management/pr-approvals/' . ($pr['id'] ?? 0) . '/approve') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="post" action="<?= site_url('top-management/pr-approvals/' . ($pr['id'] ?? 0) . '/reject') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No submitted PRs found.</td>
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

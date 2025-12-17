<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager - Receiving Tasks</title>
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
        <h2 class="mb-0"><i class="fas fa-clipboard-check text-primary"></i> Incoming Purchase Orders</h2>
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
            <a href="<?= site_url('dashboard/manager') ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= site_url('dashboard/manager/approvals') ?>">
                <i class="fas fa-check-square"></i> Approvals
            </a>
            <a href="<?= site_url('dashboard/manager/tasks') ?>" class="active">
                <i class="fas fa-clipboard-check"></i> Receiving Tasks
            </a>
            <a href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes"></i> Inventory
            </a>
            <a href="<?= site_url('stock-movements') ?>">
                <i class="fas fa-exchange-alt"></i> Stock Movements
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
                <h5 class="mb-3"><i class="fas fa-file-invoice"></i> Open Purchase Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>PO #</th>
                                <th>Vendor</th>
                                <th>Warehouse</th>
                                <th>Expected</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($pos) && is_array($pos)): ?>
                                <?php foreach ($pos as $po): ?>
                                    <tr>
                                        <td><?= esc($po['po_number'] ?? '') ?></td>
                                        <td><?= esc($po['vendor_name'] ?? '') ?></td>
                                        <td><?= esc($po['warehouse_name'] ?? '') ?></td>
                                        <td><?= esc($po['expected_delivery_date'] ?? '-') ?></td>
                                        <td>
                                            <?php $poId = (int) ($po['id'] ?? 0); ?>
                                            <?php $items = $itemsByPo[$poId] ?? []; ?>
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
                                            <a class="btn btn-sm btn-primary" href="<?= site_url('dashboard/manager/tasks/' . ($po['id'] ?? 0) . '/create') ?>">Create Task</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No open POs found.</td>
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

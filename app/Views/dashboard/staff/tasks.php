<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Staff - Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-list-check text-success"></i> My Receiving Tasks</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-info me-3"><?= session('user_role') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="dashboard-widget">
        <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> Assigned Tasks</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>PO #</th>
                        <th>Vendor</th>
                        <th>Warehouse</th>
                        <th>Scheduled</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($tasks) && is_array($tasks)): ?>
                        <?php foreach ($tasks as $t): ?>
                            <tr>
                                <td><?= esc($t['po_number'] ?? '') ?></td>
                                <td><?= esc($t['vendor_name'] ?? '') ?></td>
                                <td><?= esc($t['warehouse_name'] ?? '') ?></td>
                                <td><?= esc($t['scheduled_at'] ?? '') ?></td>
                                <td>
                                    <?php $st = $t['status'] ?? 'pending'; ?>
                                    <span class="badge bg-<?= $st === 'completed' ? 'success' : ($st === 'in_progress' ? 'warning' : 'secondary') ?>">
                                        <?= esc(ucfirst($st)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($t['status'] ?? 'pending') === 'pending'): ?>
                                        <form method="post" action="<?= site_url('dashboard/staff/tasks/' . ($t['id'] ?? 0) . '/start') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Start</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (($t['status'] ?? '') === 'in_progress'): ?>
                                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#receive_<?= esc($t['id']) ?>">Receive Items</button>
                                        <form method="post" action="<?= site_url('dashboard/staff/tasks/' . ($t['id'] ?? 0) . '/complete') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr class="collapse" id="receive_<?= esc($t['id']) ?>">
                                <td colspan="6">
                                    <div class="dashboard-widget mb-0">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label">Item Barcode / Item ID</label>
                                                <input type="text" class="form-control" id="barcode_<?= esc($t['id']) ?>" placeholder="Scan or type...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Qty</label>
                                                <input type="number" class="form-control" id="qty_<?= esc($t['id']) ?>" min="1" value="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Reference (Locked)</label>
                                                <input type="text" class="form-control" id="ref_<?= esc($t['id']) ?>" value="<?= esc($t['po_number'] ?? '') ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary w-100" type="button" onclick="receiveForTask(<?= (int) ($t['id'] ?? 0) ?>, '<?= esc($t['po_number'] ?? '') ?>', <?= (int) ($t['warehouse_id'] ?? 0) ?>)">Submit</button>
                                            </div>
                                        </div>
                                        <small class="text-muted">This will record a Stock IN for the PO reference.</small>
                                        <div class="mt-2" id="msg_<?= esc($t['id']) ?>"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No tasks assigned.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_BASE = '<?= site_url('api') ?>';

async function receiveForTask(taskId, poNumber, warehouseId) {
    const barcode = document.getElementById('barcode_' + taskId).value;
    const qty = parseInt(document.getElementById('qty_' + taskId).value);
    const msg = document.getElementById('msg_' + taskId);

    msg.innerHTML = '';

    if (!barcode || !qty || qty <= 0) {
        msg.innerHTML = '<div class="alert alert-warning">Please enter item and quantity.</div>';
        return;
    }

    try {
        const payload = {
            barcode: barcode,
            quantity: qty
        };

        const response = await fetch(API_BASE + '/warehouse-tasks/' + taskId + '/receive', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (result && result.status === 'success') {
            msg.innerHTML = '<div class="alert alert-success">Stock IN recorded.</div>';
            document.getElementById('barcode_' + taskId).value = '';
            document.getElementById('qty_' + taskId).value = '1';
        } else {
            msg.innerHTML = '<div class="alert alert-danger">Error: ' + (result.message || 'Unknown error') + '</div>';
        }
    } catch (e) {
        msg.innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
    }
}
</script>
</body>
</html>

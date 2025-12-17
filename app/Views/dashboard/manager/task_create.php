<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Receiving Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Receiving Task</h2>
        <a href="<?= site_url('dashboard/manager/tasks') ?>" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="dashboard-widget">
        <div class="row">
            <div class="col-md-6">
                <p><strong>PO #:</strong> <?= esc($po['po_number'] ?? '') ?></p>
                <p><strong>Vendor:</strong> <?= esc($po['vendor_name'] ?? '') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Warehouse:</strong> <?= esc($po['warehouse_name'] ?? '') ?></p>
                <p><strong>Expected:</strong> <?= esc($po['expected_delivery_date'] ?? '-') ?></p>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>UOM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($items) && is_array($items)): ?>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= esc($it['item_name'] ?? '') ?></td>
                                <td><?= esc($it['quantity'] ?? '') ?></td>
                                <td><?= esc($it['unit_of_measure'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <form method="post" action="<?= site_url('dashboard/manager/tasks/' . ($po['id'] ?? 0) . '/create') ?>">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Assign to Staff</label>
                    <select name="assigned_staff_id" class="form-select" required>
                        <option value="">-- Select Staff --</option>
                        <?php if (! empty($staff) && is_array($staff)): ?>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= esc($s['id']) ?>" <?= (old('assigned_staff_id') == $s['id']) ? 'selected' : '' ?>>
                                    <?= esc(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '') . ' (' . ($s['email'] ?? '') . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Warehouse Location</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">-- Select Warehouse --</option>
                        <?php if (! empty($warehouses) && is_array($warehouses)): ?>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= esc($w['id']) ?>" <?= ((old('warehouse_id') ?? ($po['warehouse_id'] ?? null)) == $w['id']) ? 'selected' : '' ?>>
                                    <?= esc($w['warehouse_name'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= esc(old('scheduled_at') ?? '') ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Task</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

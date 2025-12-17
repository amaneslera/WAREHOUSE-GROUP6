<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Create PO from PR</h5>
        <a href="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0)) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <div class="mb-3">
        <strong>PR #:</strong> <?= esc($pr['pr_number'] ?? '') ?>
        <span class="badge bg-success ms-2"><?= esc($pr['status'] ?? '') ?></span>
    </div>

    <form method="post" action="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0) . '/create-po') ?>">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select" required>
                    <option value="">-- Select Vendor --</option>
                    <?php if (! empty($vendors) && is_array($vendors)): ?>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= esc($v['id']) ?>" <?= (old('vendor_id') == $v['id']) ? 'selected' : '' ?>>
                                <?= esc($v['vendor_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Receiving Warehouse</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Select Warehouse --</option>
                    <?php if (! empty($warehouses) && is_array($warehouses)): ?>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= esc($w['id']) ?>" <?= (old('warehouse_id') == $w['id']) ? 'selected' : '' ?>>
                                <?= esc($w['warehouse_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Expected Delivery Date (optional)</label>
                <input type="date" name="expected_delivery_date" class="form-control" value="<?= esc(old('expected_delivery_date') ?? '') ?>">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
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

        <button type="submit" class="btn btn-primary">Create PO</button>
    </form>
</div>
<?= $this->endSection() ?>

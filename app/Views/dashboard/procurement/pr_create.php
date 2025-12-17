<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-plus"></i> Create Purchase Request</h5>
        <a href="<?= site_url('procurement/prs') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <form method="post" action="<?= site_url('procurement/prs/create') ?>">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Warehouse (optional)</label>
                <select name="warehouse_id" class="form-select">
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Notes (optional)</label>
                <input type="text" name="notes" class="form-control" value="<?= esc(old('notes') ?? '') ?>">
            </div>
        </div>

        <div class="dashboard-widget">
            <h6 class="mb-3">Items</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 45%;">Item</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 40%;">Item Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($items) && is_array($items)): ?>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="inventory_item_id[]" value="<?= esc($it['id']) ?>" id="item_<?= esc($it['id']) ?>">
                                            <label class="form-check-label" for="item_<?= esc($it['id']) ?>">
                                                <?= esc($it['item_name']) ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" class="form-control form-control-sm" min="0" value="0">
                                    </td>
                                    <td>
                                        <input type="text" name="item_notes[]" class="form-control form-control-sm" placeholder="Optional">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No inventory items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save PR Draft</button>
    </form>
</div>
<?= $this->endSection() ?>

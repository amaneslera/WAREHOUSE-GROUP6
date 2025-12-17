<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Purchase Orders</h5>
        <a href="<?= site_url('procurement/prs') ?>" class="btn btn-sm btn-outline-primary">Create from Approved PR</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>PO #</th>
                    <th>Vendor</th>
                    <th>Warehouse</th>
                    <th>Status</th>
                    <th>Expected</th>
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
                            <td>
                                <?php $st = $po['status'] ?? 'pending'; ?>
                                <span class="badge bg-<?= $st === 'complete' ? 'success' : ($st === 'partial' ? 'warning' : ($st === 'cancelled' ? 'danger' : 'secondary')) ?>">
                                    <?= esc(ucfirst($st)) ?>
                                </span>
                            </td>
                            <td><?= esc($po['expected_delivery_date'] ?? '-') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('procurement/pos/' . ($po['id'] ?? 0)) ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No POs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

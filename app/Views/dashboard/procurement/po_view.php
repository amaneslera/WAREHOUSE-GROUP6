<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-invoice"></i> PO Details</h5>
        <a href="<?= site_url('procurement/pos') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <p><strong>PO #:</strong> <?= esc($po['po_number'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= esc($po['status'] ?? '') ?></p>
            <p><strong>Vendor:</strong> <?= esc($vendor['vendor_name'] ?? '') ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Warehouse:</strong> <?= esc($warehouse['warehouse_name'] ?? '') ?></p>
            <p><strong>Expected Delivery:</strong> <?= esc($po['expected_delivery_date'] ?? '-') ?></p>
            <p><strong>Created:</strong> <?= esc($po['created_at'] ?? '') ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Item</th>
                    <th>Ordered</th>
                    <th>Received</th>
                    <th>UOM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($items) && is_array($items)): ?>
                    <?php foreach ($items as $it): ?>
                        <?php $recv = (int) ($received_map[(int) ($it['inventory_item_id'] ?? 0)] ?? 0); ?>
                        <tr>
                            <td><?= esc($it['item_name'] ?? '') ?></td>
                            <td><?= esc($it['quantity'] ?? 0) ?></td>
                            <td><?= esc($recv) ?></td>
                            <td><?= esc($it['unit_of_measure'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="dashboard-widget">
        <h6 class="mb-2">Accounts Payable (Read-only)</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Invoice #</th>
                        <th>Status</th>
                        <th>Matching</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($invoices) && is_array($invoices)): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><?= esc($inv['invoice_number'] ?? '') ?></td>
                                <td><?= esc($inv['status'] ?? '') ?></td>
                                <td><?= esc($inv['matching_status'] ?? 'unmatched') ?></td>
                                <td><?= esc($inv['invoice_amount'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No invoices linked to this PO.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

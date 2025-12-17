<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-signature"></i> PR Details</h5>
        <a href="<?= site_url('procurement/prs') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <p><strong>PR #:</strong> <?= esc($pr['pr_number'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= esc($pr['status'] ?? '') ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Created:</strong> <?= esc($pr['created_at'] ?? '') ?></p>
            <?php if (! empty($pr['notes'])): ?>
                <p><strong>Notes:</strong> <?= esc($pr['notes']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($pr['status'] ?? '') === 'draft'): ?>
        <form method="post" action="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0) . '/submit') ?>" class="mb-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success">Submit for Approval</button>
        </form>
    <?php endif; ?>

    <?php if (($pr['status'] ?? '') === 'approved' && empty($po)): ?>
        <a href="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0) . '/create-po') ?>" class="btn btn-primary mb-3">
            Create PO from this PR
        </a>
    <?php endif; ?>

    <?php if (! empty($po)): ?>
        <div class="alert alert-info">
            PO already created: <a href="<?= site_url('procurement/pos/' . ($po['id'] ?? 0)) ?>"><?= esc($po['po_number'] ?? '') ?></a>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>UOM</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($items) && is_array($items)): ?>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= esc($it['item_name'] ?? '') ?></td>
                            <td><?= esc($it['quantity'] ?? '') ?></td>
                            <td><?= esc($it['unit_of_measure'] ?? '') ?></td>
                            <td><?= esc($it['notes'] ?? '') ?></td>
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
</div>
<?= $this->endSection() ?>

<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-danger text-white">
            <h6 class="text-uppercase">Low Stock Alerts</h6>
            <h3><?= esc($low_stock_count ?? 0) ?></h3>
            <small>Items at/below minimum</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-warning text-white">
            <h6 class="text-uppercase">Pending PRs</h6>
            <h3><?= esc($pending_pr_count ?? 0) ?></h3>
            <small>Draft + Submitted</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-primary text-white">
            <h6 class="text-uppercase">Open POs</h6>
            <h3><?= esc($open_po_count ?? 0) ?></h3>
            <small>Pending + Partial</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-secondary text-white">
            <h6 class="text-uppercase">Delayed Deliveries</h6>
            <h3><?= esc($delayed_po_count ?? 0) ?></h3>
            <small>Past expected date</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="dashboard-widget">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-triangle-exclamation"></i> Low Stock Items</h5>
                <a href="<?= site_url('procurement/prs/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Create PR
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Warehouse</th>
                            <th>Current</th>
                            <th>Minimum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($low_stock_items) && is_array($low_stock_items)): ?>
                            <?php foreach ($low_stock_items as $it): ?>
                                <tr>
                                    <td><?= esc($it['item_name'] ?? '') ?></td>
                                    <td><?= esc($it['warehouse_id'] ?? '') ?></td>
                                    <td><?= esc($it['current_stock'] ?? '') ?></td>
                                    <td><?= esc($it['minimum_stock'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No low stock items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-widget">
            <h5 class="mb-3"><i class="fas fa-link"></i> Quick Links</h5>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-primary" href="<?= site_url('procurement/prs') ?>">View Purchase Requests</a>
                <a class="btn btn-outline-primary" href="<?= site_url('procurement/pos') ?>">View Purchase Orders</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('top-management/pr-approvals') ?>">Top Management PR Approvals</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

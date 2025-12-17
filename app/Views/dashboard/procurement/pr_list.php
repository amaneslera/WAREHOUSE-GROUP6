<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-signature"></i> Purchase Requests</h5>
        <a href="<?= site_url('procurement/prs/create') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Create PR
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>PR #</th>
                    <th>Requested By</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($prs) && is_array($prs)): ?>
                    <?php foreach ($prs as $pr): ?>
                        <tr>
                            <td><?= esc($pr['pr_number'] ?? '') ?></td>
                            <td><?= esc(($pr['first_name'] ?? '') . ' ' . ($pr['last_name'] ?? '')) ?></td>
                            <td>
                                <?php $st = $pr['status'] ?? 'draft'; ?>
                                <span class="badge bg-<?= $st === 'approved' ? 'success' : ($st === 'submitted' ? 'warning' : ($st === 'rejected' ? 'danger' : 'secondary')) ?>">
                                    <?= esc(ucfirst($st)) ?>
                                </span>
                            </td>
                            <td><small><?= esc($pr['created_at'] ?? '') ?></small></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0)) ?>">View</a>
                                <?php if (($pr['status'] ?? '') === 'draft'): ?>
                                    <form method="post" action="<?= site_url('procurement/prs/' . ($pr['id'] ?? 0) . '/submit') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success">Submit</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No PRs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

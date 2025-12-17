<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Audit Logs (Read-only)</h5>
    </div>

    <form method="get" action="<?= site_url('it-admin/audit-logs') ?>" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="module" class="form-control form-control-sm" placeholder="Module" value="<?= esc($filters['module'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="action" class="form-control form-control-sm" placeholder="Action" value="<?= esc($filters['action'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="user_id" class="form-control form-control-sm" placeholder="User ID" value="<?= esc($filters['user_id'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('it-admin/audit-logs') ?>">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Record</th>
                </tr>
            </thead>
            <tbody>
            <?php if (isset($logs) && is_array($logs) && count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= esc($log['created_at'] ?? '') ?></small></td>
                        <td>
                            <div><strong><?= esc(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?></strong></div>
                            <small class="text-muted"><?= esc($log['email'] ?? '') ?></small>
                        </td>
                        <td><?= esc($log['module'] ?? '') ?></td>
                        <td><?= esc($log['action'] ?? '') ?></td>
                        <td><small><?= esc($log['description'] ?? '') ?></small></td>
                        <td><small>#<?= esc($log['record_id'] ?? '') ?></small></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No audit logs found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-primary text-white">
            <h6 class="text-uppercase">Total Users</h6>
            <h3><?= esc($total_users ?? 0) ?></h3>
            <small>All accounts</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-success text-white">
            <h6 class="text-uppercase">Active Users</h6>
            <h3><?= ($active_users === null) ? 'N/A' : esc($active_users) ?></h3>
            <small><?= ($active_users === null) ? 'Run migrations' : 'Enabled accounts' ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-secondary text-white">
            <h6 class="text-uppercase">Deactivated</h6>
            <h3><?= ($inactive_users === null) ? 'N/A' : esc($inactive_users) ?></h3>
            <small><?= ($inactive_users === null) ? 'Run migrations' : 'Soft-disabled accounts' ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-widget stat-card bg-info text-white">
            <h6 class="text-uppercase">Last System Login</h6>
            <h3 style="font-size: 16px;"><?= ! empty($last_login_at) ? esc($last_login_at) : 'N/A' ?></h3>
            <small>From users table</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="dashboard-widget">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-users"></i> User Administration</h5>
                <a href="<?= site_url('it-admin/users') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right"></i> Manage Users
                </a>
            </div>
            <p class="mb-0 text-muted">Activate/deactivate accounts and assign roles (no permanent delete).</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dashboard-widget">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Recent Admin Actions</h5>
                <a href="<?= site_url('it-admin/audit-logs') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-right"></i> View Logs
                </a>
            </div>

            <?php if (isset($recent_actions) && is_array($recent_actions) && count($recent_actions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Module</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_actions as $a): ?>
                                <tr>
                                    <td><small><?= esc($a['created_at'] ?? '') ?></small></td>
                                    <td><small><?= esc($a['module'] ?? '') ?></small></td>
                                    <td><small><?= esc($a['action'] ?? '') ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">No admin actions found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

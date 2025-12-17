<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <h5 class="mb-3"><i class="fas fa-heartbeat"></i> System Status (Read-only)</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-widget stat-card <?= ($db_ok ?? false) ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                <h6 class="text-uppercase">Database Connection</h6>
                <h3><?= ($db_ok ?? false) ? 'OK' : 'FAILED' ?></h3>
                <small>Read-only health check</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-widget stat-card bg-secondary text-white">
                <h6 class="text-uppercase">Backup Timestamp</h6>
                <h3><?= $backup_timestamp ? esc($backup_timestamp) : 'N/A' ?></h3>
                <small>No destructive actions available</small>
            </div>
        </div>
    </div>

    <div class="dashboard-widget">
        <h6 class="mb-2">Last System Login</h6>
        <p class="mb-0">
            <?php if (! empty($last_login_at)): ?>
                <?= esc($last_login_at) ?>
            <?php else: ?>
                <span class="text-muted">No login data available.</span>
            <?php endif; ?>
        </p>
    </div>
</div>
<?= $this->endSection() ?>

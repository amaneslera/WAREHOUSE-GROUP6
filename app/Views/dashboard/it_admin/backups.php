<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-database"></i> Backups</h5>
        <form method="post" action="<?= site_url('it-admin/backups/run') ?>" class="mb-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-play"></i> Run Backup Now
            </button>
        </form>
    </div>

    <div class="text-muted mb-3">
        This creates a database backup file using <code>mysqldump</code> and stores it inside <code>writable/backups</code>.
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>File</th>
                    <th>Created</th>
                    <th>Size</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($backups) && is_array($backups) && count($backups) > 0): ?>
                    <?php foreach ($backups as $b): ?>
                        <tr>
                            <td><?= esc($b['name'] ?? '') ?></td>
                            <td><?= esc($b['created_at'] ?? '') ?></td>
                            <td><?= esc($b['size_human'] ?? '') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('it-admin/backups/download/' . ($b['name'] ?? '')) ?>">
                                    Download
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No backups found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

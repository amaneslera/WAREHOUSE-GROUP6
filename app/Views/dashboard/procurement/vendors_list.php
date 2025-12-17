<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-truck"></i> Vendors</h5>
        <a href="<?= site_url('procurement/vendors/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> New Vendor</a>
    </div>

    <?php $errors = session('errors'); ?>
    <?php if (! empty($errors) && is_array($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Payment Terms</th>
                    <th>Status</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($vendors) && is_array($vendors)): ?>
                    <?php foreach ($vendors as $v): ?>
                        <?php $st = $v['status'] ?? 'inactive'; ?>
                        <tr>
                            <td><?= esc($v['vendor_code'] ?? '') ?></td>
                            <td><?= esc($v['vendor_name'] ?? '') ?></td>
                            <td><?= esc($v['contact_person'] ?? '-') ?></td>
                            <td><?= esc($v['email'] ?? '-') ?></td>
                            <td><?= esc($v['phone'] ?? '-') ?></td>
                            <td><?= esc($v['payment_terms'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $st === 'active' ? 'success' : ($st === 'blocked' ? 'danger' : 'secondary') ?>">
                                    <?= esc(ucfirst($st)) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('procurement/vendors/edit/' . ($v['id'] ?? 0)) ?>">Edit</a>

                                    <form method="post" action="<?= site_url('procurement/vendors/' . ($v['id'] ?? 0) . '/status') ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <select name="status" class="form-select form-select-sm" style="min-width: 120px;">
                                            <option value="active" <?= ($st === 'active') ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($st === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                            <option value="blocked" <?= ($st === 'blocked') ? 'selected' : '' ?>>Blocked</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No vendors found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

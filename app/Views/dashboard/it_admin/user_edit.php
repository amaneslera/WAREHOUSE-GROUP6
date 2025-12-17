<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-user-pen"></i> Edit User</h5>
        <a href="<?= site_url('it-admin/users') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <?php if (isset($validation)): ?>
        <div class="alert alert-danger">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('it-admin/users/' . ($user['id'] ?? '') . '/edit') ?>">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= esc(old('last_name') ?? ($user['last_name'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= esc(old('first_name') ?? ($user['first_name'] ?? '')) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Middle Name</label>
                <input type="text" name="middle_name" class="form-control" value="<?= esc(old('middle_name') ?? ($user['middle_name'] ?? '')) ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email') ?? ($user['email'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <?php foreach ($roles as $roleKey => $roleLabel): ?>
                        <option value="<?= esc($roleKey) ?>" <?= ((old('role') ?? ($user['role'] ?? '')) === $roleKey) ? 'selected' : '' ?>>
                            <?= esc($roleLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (array_key_exists('is_active', $user)): ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= ((string) (old('is_active') ?? (string) ($user['is_active'] ?? '1')) === '1') ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ((string) (old('is_active') ?? (string) ($user['is_active'] ?? '1')) === '0') ? 'selected' : '' ?>>Deactivated</option>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">New Password (optional)</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirm" class="form-control" placeholder="Only required if changing password">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->extend('dashboard/it_admin/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-users"></i> User Management</h5>
        <a href="<?= site_url('it-admin/users/create') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-user-plus"></i> Create User
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="width: 340px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (isset($users) && is_array($users) && count($users) > 0): ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= esc($u['id']) ?></td>
                        <td><?= esc(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?></td>
                        <td><?= esc($u['email'] ?? '') ?></td>
                        <td>
                            <form method="post" action="<?= site_url('it-admin/users/' . $u['id'] . '/role') ?>" class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <select name="role" class="form-select form-select-sm" <?= ((int) $u['id'] === (int) session('user_id')) ? '' : '' ?>>
                                    <?php foreach ($roles as $roleKey => $roleLabel): ?>
                                        <option value="<?= esc($roleKey) ?>" <?= (($u['role'] ?? '') === $roleKey) ? 'selected' : '' ?>>
                                            <?= esc($roleLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                        <td>
                            <?php if (array_key_exists('is_active', $u)): ?>
                                <?php if ((int) $u['is_active'] === 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Deactivated</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (array_key_exists('last_login_at', $u) && $u['last_login_at']): ?>
                                <small><?= esc($u['last_login_at']) ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) $u['id'] === (int) session('user_id')): ?>
                                <span class="text-muted">Self-protected</span>
                            <?php else: ?>
                                <a href="<?= site_url('it-admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-2">
                                    Edit
                                </a>

                                <?php if (array_key_exists('is_active', $u)): ?>
                                    <form method="post" action="<?= site_url('it-admin/users/' . $u['id'] . '/status') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="is_active" value="<?= ((int) $u['is_active'] === 1) ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= ((int) $u['is_active'] === 1) ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                            <?= ((int) $u['is_active'] === 1) ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Status not available</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

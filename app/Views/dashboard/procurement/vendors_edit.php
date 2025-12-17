<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-truck"></i> Edit Vendor</h5>
        <a href="<?= site_url('procurement/vendors') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
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

    <form method="post" action="<?= site_url('procurement/vendors/edit/' . ($vendor['id'] ?? 0)) ?>">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Vendor Code</label>
                <input type="text" class="form-control" value="<?= esc($vendor['vendor_code'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control" value="<?= esc(old('vendor_name') ?? ($vendor['vendor_name'] ?? '')) ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="<?= esc(old('contact_person') ?? ($vendor['contact_person'] ?? '')) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email') ?? ($vendor['email'] ?? '')) ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= esc(old('phone') ?? ($vendor['phone'] ?? '')) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Terms</label>
                <input type="text" name="payment_terms" class="form-control" value="<?= esc(old('payment_terms') ?? ($vendor['payment_terms'] ?? '')) ?>" placeholder="e.g. Net 30, COD">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3"><?= esc(old('address') ?? ($vendor['address'] ?? '')) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <?php $st = old('status') ?? ($vendor['status'] ?? 'active'); ?>
            <select name="status" class="form-select" required>
                <option value="active" <?= ($st === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($st === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                <option value="blocked" <?= ($st === 'blocked') ? 'selected' : '' ?>>Blocked</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?= $this->endSection() ?>

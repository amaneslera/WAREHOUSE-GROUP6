<?= $this->extend('dashboard/procurement/_layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-truck"></i> Create Vendor</h5>
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

    <form method="post" action="<?= site_url('procurement/vendors/create') ?>">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control" value="<?= esc(old('vendor_name') ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="<?= esc(old('contact_person') ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email') ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= esc(old('phone') ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3"><?= esc(old('address') ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Payment Terms</label>
                <input type="text" name="payment_terms" class="form-control" value="<?= esc(old('payment_terms') ?? '') ?>" placeholder="e.g. Net 30, COD">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <?php $st = old('status') ?? 'active'; ?>
                    <option value="active" <?= ($st === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($st === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    <option value="blocked" <?= ($st === 'blocked') ? 'selected' : '' ?>>Blocked</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Vendor</button>
    </form>
</div>
<?= $this->endSection() ?>

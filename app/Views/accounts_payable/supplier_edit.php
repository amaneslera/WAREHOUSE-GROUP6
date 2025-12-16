<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Supplier</h2>
        <a href="<?= site_url('supplier-management') ?>" class="btn btn-secondary">Back to Suppliers</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Supplier Details</h5>
        </div>
        <div class="card-body">
            <form action="<?= site_url('supplier-management/update/' . $supplier['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vendor_code" class="form-label">Vendor Code</label>
                            <input type="text" class="form-control" id="vendor_code" name="vendor_code" value="<?= $supplier['vendor_code'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vendor_name" class="form-label">Vendor Name</label>
                            <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="<?= $supplier['vendor_name'] ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?= $supplier['contact_person'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $supplier['email'] ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= $supplier['phone'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $supplier['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $supplier['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="blocked" <?= $supplier['status'] == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= $supplier['address'] ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tax_id" class="form-label">Tax ID</label>
                            <input type="text" class="form-control" id="tax_id" name="tax_id" value="<?= $supplier['tax_id'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_terms" class="form-label">Payment Terms</label>
                            <input type="text" class="form-control" id="payment_terms" name="payment_terms" value="<?= $supplier['payment_terms'] ?>" placeholder="e.g., Net 30, Net 60">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Supplier</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>

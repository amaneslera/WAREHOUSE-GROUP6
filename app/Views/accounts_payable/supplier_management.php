<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Management</h2>
        <a href="<?= site_url('dashboard/apclerk') ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary"><?= $active_count ?></h5>
                    <p class="card-text">Active Suppliers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">$<?= number_format($total_outstanding, 2) ?></h5>
                    <p class="card-text">Total Outstanding</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info"><?= $avg_payment_terms ?></h5>
                    <p class="card-text">Average Payment Terms</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Directory Table -->
    <div class="card">
        <div class="card-header">
            <h5>Supplier Directory</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Terms</th>
                            <th>Amount Owed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= $supplier['vendor_name'] ?></td>
                            <td><?= $supplier['contact_person'] ?: 'N/A' ?></td>
                            <td><?= $supplier['email'] ?: 'N/A' ?></td>
                            <td><?= $supplier['phone'] ?: 'N/A' ?></td>
                            <td><?= $supplier['payment_terms'] ?: 'N/A' ?></td>
                            <td>$<?= number_format($supplier['stats']['total_balance'] ?? 0, 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $supplier['status'] == 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($supplier['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('supplier-management/edit/' . $supplier['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

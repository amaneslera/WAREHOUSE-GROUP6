<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Recording</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payment Recording</h2>
        <div>
            <a href="<?= site_url('payment-recording/create') ?>" class="btn btn-primary">Record New Payment</a>
            <a href="<?= site_url('dashboard/apclerk') ?>" class="btn btn-secondary">Back to Dashboard</a>
        </div>
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
                    <h5 class="card-title text-success">$<?= number_format($total_today, 2) ?></h5>
                    <p class="card-text">Total Payments Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">$<?= number_format($total_month, 2) ?></h5>
                    <p class="card-text">Total Payments This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning"><?= $pending_count ?></h5>
                    <p class="card-text">Pending Payments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="card">
        <div class="card-header">
            <h5>Payment History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= $payment['invoice_number'] ?></td>
                            <td><?= $payment['vendor_name'] ?></td>
                            <td>$<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                            <td><?= $payment['payment_method'] ?></td>
                            <td><?= $payment['reference_number'] ?: 'N/A' ?></td>
                            <td><span class="badge bg-success">Completed</span></td>
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

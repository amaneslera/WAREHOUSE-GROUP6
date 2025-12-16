<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Payable Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Accounts Payable Clerk Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Processes supplier invoices, records payments, and updates the central office system.</p>
    
    <div class="row">
        <div class="col-md-6">
                <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Invoice Processing</h5>
                    <p class="card-text">Process and review supplier invoices.</p>
                    <a href="<?= site_url('invoice-management') ?>" class="btn btn-primary">View Invoices</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Payment Records</h5>
                    <p class="card-text">Record and track payments to suppliers.</p>
                    <a href="<?= site_url('payment-recording') ?>" class="btn btn-primary">Manage Payments</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

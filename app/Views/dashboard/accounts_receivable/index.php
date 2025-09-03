<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Receivable Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Accounts Receivable Clerk Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Issues billing to clients, records payments received, and follows up on unpaid dues.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Client Billing</h5>
                    <p class="card-text">Issue invoices and billing to clients.</p>
                    <a href="#" class="btn btn-primary">Manage Billing</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Payment Tracking</h5>
                    <p class="card-text">Track payments and follow up on unpaid dues.</p>
                    <a href="#" class="btn btn-primary">Track Payments</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

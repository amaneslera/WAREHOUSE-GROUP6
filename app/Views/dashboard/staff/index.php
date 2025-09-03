<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warehouse Staff Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Scans items in/out, updates the system with stock changes, and assists in physical counts.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Scan Items</h5>
                    <p class="card-text">Scan items in and out of the warehouse.</p>
                    <a href="#" class="btn btn-primary">Start Scanning</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Physical Count</h5>
                    <p class="card-text">Assist in inventory physical counting.</p>
                    <a href="#" class="btn btn-primary">Start Count</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

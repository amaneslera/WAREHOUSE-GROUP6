<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Procurement Officer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Procurement Officer Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Orders materials, ensures suppliers deliver on time, and coordinates with accounts payable.</p>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Purchase Orders</h5>
                    <p class="card-text">Create and manage purchase orders.</p>
                    <a href="#" class="btn btn-primary">Manage Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Suppliers</h5>
                    <p class="card-text">Manage supplier relationships and deliveries.</p>
                    <a href="#" class="btn btn-primary">View Suppliers</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Delivery Tracking</h5>
                    <p class="card-text">Track supplier deliveries and schedules.</p>
                    <a href="#" class="btn btn-primary">Track Deliveries</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

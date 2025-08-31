<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Warehouse System</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <span class="navbar-text">Manager Dashboard</span>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text me-3">
                        Welcome, <?= session('user_lname') . ', ' . session('user_fname') ?>!
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('logout') ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2>Warehouse Manager Dashboard</h2>
            <p class="lead">Oversees inventory at each warehouse, verifies stock levels, and approves stock movements.</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Inventory Overview</h5>
                    <p class="card-text">View and manage warehouse inventory levels.</p>
                    <a href="#" class="btn btn-primary">View Inventory</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Stock Movements</h5>
                    <p class="card-text">Review and approve stock transfer requests.</p>
                    <a href="#" class="btn btn-primary">Manage Movements</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Reports</h5>
                    <p class="card-text">Generate warehouse performance reports.</p>
                    <a href="#" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

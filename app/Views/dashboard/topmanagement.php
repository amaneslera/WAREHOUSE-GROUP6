<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Warehouse System</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <span class="navbar-text">Management Dashboard</span>
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
            <h2>Top Management Dashboard</h2>
            <p class="lead">Views centralized dashboards, reviews financial and inventory reports, and makes decisions.</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total Inventory Value</h6>
                    <h3 class="card-title text-success">$310,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Total Items</h6>
                    <h3 class="card-title text-primary">145</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Pending Orders</h6>
                    <h3 class="card-title text-warning">12</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Outstanding Dues</h6>
                    <h3 class="card-title text-danger">$45,000</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Financial Reports</h5>
                    <p class="card-text">Review comprehensive financial analytics and reports.</p>
                    <a href="#" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Inventory Reports</h5>
                    <p class="card-text">Review inventory status across all warehouses.</p>
                    <a href="#" class="btn btn-primary">View Inventory</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Procurement Overview</h5>
                    <p class="card-text">Monitor supplier performance and purchase orders.</p>
                    <a href="#" class="btn btn-primary">View Procurement</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Audit Logs</h5>
                    <p class="card-text">Review system activity and audit trails.</p>
                    <a href="#" class="btn btn-primary">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('js/topmanagement-dashboard.js') ?>"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Top Management Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Oversees overall operations, strategic planning, and decision-making for the warehouse.</p>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Strategic Overview</h5>
                    <p class="card-text">View overall performance and strategic metrics.</p>
                    <a href="#" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Operations Management</h5>
                    <p class="card-text">Monitor and manage all warehouse operations.</p>
                    <a href="#" class="btn btn-primary">Operations</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Financial Overview</h5>
                    <p class="card-text">Review financial performance and budgets.</p>
                    <a href="#" class="btn btn-primary">Financials</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Staff Management</h5>
                    <p class="card-text">Oversee staff performance and organizational structure.</p>
                    <a href="#" class="btn btn-primary">Staff Overview</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Decision Support</h5>
                    <p class="card-text">Access tools and data for strategic decision-making.</p>
                    <a href="#" class="btn btn-primary">Decision Tools</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>IT Administrator Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Manages the warehouse's IT systems, equipment, and software maintenance.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">System Management</h5>
                    <p class="card-text">Monitor and manage IT systems and infrastructure.</p>
                    <a href="#" class="btn btn-primary">Manage Systems</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Software Maintenance</h5>
                    <p class="card-text">Handle software updates and maintenance tasks.</p>
                    <a href="#" class="btn btn-primary">Software Tools</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">User Management</h5>
                    <p class="card-text">Manage user accounts and access permissions.</p>
                    <a href="#" class="btn btn-primary">User Admin</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Security</h5>
                    <p class="card-text">Monitor security and implement protective measures.</p>
                    <a href="#" class="btn btn-primary">Security Center</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

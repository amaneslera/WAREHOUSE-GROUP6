<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auditor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Auditor Dashboard</h2>
        <div class="d-flex align-items-center">
            <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
    
    <p class="lead">Conducts regular checks and reconciliations of physical vs. system records.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Audit Records</h5>
                    <p class="card-text">Review and conduct inventory audits.</p>
                    <a href="#" class="btn btn-primary">Start Audit</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Reconciliation</h5>
                    <p class="card-text">Reconcile physical vs system records.</p>
                    <a href="#" class="btn btn-primary">View Discrepancies</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

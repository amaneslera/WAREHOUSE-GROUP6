<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'IT Administrator') ?> - WeBuild</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-shield-halved text-primary"></i> IT Administrator</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-primary me-3"><?= session('user_role') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 text-center text-white mb-3">
                <h5><i class="fas fa-bars"></i> Navigation</h5>
            </div>
            <a href="<?= site_url('it-admin') ?>" class="<?= (isset($active) && $active === 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= site_url('it-admin/users') ?>" class="<?= (isset($active) && $active === 'users') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> User Management
            </a>
            <a href="<?= site_url('it-admin/audit-logs') ?>" class="<?= (isset($active) && $active === 'audit') ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Audit Logs
            </a>
            <a href="<?= site_url('it-admin/backups') ?>" class="<?= (isset($active) && $active === 'backups') ? 'active' : '' ?>">
                <i class="fas fa-database"></i> Backups
            </a>
            <a href="<?= site_url('it-admin/system-status') ?>" class="<?= (isset($active) && $active === 'status') ? 'active' : '' ?>">
                <i class="fas fa-heartbeat"></i> System Status
            </a>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="col-md-10 p-4">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>

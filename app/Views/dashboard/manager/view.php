<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Item - Webuild Inventory</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link href="<?= base_url('css/manager.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h2>Webuild</h2>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= site_url('inventory') ?>">
                                Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('inventory/add') ?>">
                                Add Item
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Stock movements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Material Tracking
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Batch & Lot Tracking
                            </a>
                        </li>
                    </ul>
                    
                    <div class="role-info mt-4 p-3">
                        <h6>Role:</h6>
                        <div class="badge bg-secondary">Warehouse manager</div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header with user info -->
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Item Details</h1>
                    <div class="d-flex align-items-center">
                        <a href="<?= site_url('inventory') ?>" class="btn btn-secondary me-3">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                        <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
                        <a href="<?= site_url('logout') ?>" class="btn btn-outline-secondary btn-sm">Logout</a>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Item Details -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><?= esc($item['item_id']) ?> - <?= esc($item['item_name']) ?></h5>
                                <div>
                                    <a href="<?= site_url('inventory/edit/' . $item['id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= site_url('inventory/delete/' . $item['id']) ?>" class="btn btn-outline-secondary btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Item ID:</th>
                                                <td><?= esc($item['item_id']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Item Name:</th>
                                                <td><?= esc($item['item_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Category:</th>
                                                <td><?= esc($item['category_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Warehouse:</th>
                                                <td><?= esc($item['warehouse_name']) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Current Stock:</th>
                                                <td><?= number_format($item['current_stock']) ?> units</td>
                                            </tr>
                                            <tr>
                                                <th>Unit Price:</th>
                                                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Value:</th>
                                                <td><strong>$<?= number_format($item['current_stock'] * $item['unit_price'], 2) ?></strong></td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <?php
                                                    $statusClass = 'bg-success';
                                                    $statusText = 'Good Stock';
                                                    
                                                    if ($item['current_stock'] == 0) {
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Out of Stock';
                                                    } elseif ($item['current_stock'] <= $item['minimum_stock']) {
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'Low Stock';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Description:</h6>
                                        <p class="text-muted"><?= !empty($item['description']) ? esc($item['description']) : 'No description available.' ?></p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Created: <?= date('Y-m-d H:i A', strtotime($item['created_at'])) ?></small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">Last Updated: <?= date('Y-m-d H:i A', strtotime($item['updated_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Stock
                                    </button>
                                    <button class="btn btn-warning">
                                        <i class="fas fa-minus"></i> Remove Stock
                                    </button>
                                    <button class="btn btn-info">
                                        <i class="fas fa-exchange-alt"></i> Transfer
                                    </button>
                                    <button class="btn btn-secondary">
                                        <i class="fas fa-history"></i> View History
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Stock Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-success"><?= number_format($item['current_stock']) ?></h4>
                                        <small>Current</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-warning"><?= number_format($item['minimum_stock']) ?></h4>
                                        <small>Minimum</small>
                                    </div>
                                </div>
                                <div class="progress mt-3">
                                    <?php
                                    $percentage = $item['minimum_stock'] > 0 ? ($item['current_stock'] / $item['minimum_stock']) * 100 : 100;
                                    $percentage = min(100, $percentage); // Cap at 100%
                                    $progressClass = $percentage > 100 ? 'bg-success' : ($percentage > 50 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                         style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">Stock Level: 
                                    <?= $item['current_stock'] == 0 ? 'Out of Stock' : 
                                        ($item['current_stock'] <= $item['minimum_stock'] ? 'Low Stock' : 'Good Stock') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

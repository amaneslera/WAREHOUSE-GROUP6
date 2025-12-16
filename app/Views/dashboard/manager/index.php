<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webuild - Inventory Management</title>
    
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
                    <h1>Inventory management</h1>
                    <div class="d-flex align-items-center">
                        <a href="<?= site_url('inventory/add') ?>" class="btn btn-primary me-3">
                            <i class="fas fa-plus"></i> Add New Item
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

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Warehouse Cards -->
                <div class="row mb-4">
                    <?php if (!empty($warehouse_stats)): ?>
                        <?php foreach ($warehouse_stats as $warehouse_name => $stats): ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">WAREHOUSE - <?= esc($warehouse_name) ?></h5>
                                        <h2 class="card-subtitle mb-2"><?= number_format($stats['total_items']) ?></h2>
                                        <p class="text-muted">Total Items in Stock</p>
                                        <h3 class="text-primary">$<?= number_format($stats['total_value'], 2) ?></h3>
                                        <p class="text-muted mb-0">Total Value (<?= $stats['item_count'] ?> types)</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No warehouse data available.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Filter Tabs -->
                <div class="mb-4">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">All Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Low Stock Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">By Category</a>
                        </li>
                    </ul>
                </div>

                <!-- Search Bar -->
                <div class="row mb-4">
                    <div class="col">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search inventory...">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                All warehouses
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">All warehouses</a></li>
                                <li><a class="dropdown-item" href="#">Building A</a></li>
                                <li><a class="dropdown-item" href="#">Building B</a></li>
                                <li><a class="dropdown-item" href="#">Building C</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Warehouses</th>
                                <th>Current stock</th>
                                <th>Unit Price</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    // Determine status badge based on stock levels
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
                                    <tr>
                                        <td><?= esc($item['item_id']) ?></td>
                                        <td><?= esc($item['item_name']) ?></td>
                                        <td><?= esc($item['category_name']) ?></td>
                                        <td><?= esc($item['warehouse_name']) ?></td>
                                        <td><?= number_format($item['current_stock']) ?></td>
                                        <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                        <td><?= date('Y-m-d', strtotime($item['updated_at'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= site_url('inventory/view/' . $item['id']) ?>" class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="<?= site_url('inventory/edit/' . $item['id']) ?>" class="btn btn-sm btn-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="<?= site_url('inventory/delete/' . $item['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Delete" 
                                                   onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No inventory items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

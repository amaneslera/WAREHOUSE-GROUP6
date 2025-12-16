<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - Webuild Inventory</title>
    
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
                            <a class="nav-link" href="<?= site_url('inventory') ?>">
                                Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= site_url('inventory/add') ?>">
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
                    <h1>Add New Inventory Item</h1>
                    <div class="d-flex align-items-center">
                        <a href="<?= site_url('inventory') ?>" class="btn btn-secondary me-3">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
                        </a>
                        <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
                        <a href="<?= site_url('logout') ?>" class="btn btn-outline-secondary btn-sm">Logout</a>
                    </div>
                </div>

                <!-- Add Item Form -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Item Information</h5>
                            </div>
                            <div class="card-body">
                                
                                <?php if (session()->getFlashdata('error')): ?>
                                    <div class="alert alert-danger">
                                        <?= session()->getFlashdata('error') ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (session()->getFlashdata('validation')): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach (session()->getFlashdata('validation') as $error): ?>
                                                <li><?= esc($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form action="<?= site_url('inventory/create') ?>" method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="item_id" class="form-label">Item ID</label>
                                                <input type="text" class="form-control" id="item_id" name="item_id" 
                                                       value="<?= old('item_id') ?>" placeholder="INV001" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="item_name" class="form-label">Item Name</label>
                                                <input type="text" class="form-control" id="item_name" name="item_name" 
                                                       value="<?= old('item_name') ?>" placeholder="Steel" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category</label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $id => $name): ?>
                                                        <option value="<?= $id ?>" <?= old('category_id') == $id ? 'selected' : '' ?>>
                                                            <?= esc($name) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="warehouse_id" class="form-label">Warehouse</label>
                                                <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                                    <option value="">Select Warehouse</option>
                                                    <?php foreach ($warehouses as $id => $name): ?>
                                                        <option value="<?= $id ?>" <?= old('warehouse_id') == $id ? 'selected' : '' ?>>
                                                            <?= esc($name) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="current_stock" class="form-label">Current Stock</label>
                                                <input type="number" class="form-control" id="current_stock" name="current_stock" 
                                                       value="<?= old('current_stock') ?>" placeholder="150" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="unit_price" class="form-label">Unit Price ($)</label>
                                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" 
                                                       value="<?= old('unit_price') ?>" placeholder="450.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="minimum_stock" class="form-label">Minimum Stock Level</label>
                                                <input type="number" class="form-control" id="minimum_stock" name="minimum_stock" 
                                                       value="<?= old('minimum_stock') ?>" placeholder="50" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Item description..."><?= old('description') ?></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <a href="<?= site_url('inventory') ?>" class="btn btn-secondary me-2">Cancel</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Add Item
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Quick Tips</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-info-circle text-primary"></i> Use unique Item IDs</li>
                                    <li><i class="fas fa-info-circle text-primary"></i> Set appropriate minimum stock levels</li>
                                    <li><i class="fas fa-info-circle text-primary"></i> Choose the correct warehouse location</li>
                                    <li><i class="fas fa-info-circle text-primary"></i> Add detailed descriptions for clarity</li>
                                </ul>
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

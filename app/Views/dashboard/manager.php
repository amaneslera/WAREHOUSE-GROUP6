<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webuild - Inventory Management</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
                            <a class="nav-link active" href="#">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Stock movements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Inventory
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
                        <span class="me-3">Welcome, <?= session('user_fname') . ' ' . session('user_lname') ?></span>
                        <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger btn-sm">Logout</a>
                    </div>
                </div>

                <!-- Warehouse Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">WAREHOUSE - Building A</h5>
                                <h2 class="card-subtitle mb-2">45</h2>
                                <p class="text-muted">Total Items in Stock</p>
                                <h3 class="text-primary">$125,000</h3>
                                <p class="text-muted mb-0">Total Value</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">WAREHOUSE - Building B</h5>
                                <h2 class="card-subtitle mb-2">62</h2>
                                <p class="text-muted">Total Items in Stock</p>
                                <h3 class="text-primary">$98,000</h3>
                                <p class="text-muted mb-0">Total Value</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">WAREHOUSE - Building C</h5>
                                <h2 class="card-subtitle mb-2">38</h2>
                                <p class="text-muted">Total Items in Stock</p>
                                <h3 class="text-primary">$87,000</h3>
                                <p class="text-muted mb-0">Total Value</p>
                            </div>
                        </div>
                    </div>
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>INV 001</td>
                                <td>Steel</td>
                                <td>Construction materials</td>
                                <td>Building A</td>
                                <td>150</td>
                                <td>$450</td>
                                <td><span class="badge bg-success">Good Stock</span></td>
                                <td>2025-07-23</td>
                            </tr>
                           
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
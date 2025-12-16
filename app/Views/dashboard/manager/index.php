<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard - WeBuild</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .approval-badge { display: inline-block; padding: 8px 16px; border-radius: 50px; font-weight: 600; }
        .approval-badge.pending { background-color: #fff3cd; color: #856404; }
        .approval-badge.approved { background-color: #d4edda; color: #155724; }
        .approval-badge.rejected { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-warehouse text-primary"></i> Warehouse Manager Dashboard</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-success me-3"><?= session('user_role') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR NAVIGATION -->
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 text-center text-white mb-3">
                <h5><i class="fas fa-bars"></i> Navigation</h5>
            </div>
            <a href="<?= site_url('dashboard/manager') ?>" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= site_url('dashboard/manager/approvals') ?>">
                <i class="fas fa-check-square"></i> Approvals
            </a>
            <a href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes"></i> Inventory
            </a>
            <a href="<?= site_url('stock-movements') ?>">
                <i class="fas fa-exchange-alt"></i> Stock Movements
            </a>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">
            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-primary text-white">
                        <h6 class="text-uppercase">Total Inventory Value</h6>
                        <h3 id="totalValue">$0.00</h3>
                        <small>All warehouses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-info text-white">
                        <h6 class="text-uppercase">Total Items</h6>
                        <h3 id="totalItems">0</h3>
                        <small>In stock</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-warning text-white">
                        <h6 class="text-uppercase">Pending Approvals</h6>
                        <h3 id="pendingCount">0</h3>
                        <small>Awaiting your action</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-widget stat-card bg-danger text-white">
                        <h6 class="text-uppercase">Low Stock Items</h6>
                        <h3 id="lowStockCount">0</h3>
                        <small>Below threshold</small>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Section -->
            <div class="dashboard-widget mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-clock text-warning"></i> Pending Stock Movement Approvals</h5>
                    <a href="<?= site_url('dashboard/manager/approvals') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> View All
                    </a>
                </div>
                <div id="pendingApprovalsContainer">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i> Loading approvals...
                    </div>
                </div>
            </div>

            <!-- Warehouse Overview -->
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-building"></i> Warehouse Inventory Summary</h5>
                </div>
                <div id="warehouseCards"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_BASE = '<?= site_url('api') ?>';

// Load dashboard data
async function loadDashboard() {
    await loadDashboardStats();
    await loadPendingApprovals();
    await loadWarehouseCards();
}

// Load key metrics
async function loadDashboardStats() {
    try {
        const response = await fetch(`${API_BASE}/reports/inventory/summary`);
        const data = await response.json();
        
        if (data.status === 'success') {
            document.getElementById('totalValue').textContent = '$' + parseFloat(data.total_value || 0).toFixed(2);
            document.getElementById('totalItems').textContent = data.total_items || 0;
            document.getElementById('lowStockCount').textContent = data.low_stock_count || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load pending approvals
async function loadPendingApprovals() {
    try {
        const response = await fetch(`${API_BASE}/approvals/pending`);
        const data = await response.json();
        
        if (data.status === 'success') {
            document.getElementById('pendingCount').textContent = data.count || 0;
            const container = document.getElementById('pendingApprovalsContainer');
            
            if (!data.data || data.data.length === 0) {
                container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> No pending approvals</div>';
                return;
            }

            let html = '';
            data.data.slice(0, 5).forEach(movement => {
                html += `<div class="card mb-2 border-left border-warning">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-0">${movement.item_name || 'Unknown Item'}</h6>
                                <small class="text-muted">
                                    <strong>${(movement.movement_type || 'Unknown').toUpperCase()}</strong> - 
                                    ${movement.quantity} units
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-warning text-dark">PENDING</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading approvals:', error);
    }
}

// Load warehouse cards
async function loadWarehouseCards() {
    try {
        const response = await fetch(`${API_BASE}/warehouses`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const container = document.getElementById('warehouseCards');
            let html = '';
            
            data.data.forEach(warehouse => {
                html += `<div class="col-md-4 mb-3">
                    <div class="dashboard-widget">
                        <h6><i class="fas fa-warehouse"></i> ${warehouse.warehouse_name || 'Unknown'}</h6>
                        <p class="mb-1"><strong>Items:</strong> ${warehouse.item_count || 0}</p>
                        <p class="mb-1"><strong>Total Value:</strong> $${parseFloat(warehouse.total_value || 0).toFixed(2)}</p>
                        <p class="mb-0"><strong>Capacity:</strong> ${warehouse.capacity_used || 0}%</p>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading warehouses:', error);
    }
}

// Load data on page load
document.addEventListener('DOMContentLoaded', loadDashboard);

// Auto-refresh approvals every 30 seconds
setInterval(loadPendingApprovals, 30000);
</script>
</body>
</html>

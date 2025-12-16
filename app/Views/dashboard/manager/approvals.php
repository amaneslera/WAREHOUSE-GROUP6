<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager - Inventory Management & Approvals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; background: none; text-align: left; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .approval-card { border-left: 4px solid #ff9800; }
        .approval-card.approved { border-left-color: #28a745; }
        .approval-card.rejected { border-left-color: #dc3545; }
        .badge-pending { background-color: #ff9800; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
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
            <ul class="nav flex-column">
                <li class="nav-item">
                    <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
                        <i class="fas fa-home"></i> Dashboard
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="fas fa-boxes"></i> Inventory
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="approvals-tab" data-bs-toggle="tab" data-bs-target="#approvals" type="button" role="tab">
                        <i class="fas fa-check-square"></i> Approvals
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements" type="button" role="tab">
                        <i class="fas fa-exchange-alt"></i> Stock Movements
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                        <i class="fas fa-chart-bar"></i> Reports
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="warehouses-tab" data-bs-toggle="tab" data-bs-target="#warehouses" type="button" role="tab">
                        <i class="fas fa-building"></i> Warehouses
                    </button>
                </li>
            </ul>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger d-block">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">
            <div class="tab-content">
                <!-- DASHBOARD TAB (HOME) -->
                <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
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
                            <button class="btn btn-sm btn-primary" onclick="loadPendingApprovals()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
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

                <!-- INVENTORY TAB -->
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-boxes"></i> Inventory Items</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="inventoryTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Unit Price</th>
                                        <th>Total Value</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryBody">
                                    <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- APPROVALS TAB -->
                <div class="tab-pane fade" id="approvals" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-check-square"></i> Stock Movement Approvals</h5>
                        <div class="mb-3">
                            <button class="btn btn-outline-secondary btn-sm" onclick="filterApprovals('pending')">Pending</button>
                            <button class="btn btn-outline-success btn-sm" onclick="filterApprovals('approved')">Approved</button>
                            <button class="btn btn-outline-danger btn-sm" onclick="filterApprovals('rejected')">Rejected</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="filterApprovals('all')">All</button>
                        </div>
                        <div id="approvalsContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin"></i> Loading approvals...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MOVEMENTS TAB -->
                <div class="tab-pane fade" id="movements" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-exchange-alt"></i> Recent Stock Movements</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="movementsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>From/To</th>
                                        <th>Recorded By</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="movementsBody">
                                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- REPORTS TAB -->
                <div class="tab-pane fade" id="reports" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="dashboard-widget">
                                <h5><i class="fas fa-chart-pie"></i> Inventory by Category</h5>
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dashboard-widget">
                                <h5><i class="fas fa-chart-line"></i> Stock Movements Trend</h5>
                                <canvas id="movementChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WAREHOUSES TAB -->
                <div class="tab-pane fade" id="warehouses" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-building"></i> Warehouse Management</h5>
                        <div id="warehousesTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Approval -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Review Movement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4">Item:</dt>
                    <dd class="col-sm-8" id="modalItem">-</dd>
                    <dt class="col-sm-4">Type:</dt>
                    <dd class="col-sm-8" id="modalType">-</dd>
                    <dt class="col-sm-4">Quantity:</dt>
                    <dd class="col-sm-8" id="modalQty">-</dd>
                    <dt class="col-sm-4">Location:</dt>
                    <dd class="col-sm-8" id="modalLocation">-</dd>
                    <dt class="col-sm-4">Recorded By:</dt>
                    <dd class="col-sm-8" id="modalRecordedBy">-</dd>
                </dl>
                <div class="mb-3">
                    <label class="form-label">Your Approval Notes:</label>
                    <textarea id="approvalNotes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="rejectBtn" onclick="rejectMovement()">Reject</button>
                <button type="button" class="btn btn-success" id="approveBtn" onclick="approveMovement()">Approve</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const API_BASE = '<?= site_url('api') ?>';
let currentMovementId = null;

// Load dashboard data
async function loadDashboard() {
    await loadDashboardStats();
    await loadPendingApprovals();
    await loadWarehouseCards();
}

// Load key metrics
async function loadDashboardStats() {
    try {
        const response = await fetch(`${API_BASE}/approvals/stats`);
        const data = await response.json();
        
        if (data.status === 'success') {
            document.getElementById('pendingCount').textContent = data.data.pending || 0;
            
            // Calculate totals from warehouses
            const warehouseResponse = await fetch(`${API_BASE}/warehouses`);
            const warehouseData = await warehouseResponse.json();
            
            if (warehouseData.status === 'success') {
                let totalValue = 0;
                let totalItems = 0;
                warehouseData.data.forEach(wh => {
                    totalValue += parseFloat(wh.total_value || 0);
                    totalItems += parseInt(wh.item_count || 0);
                });
                
                document.getElementById('totalValue').textContent = '$' + totalValue.toFixed(2);
                document.getElementById('totalItems').textContent = totalItems;
            }
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
            document.getElementById('pendingCount').textContent = data.data.length;
            const container = document.getElementById('pendingApprovalsContainer');
            
            if (data.data.length === 0) {
                container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> No pending approvals</div>';
                return;
            }

            let html = '';
            data.data.forEach(movement => {
                html += `<div class="approval-card card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>${movement.item_name}</h6>
                                <small class="text-muted">
                                    <strong>${movement.movement_type.toUpperCase()}</strong> - 
                                    ${movement.quantity} units
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge badge-pending">PENDING</span>
                                <br>
                                <small class="text-muted">${new Date(movement.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                        <p class="mt-2 mb-2"><small>${movement.notes || 'No notes'}</small></p>
                        <button class="btn btn-sm btn-primary" onclick="showApprovalModal(${movement.id})">
                            <i class="fas fa-eye"></i> Review & Decide
                        </button>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading approvals:', error);
        const container = document.getElementById('pendingApprovalsContainer');
        container.innerHTML = '<div class="alert alert-danger">Error loading approvals</div>';
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
                        <h6><i class="fas fa-warehouse"></i> ${warehouse.warehouse_name}</h6>
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

// Show approval modal
async function showApprovalModal(movementId) {
    try {
        const response = await fetch(`${API_BASE}/approvals/${movementId}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const movement = data.data;
            currentMovementId = movement.id;
            
            document.getElementById('modalItem').textContent = movement.item_name;
            document.getElementById('modalType').textContent = movement.movement_type.toUpperCase();
            document.getElementById('modalQty').textContent = movement.quantity;
            document.getElementById('modalLocation').textContent = movement.from_warehouse_id || movement.to_warehouse_id || '-';
            document.getElementById('modalRecordedBy').textContent = (movement.first_name || '') + ' ' + (movement.last_name || '') || 'Unknown';
            document.getElementById('approvalNotes').value = '';
            
            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }
    } catch (error) {
        showAlert('Error loading movement details', 'danger');
    }
}

// Approve movement
async function approveMovement() {
    if (!currentMovementId) return;
    
    const notes = document.getElementById('approvalNotes').value;
    
    try {
        const response = await fetch(`${API_BASE}/approvals/${currentMovementId}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ approval_notes: notes })
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('Movement approved successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
            loadPendingApprovals();
            loadDashboardStats();
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    } catch (error) {
        showAlert('Error approving movement: ' + error.message, 'danger');
    }
}

// Reject movement
async function rejectMovement() {
    if (!currentMovementId) return;
    
    const notes = document.getElementById('approvalNotes').value;
    
    try {
        const response = await fetch(`${API_BASE}/approvals/${currentMovementId}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rejection_reason: notes })
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('Movement rejected!', 'info');
            bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
            loadPendingApprovals();
            loadDashboardStats();
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    } catch (error) {
        showAlert('Error rejecting movement: ' + error.message, 'danger');
    }
}

// Show alert
function showAlert(message, type = 'info') {
    const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;" role="alert">
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) alerts[0].remove();
    }, 4000);
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
    
    // Add tab event listeners to load data when tabs are clicked
    document.getElementById('inventory-tab').addEventListener('click', loadInventoryData);
    document.getElementById('approvals-tab').addEventListener('click', function() {
        loadApprovalsByStatus('pending');
    });
    document.getElementById('movements-tab').addEventListener('click', loadStockMovements);
    document.getElementById('warehouses-tab').addEventListener('click', loadWarehouseCards);
});

// Load inventory data
async function loadInventoryData() {
    try {
        const response = await fetch(`${API_BASE}/inventory`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const tbody = document.getElementById('inventoryBody');
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No inventory items found</td></tr>';
                return;
            }
            
            let html = '';
            data.data.forEach(item => {
                const totalValue = (item.current_stock * item.unit_price).toFixed(2);
                const stockStatus = item.current_stock < (item.reorder_level || 10) ? 
                    '<span class="badge bg-danger">Low Stock</span>' : 
                    '<span class="badge bg-success">In Stock</span>';
                
                html += `<tr>
                    <td>${item.item_id}</td>
                    <td><strong>${item.item_name}</strong></td>
                    <td>${item.category || 'N/A'}</td>
                    <td>${item.current_stock}</td>
                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td><strong>$${totalValue}</strong></td>
                    <td>${item.warehouse_name || 'N/A'}</td>
                    <td>${stockStatus}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewItemDetails(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
        document.getElementById('inventoryBody').innerHTML = 
            '<tr><td colspan="9" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Error loading inventory data</td></tr>';
    }
}

// View item details (placeholder - can expand later)
function viewItemDetails(itemId) {
    alert('Item details view - Item ID: ' + itemId + '\n\nThis feature can be expanded to show detailed item information, stock history, etc.');
}

// Load stock movements data
async function loadStockMovements() {
    try {
        const response = await fetch(`${API_BASE}/stock-movements`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const tbody = document.getElementById('movementsBody');
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No stock movements found</td></tr>';
                return;
            }
            
            let html = '';
            data.data.forEach(movement => {
                const movementType = movement.movement_type.toUpperCase();
                const typeColor = {
                    'IN': 'success',
                    'OUT': 'danger',
                    'TRANSFER': 'info',
                    'ADJUSTMENT': 'warning'
                }[movementType] || 'secondary';
                
                const statusColor = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger'
                }[movement.approval_status] || 'secondary';
                
                const fromTo = movement.movement_type === 'in' ? 
                    `→ ${movement.to_warehouse_name || 'Unknown'}` :
                    movement.movement_type === 'out' ?
                    `${movement.from_warehouse_name || 'Unknown'} →` :
                    `${movement.from_warehouse_name || 'N/A'} → ${movement.to_warehouse_name || 'N/A'}`;
                
                html += `<tr>
                    <td>${new Date(movement.created_at).toLocaleDateString()}</td>
                    <td><strong>${movement.item_name || 'Unknown Item'}</strong></td>
                    <td><span class="badge bg-${typeColor}">${movementType}</span></td>
                    <td>${movement.quantity}</td>
                    <td><small>${fromTo}</small></td>
                    <td>${movement.performed_by_name || 'Unknown'}</td>
                    <td><span class="badge bg-${statusColor}">${movement.approval_status.toUpperCase()}</span></td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading stock movements:', error);
        document.getElementById('movementsBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Error loading stock movements</td></tr>';
    }
}

// Filter approvals by status
function filterApprovals(status) {
    const container = document.getElementById('approvalsContainer');
    const buttons = container.parentElement.querySelectorAll('.btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('btn-outline-secondary', 'btn-outline-success', 'btn-outline-danger', 'btn-outline-primary', 'active'));
    event.target.classList.add('btn-' + (status === 'pending' ? 'outline-secondary' : status === 'approved' ? 'outline-success' : status === 'rejected' ? 'outline-danger' : 'outline-primary'), 'active');
    
    // Load and filter approvals
    loadApprovalsByStatus(status);
}

// Load approvals filtered by status
async function loadApprovalsByStatus(status) {
    try {
        const response = await fetch(`${API_BASE}/approvals/pending`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const container = document.getElementById('approvalsContainer');
            let movements = data.data;
            
            // Filter by status if needed
            if (status !== 'pending' && status !== 'all') {
                // For approved/rejected, we need to fetch history
                const historyResponse = await fetch(`${API_BASE}/approvals/history?limit=100`);
                const historyData = await historyResponse.json();
                if (historyData.status === 'success') {
                    movements = historyData.data.filter(m => m.approval_status === status);
                }
            } else if (status === 'all') {
                // Get both pending and history
                const historyResponse = await fetch(`${API_BASE}/approvals/history?limit=100`);
                const historyData = await historyResponse.json();
                if (historyData.status === 'success') {
                    movements = [...data.data, ...historyData.data];
                }
            }
            
            if (movements.length === 0) {
                container.innerHTML = `<div class="alert alert-info"><i class="fas fa-info-circle"></i> No ${status === 'all' ? 'movements' : status} approvals</div>`;
                return;
            }
            
            let html = '';
            movements.forEach(movement => {
                const statusBadge = `<span class="badge badge-${movement.approval_status}">${movement.approval_status.toUpperCase()}</span>`;
                html += `<div class="approval-card card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>${movement.item_name}</h6>
                                <small class="text-muted">
                                    <strong>${movement.movement_type.toUpperCase()}</strong> - 
                                    ${movement.quantity} units by ${movement.first_name || 'Unknown'}
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                ${statusBadge}
                                <br>
                                <small class="text-muted">${new Date(movement.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                        ${movement.approval_status === 'pending' ? `<button class="btn btn-sm btn-primary mt-2" onclick="showApprovalModal(${movement.id})">Review</button>` : ''}
                    </div>
                </div>`;
            });
            
            container.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading approvals:', error);
        document.getElementById('approvalsContainer').innerHTML = '<div class="alert alert-danger">Error loading approvals</div>';
    }
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Staff - Scanner Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar .nav-link { color: #ecf0f1; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s; background: none; text-align: left; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .stat-card { border-radius: 8px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .scanner-input { font-size: 20px; padding: 15px; }
        .item-card { border-left: 4px solid #0d6efd; }
        .dashboard-widget { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-barcode text-success"></i> Warehouse Staff Dashboard</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-info me-3"><?= session('user_role') ?></span>
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
                    <button class="nav-link" id="scanner-tab" data-bs-toggle="tab" data-bs-target="#scanner" type="button" role="tab">
                        <i class="fas fa-barcode"></i> Scanner
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements" type="button" role="tab">
                        <i class="fas fa-exchange-alt"></i> My Movements
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="fas fa-boxes"></i> Inventory
                    </button>
                </li>
                </li>
            </ul>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger d-block px-3">
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
                            <div class="dashboard-widget stat-card bg-success text-white">
                                <h6 class="text-uppercase">Today's Movements</h6>
                                <h3 id="todayCount">0</h3>
                                <small>Recorded by you</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-widget stat-card bg-primary text-white">
                                <h6 class="text-uppercase">Pending Approval</h6>
                                <h3 id="pendingCount">0</h3>
                                <small>Awaiting manager</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-widget stat-card bg-info text-white">
                                <h6 class="text-uppercase">Approved</h6>
                                <h3 id="approvedCount">0</h3>
                                <small>This week</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-widget stat-card bg-warning text-white">
                                <h6 class="text-uppercase">Items Scanned</h6>
                                <h3 id="itemsCount">0</h3>
                                <small>Total items</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="dashboard-widget mb-4">
                        <h5 class="mb-3"><i class="fas fa-bolt text-warning"></i> Quick Actions</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <button class="btn btn-success w-100 py-3" onclick="document.getElementById('scanner-tab').click()">
                                    <i class="fas fa-box fa-2x mb-2"></i><br>
                                    Stock IN
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-danger w-100 py-3" onclick="document.getElementById('scanner-tab').click()">
                                    <i class="fas fa-shipping-fast fa-2x mb-2"></i><br>
                                    Stock OUT
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info w-100 py-3" onclick="document.getElementById('scanner-tab').click()">
                                    <i class="fas fa-exchange-alt fa-2x mb-2"></i><br>
                                    Transfer
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning w-100 py-3" onclick="document.getElementById('scanner-tab').click()">
                                    <i class="fas fa-barcode fa-2x mb-2"></i><br>
                                    Scan Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-widget">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-history text-primary"></i> Recent Activity</h5>
                            <button class="btn btn-sm btn-primary" onclick="loadRecentMovements()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                        <div id="recentActivityContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCANNER TAB -->
                <div class="tab-pane fade" id="scanner" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-barcode"></i> Barcode Scanner</h5>
                        <h5 class="mb-3"><i class="fas fa-barcode"></i> Barcode Scanner</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="text-muted mb-3">Scan barcode or enter item ID to record movement</p>
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Barcode/Item ID</strong></label>
                                    <input type="text" id="barcodeInput" class="form-control scanner-input" placeholder="Scan barcode or type item ID..." autofocus>
                                    <small class="text-muted">Press Enter to lookup item</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Movement Type</strong></label>
                                    <select id="movementType" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="in">🟢 Stock IN (Receiving)</option>
                                        <option value="out">🔴 Stock OUT (Dispatch)</option>
                                        <option value="transfer">🔵 Transfer to Another Warehouse</option>
                                        <option value="adjustment">🟡 Physical Count/Adjustment</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="warehouseGroup" style="display:none;">
                                    <label class="form-label"><strong>Warehouse</strong></label>
                                    <select id="warehouseSelect" class="form-select">
                                        <option value="">Select Warehouse</option>
                                        <?php if (isset($warehouses) && is_array($warehouses)): ?>
                                            <?php foreach ($warehouses as $wh): ?>
                                                <option value="<?= $wh['id'] ?>"><?= esc($wh['warehouse_name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Quantity</strong></label>
                                            <input type="number" id="quantity" class="form-control" placeholder="Enter quantity" min="1" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Reference (Optional)</strong></label>
                                            <input type="text" id="reference" class="form-control" placeholder="PO-2025-001">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Notes (Optional)</strong></label>
                                    <textarea id="notes" class="form-control" placeholder="Any additional notes..." rows="2"></textarea>
                                </div>

                                <button type="button" id="recordMovement" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check"></i> Record Movement
                                </button>
                            </div>

                            <div class="col-md-4">
                                <!-- Item Preview -->
                                <div id="itemPreview" style="display:none;">
                                    <div class="card item-card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-box"></i> Item Details</h6>
                                            <hr>
                                            <p class="mb-1"><strong>ID:</strong> <span id="previewId">-</span></p>
                                            <p class="mb-1"><strong>Name:</strong> <span id="previewName">-</span></p>
                                            <p class="mb-1"><strong>Stock:</strong> <span id="previewStock" class="badge bg-info">-</span></p>
                                            <p class="mb-1"><strong>Price:</strong> <span id="previewPrice">-</span></p>
                                            <p class="mb-0"><strong>Location:</strong> <span id="previewWarehouse">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MY MOVEMENTS TAB -->
                <div class="tab-pane fade" id="movements" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-exchange-alt"></i> My Stock Movements</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="movementsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>From/To</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody id="movementsBody">
                                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- INVENTORY TAB -->
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <div class="dashboard-widget">
                        <h5 class="mb-3"><i class="fas fa-boxes"></i> Inventory Items</h5>
                        <div class="mb-3">
                            <input type="text" id="inventorySearch" class="form-control" placeholder="Search items...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Item Name</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryBody">
                                    <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Container -->
<div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 450px; min-width: 300px;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_BASE = '<?= site_url('api') ?>';
let currentItem = null;

// Load dashboard data
async function loadDashboard() {
    await loadStats();
    await loadRecentMovements();
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch(`${API_BASE}/stock-movements`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const userId = <?= session('user_id') ?>;
            const myMovements = data.data.filter(m => m.performed_by == userId);
            
            document.getElementById('todayCount').textContent = myMovements.length;
            document.getElementById('pendingCount').textContent = myMovements.filter(m => m.approval_status === 'pending').length;
            document.getElementById('approvedCount').textContent = myMovements.filter(m => m.approval_status === 'approved').length;
            document.getElementById('itemsCount').textContent = myMovements.length;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load recent movements for dashboard
async function loadRecentMovements() {
    try {
        const response = await fetch(`${API_BASE}/stock-movements`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const userId = <?= session('user_id') ?>;
            const myMovements = data.data.filter(m => m.performed_by == userId).slice(0, 5);
            const container = document.getElementById('recentActivityContainer');
            
            if (myMovements.length === 0) {
                container.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No recent movements</div>';
                return;
            }
            
            let html = '';
            myMovements.forEach(movement => {
                const statusColor = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger'
                }[movement.approval_status] || 'secondary';
                
                html += `<div class="card mb-2">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>${movement.item_name || 'Unknown'}</strong>
                                <small class="text-muted d-block">${movement.movement_type.toUpperCase()} - ${movement.quantity} units</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">${new Date(movement.created_at).toLocaleDateString()}</small>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="badge bg-${statusColor}">${movement.approval_status.toUpperCase()}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
            
            // Also update movements tab
            loadMyMovements();
        }
    } catch (error) {
        console.error('Error loading movements:', error);
    }
}

// Load movements for movements tab
async function loadMyMovements() {
    try {
        const response = await fetch(`${API_BASE}/stock-movements`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const userId = <?= session('user_id') ?>;
            const myMovements = data.data.filter(m => m.performed_by == userId);
            const tbody = document.getElementById('movementsBody');
            
            if (myMovements.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No movements found</td></tr>';
                return;
            }
            
            let html = '';
            myMovements.forEach(movement => {
                const typeColor = {
                    'in': 'success',
                    'out': 'danger',
                    'transfer': 'info',
                    'adjustment': 'warning'
                }[movement.movement_type] || 'secondary';
                
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
                    <td>${movement.item_name || 'Unknown'}</td>
                    <td><span class="badge bg-${typeColor}">${movement.movement_type.toUpperCase()}</span></td>
                    <td>${movement.quantity}</td>
                    <td><small>${fromTo}</small></td>
                    <td><span class="badge bg-${statusColor}">${movement.approval_status.toUpperCase()}</span></td>
                    <td><small>${movement.notes || '-'}</small></td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading my movements:', error);
        document.getElementById('movementsBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle"></i> Error loading movements</td></tr>';
    }
}

// Load inventory
async function loadInventory() {
    try {
        const response = await fetch(`${API_BASE}/inventory`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const tbody = document.getElementById('inventoryBody');
            
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No inventory items</td></tr>';
                return;
            }
            
            let html = '';
            data.data.forEach(item => {
                const stockStatus = item.current_stock < (item.reorder_level || 10) ? 
                    '<span class="badge bg-danger">Low</span>' : 
                    '<span class="badge bg-success">OK</span>';
                
                html += `<tr>
                    <td>${item.item_id}</td>
                    <td>${item.item_name}</td>
                    <td>${item.current_stock}</td>
                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>${item.warehouse_name || 'N/A'}</td>
                    <td>${stockStatus}</td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
    }
}

// Movement type change handler
document.getElementById('movementType').addEventListener('change', (e) => {
    document.getElementById('warehouseGroup').style.display = (e.target.value === 'in' || e.target.value === 'transfer') ? 'block' : 'none';
});

// Barcode lookup on Enter
document.getElementById('barcodeInput').addEventListener('keypress', async (e) => {
    if (e.key === 'Enter') {
        const barcode = e.target.value.trim();
        if (barcode) {
            await lookupItem(barcode);
        }
    }
});

// Lookup item
async function lookupItem(barcode) {
    try {
        const response = await fetch(`${API_BASE}/inventory`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const item = data.data.find(i => i.item_id == barcode || i.id == barcode);
            
            if (item) {
                currentItem = item;
                document.getElementById('itemPreview').style.display = 'block';
                document.getElementById('previewId').textContent = item.item_id;
                document.getElementById('previewName').textContent = item.item_name;
                document.getElementById('previewStock').textContent = item.current_stock + ' units';
                document.getElementById('previewPrice').textContent = '$' + parseFloat(item.unit_price).toFixed(2);
                document.getElementById('previewWarehouse').textContent = item.warehouse_name || 'N/A';
                showAlert('Item found: ' + item.item_name, 'success');
            } else {
                showAlert('Item not found', 'warning');
            }
        }
    } catch (error) {
        showAlert('Error looking up item: ' + error.message, 'danger');
    }
}

// Record movement
document.getElementById('recordMovement').addEventListener('click', async () => {
    const movementType = document.getElementById('movementType').value;
    const barcode = document.getElementById('barcodeInput').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    const warehouse = document.getElementById('warehouseSelect').value;
    const reference = document.getElementById('reference').value;
    const notes = document.getElementById('notes').value;

    if (!movementType || !barcode || !quantity) {
        showAlert('Please fill in all required fields', 'warning');
        return;
    }

    if (!currentItem) {
        showAlert('Please lookup an item first', 'warning');
        return;
    }

    const payload = {
        item_id: currentItem.id,
        quantity: quantity,
        reference: reference || 'STAFF-' + Date.now(),
        notes: notes
    };

    let endpoint = '';
    if (movementType === 'in') {
        endpoint = `/stock-movements/in`;
        payload.warehouse_id = warehouse || currentItem.warehouse_id;
    } else if (movementType === 'out') {
        endpoint = `/stock-movements/out`;
        payload.warehouse_id = currentItem.warehouse_id;
    } else if (movementType === 'transfer') {
        endpoint = `/stock-movements/transfer`;
        payload.from_warehouse_id = currentItem.warehouse_id;
        payload.to_warehouse_id = warehouse;
    } else if (movementType === 'adjustment') {
        endpoint = `/stock-movements/adjustment`;
        payload.warehouse_id = currentItem.warehouse_id;
        payload.adjustment_quantity = quantity;
    }

    // Debug logging
    console.log('Sending payload:', payload);
    console.log('Endpoint:', API_BASE + endpoint);

    try {
        const response = await fetch(API_BASE + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        console.log('Response:', result);

        if (result.status === 'success') {
            showAlert('✓ Movement recorded successfully! Awaiting manager approval.', 'success');
            // Reset form
            document.getElementById('barcodeInput').value = '';
            document.getElementById('movementType').value = '';
            document.getElementById('quantity').value = '1';
            document.getElementById('reference').value = '';
            document.getElementById('notes').value = '';
            document.getElementById('itemPreview').style.display = 'none';
            currentItem = null;
            // Reload data
            loadDashboard();
        } else {
            showAlert('Error: ' + (result.message || 'Unknown error'), 'danger');
            console.error('API Error:', result);
        }
    } catch (error) {
        showAlert('Error recording movement: ' + error.message, 'danger');
        console.error('Exception:', error);
    }
});

// Show alert
function showAlert(message, type = 'info') {
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show shadow-lg" role="alert" style="font-size: 16px; font-weight: 500;">
        <i class="fas fa-${iconMap[type] || 'info-circle'} me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    
    const container = document.getElementById('alertContainer');
    container.innerHTML = alertHtml;
    
    // Auto dismiss after 5 seconds
    setTimeout(() => { 
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => { container.innerHTML = ''; }, 150);
        }
    }, 5000);
}

// Event listeners for tabs
document.getElementById('movements-tab').addEventListener('click', loadMyMovements);
document.getElementById('inventory-tab').addEventListener('click', loadInventory);

// Load dashboard on page load
document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
</body>
</html>

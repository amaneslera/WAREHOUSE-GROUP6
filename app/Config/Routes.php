<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AUTH::login');

// Auth routes
$routes->get('login', 'AUTH::login');
$routes->post('login', 'AUTH::login');
$routes->get('register', 'AUTH::register');
$routes->post('register', 'AUTH::register');
$routes->get('logout', 'AUTH::logout');

// Debug route
$routes->get('testdb', 'AUTH::testdb');

// Role-based dashboards (excluding manager - goes to inventory)
$routes->get('dashboard/staff', 'Dashboard::staff');
$routes->get('dashboard/auditor', 'Dashboard::auditor');
$routes->get('dashboard/procurement', 'Dashboard::procurement');
$routes->get('dashboard/apclerk', 'Dashboard::apclerk');
$routes->get('dashboard/arclerk', 'Dashboard::arclerk');
$routes->get('dashboard/it', 'Dashboard::it');
$routes->get('dashboard/top', 'Dashboard::top');

// Accounts Receivable Clerk Routes
$routes->get('arclerk/invoices', 'AccountsReceivableController::indexView');
$routes->get('arclerk/create-invoice', 'AccountsReceivableController::createView');
$routes->get('arclerk/payments', 'AccountsReceivableController::paymentsView');
$routes->get('arclerk/reports', 'AccountsReceivableController::reportsView');

// Procurement module
$routes->get('procurement', 'Procurement::index');
$routes->get('procurement/prs', 'Procurement::purchaseRequests');
$routes->get('procurement/prs/create', 'Procurement::createPR');
$routes->post('procurement/prs/create', 'Procurement::storePR');
$routes->get('procurement/prs/(:num)', 'Procurement::viewPR/$1');
$routes->post('procurement/prs/(:num)/submit', 'Procurement::submitPR/$1');
$routes->get('procurement/pos', 'Procurement::purchaseOrders');
$routes->get('procurement/pos/(:num)', 'Procurement::viewPO/$1');
$routes->get('procurement/prs/(:num)/create-po', 'Procurement::createPOFromPR/$1');
$routes->post('procurement/prs/(:num)/create-po', 'Procurement::storePOFromPR/$1');

$routes->get('procurement/vendors', 'ProcurementVendors::index');
$routes->get('procurement/vendors/create', 'ProcurementVendors::create');
$routes->post('procurement/vendors/create', 'ProcurementVendors::store');
$routes->get('procurement/vendors/edit/(:num)', 'ProcurementVendors::edit/$1');
$routes->post('procurement/vendors/edit/(:num)', 'ProcurementVendors::update/$1');
$routes->post('procurement/vendors/(:num)/status', 'ProcurementVendors::updateStatus/$1');

$routes->get('top-management/pr-approvals', 'TopManagementApprovals::purchaseRequests');
$routes->post('top-management/pr-approvals/(:num)/approve', 'TopManagementApprovals::approvePR/$1');
$routes->post('top-management/pr-approvals/(:num)/reject', 'TopManagementApprovals::rejectPR/$1');

$routes->get('top-management/po-approvals', 'TopManagementPOApprovals::purchaseOrders');
$routes->post('top-management/po-approvals/(:num)/approve', 'TopManagementPOApprovals::approvePO/$1');
$routes->post('top-management/po-approvals/(:num)/reject', 'TopManagementPOApprovals::rejectPO/$1');

$routes->get('top-management', 'TopManagementDashboard::index');
$routes->get('top-management/kpis', 'TopManagementDashboard::kpis');
$routes->get('top-management/inventory-summary', 'TopManagementDashboard::inventorySummary');
$routes->get('top-management/financial-summary', 'TopManagementDashboard::financialSummary');
$routes->get('top-management/export/warehouse-summary.csv', 'TopManagementDashboard::exportWarehouseSummaryCsv');
$routes->get('top-management/audit-logs', 'TopManagementAuditLogs::index');

// IT Admin module
$routes->get('it-admin', 'ITAdmin::index');
$routes->get('it-admin/users', 'ITAdmin::users');
$routes->get('it-admin/users/create', 'ITAdmin::createUser');
$routes->post('it-admin/users/create', 'ITAdmin::storeUser');
$routes->get('it-admin/users/(:num)/edit', 'ITAdmin::editUser/$1');
$routes->post('it-admin/users/(:num)/edit', 'ITAdmin::updateUser/$1');
$routes->post('it-admin/users/(:num)/status', 'ITAdmin::updateStatus/$1');
$routes->post('it-admin/users/(:num)/role', 'ITAdmin::updateRole/$1');
$routes->get('it-admin/audit-logs', 'ITAdmin::auditLogs');
$routes->get('it-admin/backups', 'ITAdmin::backups');
$routes->post('it-admin/backups/run', 'ITAdmin::runBackup');
$routes->get('it-admin/backups/download/(:any)', 'ITAdmin::downloadBackup/$1');
$routes->get('it-admin/system-status', 'ITAdmin::systemStatus');

// Accounts Payable Routes
$routes->get('invoice-management', 'InvoiceManagementController::index');
$routes->get('invoice-management/approve/(:num)', 'InvoiceManagementController::approve/$1');
$routes->get('invoice-management/mark-paid/(:num)', 'InvoiceManagementController::markPaid/$1');
$routes->get('invoice-management/view/(:num)', 'InvoiceManagementController::view/$1');
$routes->get('invoice-management/match/(:num)', 'InvoiceManagementController::match/$1');
$routes->post('invoice-management/match/(:num)', 'InvoiceManagementController::storeMatch/$1');
$routes->post('invoice-management/flag-discrepancy/(:num)', 'InvoiceManagementController::flagDiscrepancy/$1');

$routes->get('payment-recording', 'PaymentRecordingController::index');
$routes->get('payment-recording/create', 'PaymentRecordingController::create');
$routes->post('payment-recording/store', 'PaymentRecordingController::store');

$routes->get('supplier-management', 'SupplierManagementController::index');
$routes->get('supplier-management/edit/(:num)', 'SupplierManagementController::edit/$1');
$routes->post('supplier-management/update/(:num)', 'SupplierManagementController::update/$1');

// Inventory Management Routes (Web/View Routes - Legacy)
$routes->get('inventory', 'InventoryController::indexView');
$routes->get('inventory/add', 'InventoryController::create');
$routes->get('inventory/view/(:num)', 'InventoryController::show/$1');
$routes->get('inventory/edit/(:num)', 'InventoryController::edit/$1');

// Inventory API Test Page
$routes->get('inventory/test', 'InventoryTestController::index');

// Inventory API Routes (RESTful JSON endpoints)
$routes->group('api/inventory', ['namespace' => 'App\Controllers'], function($routes) {
    // GET /api/inventory - List all items with filters
    $routes->get('', 'InventoryController::index');
    
    // GET /api/inventory/{id} - Show specific item
    $routes->get('(:num)', 'InventoryController::show/$1');
    
    // POST /api/inventory - Create new item
    $routes->post('', 'InventoryController::store');
    
    // PUT/PATCH /api/inventory/{id} - Update item
    $routes->put('(:num)', 'InventoryController::update/$1');
    $routes->patch('(:num)', 'InventoryController::update/$1');
    
    // DELETE /api/inventory/{id} - Delete item
    $routes->delete('(:num)', 'InventoryController::delete/$1');
});

// Stock Movement Routes (Web)
$routes->get('stock-movements', 'StockMovementController::index');
$routes->get('stock-movements/in', 'StockMovementController::stockInForm');
$routes->get('stock-movements/out', 'StockMovementController::stockOutForm');
$routes->get('stock-movements/transfer', 'StockMovementController::transferForm');

// Dashboard Routes for Warehouse Manager & Staff
$routes->get('dashboard/manager', 'Dashboard::manager');
$routes->get('dashboard/manager/approvals', 'Dashboard::managerApprovals');
$routes->get('dashboard/manager/tasks', 'WarehouseTasks::managerIndex');
$routes->get('dashboard/manager/tasks/(:num)/create', 'WarehouseTasks::createTask/$1');
$routes->post('dashboard/manager/tasks/(:num)/create', 'WarehouseTasks::storeTask/$1');
$routes->get('dashboard/staff/scanner', 'Dashboard::staffScanner');
$routes->get('dashboard/staff/tasks', 'WarehouseTasks::staffIndex');
$routes->post('dashboard/staff/tasks/(:num)/start', 'WarehouseTasks::start/$1');
$routes->post('dashboard/staff/tasks/(:num)/complete', 'WarehouseTasks::complete/$1');

// Stock Movement API Routes (RESTful)
$routes->group('api/stock-movements', function($routes) {
    $routes->get('', 'StockMovementController::apiGetMovements');
    $routes->get('(:num)', 'StockMovementController::show/$1');
    $routes->get('stats', 'StockMovementController::apiGetStats');
    $routes->get('item/(:num)', 'StockMovementController::apiGetItemHistory/$1');
    $routes->post('in', 'StockMovementController::apiStockIn');
    $routes->post('out', 'StockMovementController::apiStockOut');
    $routes->post('transfer', 'StockMovementController::apiTransfer');
    $routes->post('adjustment', 'StockMovementController::apiAdjustment');
    $routes->post('(:num)/approve', 'StockApprovalController::approve/$1');
    $routes->post('(:num)/reject', 'StockApprovalController::reject/$1');
});

// Barcode/Scanner API Routes
$routes->group('api/barcode', function($routes) {
    $routes->get('lookup', 'BarcodeController::lookup');
    $routes->get('(:num)', 'BarcodeController::getItem/$1');
    $routes->get('search', 'BarcodeController::search');
    $routes->get('qr/(:num)', 'BarcodeController::generateQR/$1');
    $routes->post('stock-in', 'BarcodeController::stockIn');
    $routes->post('stock-out', 'BarcodeController::stockOut');
});

// Warehouse API Routes
$routes->group('api/warehouses', function($routes) {
    $routes->get('', 'WarehouseController::index');
    $routes->get('(:num)', 'WarehouseController::show/$1');
});

// Stock Approval API Routes
$routes->group('api/approvals', function($routes) {
    $routes->get('pending', 'StockApprovalController::pending');
    $routes->get('stats', 'StockApprovalController::stats');
    $routes->get('history', 'StockApprovalController::history');
    $routes->get('(:num)', 'StockApprovalController::show/$1');
    $routes->post('(:num)/approve', 'StockApprovalController::approve/$1');
    $routes->post('(:num)/reject', 'StockApprovalController::reject/$1');
});

// Accounts Receivable API Routes (RESTful JSON endpoints)
$routes->group('api/accounts-receivable', ['namespace' => 'App\Controllers'], function($routes) {
    // GET /api/accounts-receivable - List all AR invoices with pagination & filtering
    $routes->get('', 'AccountsReceivableController::index');
    
    // GET /api/accounts-receivable/overdue - Get overdue invoices report
    $routes->get('overdue', 'AccountsReceivableController::getOverdue');
    
    // GET /api/accounts-receivable/outstanding - Get outstanding balance report
    $routes->get('outstanding', 'AccountsReceivableController::getOutstanding');
    
    // GET /api/accounts-receivable/stats - Get AR statistics
    $routes->get('stats', 'AccountsReceivableController::getStats');
    
    // GET /api/accounts-receivable/{id} - Show specific AR invoice with payment history
    $routes->get('(:num)', 'AccountsReceivableController::show/$1');
    
    // GET /api/accounts-receivable/{id}/history - Get payment history for invoice
    $routes->get('(:num)/history', 'AccountsReceivableController::getPaymentHistory/$1');
    
    // POST /api/accounts-receivable - Create new AR invoice
    $routes->post('', 'AccountsReceivableController::store');
    
    // POST /api/accounts-receivable/{id}/payment - Record payment for invoice
    $routes->post('(:num)/payment', 'AccountsReceivableController::recordPayment/$1');
    
    // PUT/PATCH /api/accounts-receivable/{id} - Update AR invoice
    $routes->put('(:num)', 'AccountsReceivableController::update/$1');
    $routes->patch('(:num)', 'AccountsReceivableController::update/$1');
    
    // DELETE /api/accounts-receivable/{id} - Cancel/delete AR invoice
    $routes->delete('(:num)', 'AccountsReceivableController::delete/$1');
});

// Reports API Routes (Centralized Reporting System)
$routes->group('api/reports', ['namespace' => 'App\Controllers'], function($routes) {
    // Inventory Reports
    $routes->get('inventory/summary', 'ReportsController::inventorySummary');
    $routes->get('inventory/low-stock', 'ReportsController::inventoryLowStock');
    $routes->get('inventory/movements', 'ReportsController::inventoryMovements');
    
    // Accounts Receivable Reports
    $routes->get('ar/outstanding', 'ReportsController::arOutstanding');
    $routes->get('ar/aging', 'ReportsController::arAging');
    $routes->get('ar/history', 'ReportsController::arHistory');
    
    // Accounts Payable Reports
    $routes->get('ap/outstanding', 'ReportsController::apOutstanding');
    $routes->get('ap/aging', 'ReportsController::apAging');
    $routes->get('ap/history', 'ReportsController::apHistory');
    
    // Warehouse Usage Dashboard
    $routes->get('warehouse/usage', 'ReportsController::warehouseUsage');
});

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

// Accounts Payable Routes
$routes->get('invoice-management', 'InvoiceManagementController::index');
$routes->get('invoice-management/approve/(:num)', 'InvoiceManagementController::approve/$1');
$routes->get('invoice-management/mark-paid/(:num)', 'InvoiceManagementController::markPaid/$1');
$routes->get('invoice-management/view/(:num)', 'InvoiceManagementController::view/$1');

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

// Stock Movement API Routes (RESTful)
$routes->group('api/stock-movements', function($routes) {
    $routes->get('', 'StockMovementController::apiGetMovements');
    $routes->get('stats', 'StockMovementController::apiGetStats');
    $routes->get('item/(:num)', 'StockMovementController::apiGetItemHistory/$1');
    $routes->post('in', 'StockMovementController::apiStockIn');
    $routes->post('out', 'StockMovementController::apiStockOut');
    $routes->post('transfer', 'StockMovementController::apiTransfer');
    $routes->post('adjustment', 'StockMovementController::apiAdjustment');
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

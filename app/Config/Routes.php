<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

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

// Inventory Management Routes
$routes->get('inventory', 'InventoryController::index');
$routes->get('inventory/add', 'InventoryController::add');
$routes->post('inventory/create', 'InventoryController::create');
$routes->get('inventory/view/(:num)', 'InventoryController::view/$1');
$routes->get('inventory/edit/(:num)', 'InventoryController::edit/$1');
$routes->post('inventory/update/(:num)', 'InventoryController::update/$1');
$routes->get('inventory/delete/(:num)', 'InventoryController::delete/$1');

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

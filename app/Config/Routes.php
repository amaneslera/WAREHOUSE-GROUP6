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

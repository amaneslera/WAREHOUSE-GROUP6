<?php

// Load CodeIgniter's bootstrap file
require_once __DIR__ . '/vendor/autoload.php';

use CodeIgniter\Database\Config;

// Try to connect to the database
try {
    $db = Config::connect();
    $query = $db->query('SELECT 1 as test');
    $result = $query->getResult();
    echo '<h3>Database connection successful!</h3>';
    echo '<pre>'; print_r($result); echo '</pre>';
} catch (\Throwable $e) {
    echo '<h3>Database connection failed:</h3>';
    echo '<pre>' . $e->getMessage() . '</pre>';
}
<?php
/**
 * Inventory API Test Script
 * 
 * This script tests all CRUD operations for the Inventory API
 * Run this from the browser or CLI to verify API functionality
 */

// Display all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Inventory API Test Suite</h1>";
echo "<p>Testing all CRUD endpoints...</p><hr>";

// Configuration
$baseUrl = "http://localhost/WAREHOUSE-GROUP6/api/inventory";
$sessionCookie = ""; // Set this if testing with authentication

/**
 * Helper function to make HTTP requests
 */
function makeRequest($url, $method = 'GET', $data = null, $cookie = '') {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

/**
 * Display test result
 */
function displayResult($testName, $result, $expectedCode = 200) {
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
    echo "<h3>" . $testName . "</h3>";
    echo "<p><strong>HTTP Code:</strong> " . $result['code'];
    
    if ($result['code'] == $expectedCode) {
        echo " <span style='color: green;'>✓ PASS</span>";
    } else {
        echo " <span style='color: red;'>✗ FAIL (Expected: {$expectedCode})</span>";
    }
    echo "</p>";
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars(json_encode($result['body'], JSON_PRETTY_PRINT));
    echo "</pre>";
    echo "</div>";
}

// ========================================
// TEST 1: List All Inventory Items
// ========================================
echo "<h2>Test 1: GET /api/inventory (List All Items)</h2>";
$result = makeRequest($baseUrl, 'GET', null, $sessionCookie);
displayResult("List all inventory items", $result, 200);

// ========================================
// TEST 2: List with Filters
// ========================================
echo "<h2>Test 2: GET /api/inventory?page=1&limit=10 (Pagination)</h2>";
$result = makeRequest($baseUrl . "?page=1&limit=10", 'GET', null, $sessionCookie);
displayResult("List with pagination", $result, 200);

// ========================================
// TEST 3: Get Low Stock Items
// ========================================
echo "<h2>Test 3: GET /api/inventory?low_stock=true (Filter Low Stock)</h2>";
$result = makeRequest($baseUrl . "?low_stock=true", 'GET', null, $sessionCookie);
displayResult("Get low stock items", $result, 200);

// ========================================
// TEST 4: Show Specific Item
// ========================================
echo "<h2>Test 4: GET /api/inventory/1 (Show Item)</h2>";
$result = makeRequest($baseUrl . "/1", 'GET', null, $sessionCookie);
displayResult("Show specific inventory item", $result, 200);

// ========================================
// TEST 5: Show Non-Existent Item (404)
// ========================================
echo "<h2>Test 5: GET /api/inventory/99999 (404 Test)</h2>";
$result = makeRequest($baseUrl . "/99999", 'GET', null, $sessionCookie);
displayResult("Show non-existent item (should be 404)", $result, 404);

// ========================================
// TEST 6: Create New Item
// ========================================
echo "<h2>Test 6: POST /api/inventory (Create Item)</h2>";
$newItem = [
    'item_id' => 'TEST-' . time(),
    'item_name' => 'Test Product ' . time(),
    'category_id' => 1,
    'warehouse_id' => 1,
    'current_stock' => 100,
    'minimum_stock' => 20,
    'unit_price' => 99.99,
    'unit_of_measure' => 'pcs',
    'description' => 'This is a test product created via API',
    'supplier_info' => 'Test Supplier'
];
$result = makeRequest($baseUrl, 'POST', $newItem, $sessionCookie);
displayResult("Create new inventory item", $result, 201);
$createdItemId = $result['body']['data']['id'] ?? null;

// ========================================
// TEST 7: Create Duplicate Item (409)
// ========================================
if ($createdItemId) {
    echo "<h2>Test 7: POST /api/inventory (Duplicate Item Test)</h2>";
    $result = makeRequest($baseUrl, 'POST', $newItem, $sessionCookie);
    displayResult("Create duplicate item (should be 409)", $result, 409);
}

// ========================================
// TEST 8: Create with Invalid Data (400)
// ========================================
echo "<h2>Test 8: POST /api/inventory (Validation Test)</h2>";
$invalidItem = [
    'item_id' => 'INVALID',
    'item_name' => 'AB', // Too short (min 3 chars)
    'category_id' => 9999, // Non-existent
    'warehouse_id' => 9999, // Non-existent
    'current_stock' => -10, // Negative
    'minimum_stock' => -5, // Negative
    'unit_price' => 0 // Must be > 0
];
$result = makeRequest($baseUrl, 'POST', $invalidItem, $sessionCookie);
displayResult("Create with invalid data (should be 400 or 404)", $result, 400);

// ========================================
// TEST 9: Update Item
// ========================================
if ($createdItemId) {
    echo "<h2>Test 9: PUT /api/inventory/{$createdItemId} (Update Item)</h2>";
    $updateData = [
        'current_stock' => 200,
        'unit_price' => 149.99,
        'description' => 'Updated via API test'
    ];
    $result = makeRequest($baseUrl . "/{$createdItemId}", 'PUT', $updateData, $sessionCookie);
    displayResult("Update inventory item", $result, 200);
}

// ========================================
// TEST 10: Update Non-Existent Item (404)
// ========================================
echo "<h2>Test 10: PUT /api/inventory/99999 (404 Test)</h2>";
$updateData = ['current_stock' => 500];
$result = makeRequest($baseUrl . "/99999", 'PUT', $updateData, $sessionCookie);
displayResult("Update non-existent item (should be 404)", $result, 404);

// ========================================
// TEST 11: Delete Item with Stock (409)
// ========================================
if ($createdItemId) {
    echo "<h2>Test 11: DELETE /api/inventory/{$createdItemId} (Business Rule Test)</h2>";
    $result = makeRequest($baseUrl . "/{$createdItemId}", 'DELETE', null, $sessionCookie);
    displayResult("Delete item with stock (should be 409)", $result, 409);
}

// ========================================
// TEST 12: Delete Item After Zeroing Stock
// ========================================
if ($createdItemId) {
    echo "<h2>Test 12: Zero Stock Then DELETE</h2>";
    
    // First, set stock to zero
    $zeroStock = ['current_stock' => 0];
    $result1 = makeRequest($baseUrl . "/{$createdItemId}", 'PUT', $zeroStock, $sessionCookie);
    displayResult("Step 1: Set stock to zero", $result1, 200);
    
    // Then delete
    $result2 = makeRequest($baseUrl . "/{$createdItemId}", 'DELETE', null, $sessionCookie);
    displayResult("Step 2: Delete item (should succeed)", $result2, 200);
}

// ========================================
// TEST 13: Delete Non-Existent Item (404)
// ========================================
echo "<h2>Test 13: DELETE /api/inventory/99999 (404 Test)</h2>";
$result = makeRequest($baseUrl . "/99999", 'DELETE', null, $sessionCookie);
displayResult("Delete non-existent item (should be 404)", $result, 404);

echo "<hr><h2>Test Suite Completed</h2>";
echo "<p><strong>Note:</strong> If you see 403 Forbidden errors, make sure you're logged in with the correct role (warehouse_manager or procurement_officer).</p>";

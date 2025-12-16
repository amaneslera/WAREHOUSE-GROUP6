<?php

namespace App\Controllers;

/**
 * InventoryTestController
 * 
 * Test controller to verify Inventory API endpoints
 * Access via: /inventory/test
 */
class InventoryTestController extends BaseController
{
    public function index()
    {
        // Display test results page
        $baseUrl = base_url('api/inventory');
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Inventory API Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        .test-result { border: 1px solid #ddd; padding: 15px; margin: 15px 0; background: white; border-radius: 5px; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        pre { background: #f8f8f8; padding: 10px; overflow-x: auto; border-left: 3px solid #007bff; }
        .endpoint { background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; display: inline-block; margin: 5px 0; }
        .method { font-weight: bold; color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>📋 Inventory API Test Suite</h1>
    <p>This page demonstrates all available Inventory CRUD API endpoints.</p>
    
    <div class="info">
        <strong>Base URL:</strong> <code>' . $baseUrl . '</code><br>
        <strong>Authentication:</strong> Session-based (must be logged in)<br>
        <strong>Response Format:</strong> JSON
    </div>

    <h2>🔗 Available Endpoints</h2>
    <table>
        <tr>
            <th>Method</th>
            <th>Endpoint</th>
            <th>Description</th>
            <th>Required Role</th>
        </tr>
        <tr>
            <td><span class="method">GET</span></td>
            <td>/api/inventory</td>
            <td>List all inventory items</td>
            <td>manager, staff, auditor, top_management</td>
        </tr>
        <tr>
            <td><span class="method">GET</span></td>
            <td>/api/inventory/{id}</td>
            <td>Show specific item</td>
            <td>manager, staff, auditor, top_management</td>
        </tr>
        <tr>
            <td><span class="method">POST</span></td>
            <td>/api/inventory</td>
            <td>Create new item</td>
            <td>manager, procurement_officer</td>
        </tr>
        <tr>
            <td><span class="method">PUT/PATCH</span></td>
            <td>/api/inventory/{id}</td>
            <td>Update item</td>
            <td>manager, procurement_officer</td>
        </tr>
        <tr>
            <td><span class="method">DELETE</span></td>
            <td>/api/inventory/{id}</td>
            <td>Delete item</td>
            <td>manager, it_administrator</td>
        </tr>
    </table>

    <h2>🧪 Quick Tests</h2>
    
    <div class="test-result">
        <h3>Test 1: List All Items</h3>
        <div class="endpoint">GET ' . $baseUrl . '</div>
        <p>Click the button below to test this endpoint:</p>
        <button onclick="testListAll()">Run Test</button>
        <pre id="result-list"></pre>
    </div>

    <div class="test-result">
        <h3>Test 2: Show Item #1</h3>
        <div class="endpoint">GET ' . $baseUrl . '/1</div>
        <button onclick="testShowItem(1)">Run Test</button>
        <pre id="result-show"></pre>
    </div>

    <div class="test-result">
        <h3>Test 3: List with Pagination</h3>
        <div class="endpoint">GET ' . $baseUrl . '?page=1&limit=10</div>
        <button onclick="testPagination()">Run Test</button>
        <pre id="result-pagination"></pre>
    </div>

    <div class="test-result">
        <h3>Test 4: Filter Low Stock Items</h3>
        <div class="endpoint">GET ' . $baseUrl . '?low_stock=true</div>
        <button onclick="testLowStock()">Run Test</button>
        <pre id="result-lowstock"></pre>
    </div>

    <h2>📚 Documentation</h2>
    <p>For complete API documentation, see:</p>
    <ul>
        <li><a href="' . base_url('INVENTORY_API_DOCS.md') . '">INVENTORY_API_DOCS.md</a></li>
        <li><a href="' . base_url('INVENTORY_CRUD_SUMMARY.md') . '">INVENTORY_CRUD_SUMMARY.md</a></li>
    </ul>

    <script>
        const baseUrl = "' . $baseUrl . '";
        
        function testListAll() {
            fetch(baseUrl)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result-list").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById("result-list").textContent = "Error: " + error.message;
                });
        }
        
        function testShowItem(id) {
            fetch(baseUrl + "/" + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result-show").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById("result-show").textContent = "Error: " + error.message;
                });
        }
        
        function testPagination() {
            fetch(baseUrl + "?page=1&limit=10")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result-pagination").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById("result-pagination").textContent = "Error: " + error.message;
                });
        }
        
        function testLowStock() {
            fetch(baseUrl + "?low_stock=true")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("result-lowstock").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById("result-lowstock").textContent = "Error: " + error.message;
                });
        }
    </script>
</body>
</html>';
        
        return $html;
    }
}

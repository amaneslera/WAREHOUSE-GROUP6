<?php
/**
 * Test Script for Approvals API
 * Run from browser to test all approval endpoints
 */

// Start session
session_start();

// For testing, set a user session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'warehouse_manager';
    $_SESSION['user_name'] = 'Test Manager';
}

// Load CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

use Config\Database;
use App\Models\StockMovementModel;

// Initialize database
$db = Database::connect();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approvals API Tester</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .response-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px; font-family: monospace; max-height: 300px; overflow-y: auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Approvals API Tester</h1>
        <p>Session: <strong><?php echo $_SESSION['user_name'] ?? 'Not Set'; ?></strong> (Role: <strong><?php echo $_SESSION['user_role'] ?? 'Not Set'; ?></strong>)</p>

        <div class="row">
            <div class="col-md-6">
                <h3>GET Endpoints</h3>
                
                <div class="card mb-3">
                    <div class="card-header">GET /api/approvals/stats</div>
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="testEndpoint('stats')">Test Stats</button>
                        <div id="stats-response" class="response-box" style="display:none;"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">GET /api/approvals/pending</div>
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="testEndpoint('pending')">Test Pending</button>
                        <div id="pending-response" class="response-box" style="display:none;"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">GET /api/approvals/history</div>
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="testEndpoint('history')">Test History</button>
                        <div id="history-response" class="response-box" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h3>POST Endpoints</h3>

                <div class="card mb-3">
                    <div class="card-header">POST /api/approvals/{id}/approve</div>
                    <div class="card-body">
                        <input type="number" id="approve-id" placeholder="Movement ID" class="form-control mb-2" value="1">
                        <input type="text" id="approve-notes" placeholder="Approval Notes" class="form-control mb-2">
                        <button class="btn btn-success" onclick="testEndpoint('approve')">Test Approve</button>
                        <div id="approve-response" class="response-box" style="display:none;"></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">POST /api/approvals/{id}/reject</div>
                    <div class="card-body">
                        <input type="number" id="reject-id" placeholder="Movement ID" class="form-control mb-2" value="2">
                        <input type="text" id="reject-reason" placeholder="Rejection Reason" class="form-control mb-2">
                        <button class="btn btn-danger" onclick="testEndpoint('reject')">Test Reject</button>
                        <div id="reject-response" class="response-box" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h3>Database Info</h3>
                <div class="card">
                    <div class="card-body">
                        <p><strong>Total Movements:</strong> <?php echo $db->table('stock_movements')->countAllResults(); ?></p>
                        <p><strong>Pending:</strong> <?php echo $db->table('stock_movements')->where('approval_status', 'pending')->countAllResults(); ?></p>
                        <p><strong>Approved:</strong> <?php echo $db->table('stock_movements')->where('approval_status', 'approved')->countAllResults(); ?></p>
                        <p><strong>Rejected:</strong> <?php echo $db->table('stock_movements')->where('approval_status', 'rejected')->countAllResults(); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '/WAREHOUSE-GROUP6/api';

        async function testEndpoint(endpoint) {
            try {
                let url = `${API_BASE}/approvals`;
                let options = { method: 'GET', headers: { 'Content-Type': 'application/json' } };
                let responseEl = document.getElementById(`${endpoint}-response`);

                if (endpoint === 'stats') {
                    url += '/stats';
                } else if (endpoint === 'pending') {
                    url += '/pending';
                } else if (endpoint === 'history') {
                    url += '/history';
                } else if (endpoint === 'approve') {
                    const id = document.getElementById('approve-id').value;
                    const notes = document.getElementById('approve-notes').value;
                    url += `/${id}/approve`;
                    options.method = 'POST';
                    options.body = JSON.stringify({ approval_notes: notes });
                } else if (endpoint === 'reject') {
                    const id = document.getElementById('reject-id').value;
                    const reason = document.getElementById('reject-reason').value;
                    url += `/${id}/reject`;
                    options.method = 'POST';
                    options.body = JSON.stringify({ rejection_reason: reason });
                }

                const response = await fetch(url, options);
                const data = await response.json();
                
                responseEl.innerHTML = `<strong>Status:</strong> ${response.status}<br><pre>${JSON.stringify(data, null, 2)}</pre>`;
                responseEl.style.display = 'block';
                responseEl.className = 'response-box ' + (response.ok ? 'success' : 'error');

            } catch (error) {
                document.getElementById(`${endpoint}-response`).innerHTML = `<strong>Error:</strong> ${error.message}`;
                document.getElementById(`${endpoint}-response`).style.display = 'block';
                document.getElementById(`${endpoint}-response`).className = 'response-box error';
            }
        }
    </script>
</body>
</html>

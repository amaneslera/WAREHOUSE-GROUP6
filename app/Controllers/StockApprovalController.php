<?php

namespace App\Controllers;

use App\Models\StockMovementModel;
use App\Models\InventoryModel;
use CodeIgniter\RESTful\ResourceController;

class StockApprovalController extends ResourceController
{
    protected $modelName = 'App\Models\StockMovementModel';
    protected $format = 'json';

    /**
     * Get pending approvals for warehouse manager
     * GET /api/approvals/pending
     */
    public function pending()
    {
        // Check if user is authenticated
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        try {
            $db = \Config\Database::connect();
            $movements = $db->table('stock_movements sm')
                           ->select('sm.*, u.first_name, u.last_name, i.item_name, i.current_stock')
                           ->join('users u', 'u.id = sm.performed_by', 'left')
                           ->join('inventory_items i', 'i.id = sm.inventory_item_id')
                           ->where('sm.approval_status', 'pending')
                           ->orderBy('sm.created_at', 'DESC')
                           ->get()
                           ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $movements,
                'count' => count($movements)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Pending approvals error: ' . $e->getMessage());
            return $this->fail('Error fetching approvals: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get movement details for approval review
     * GET /api/approvals/:id
     */
    public function show($id = null)
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        try {
            $db = \Config\Database::connect();
            $movement = $db->table('stock_movements sm')
                          ->select('sm.*, u.first_name, u.last_name, i.item_name, i.current_stock, w.warehouse_name')
                          ->join('users u', 'u.id = sm.performed_by', 'left')
                          ->join('inventory_items i', 'i.id = sm.inventory_item_id')
                          ->join('warehouses w', 'w.id = sm.to_warehouse_id OR w.id = sm.from_warehouse_id', 'left')
                          ->where('sm.id', $id)
                          ->get()
                          ->getRowArray();

            if (!$movement) {
                return $this->failNotFound('Movement not found');
            }

            return $this->respond([
                'status' => 'success',
                'data' => $movement
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Show approval error: ' . $e->getMessage());
            return $this->fail('Error fetching movement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve a stock movement
     * POST /api/approvals/:id/approve
     */
    public function approve($id = null)
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        if (!$id) {
            return $this->failValidationErrors('Movement ID is required');
        }

        try {
            // Log incoming request for debugging
            log_message('info', 'Approve request received for ID: ' . $id);
            log_message('info', 'Request body: ' . $this->request->getBody());
            
            $data = $this->request->getJSON(true);
            if ($data === null) {
                $data = [];
            }
            
            log_message('info', 'Parsed data: ' . json_encode($data));
            
            $movementModel = new StockMovementModel();

            // Get movement
            $movement = $movementModel->find($id);
            if (!$movement) {
                return $this->failNotFound('Movement not found');
            }

            if ($movement['approval_status'] !== 'pending') {
                return $this->fail('This movement has already been reviewed', 400);
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Update approval status using query builder to avoid "no data to update" error
                $builder = $db->table('stock_movements');
                $builder->where('id', $id);
                $builder->update([
                    'approval_status' => 'approved',
                    'approved_by' => session('user_id'),
                    'approval_notes' => $data['approval_notes'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // If approved, update inventory stock
                $inventoryModel = new InventoryModel();
                $item = $inventoryModel->find($movement['inventory_item_id']);

                if ($item) {
                    // Calculate new stock based on movement type
                    $newStock = $item['current_stock'];
                    
                    if ($movement['movement_type'] === 'in') {
                        $newStock += $movement['quantity'];
                    } elseif ($movement['movement_type'] === 'out') {
                        $newStock -= $movement['quantity'];
                    } elseif ($movement['movement_type'] === 'adjustment') {
                        $newStock = $movement['quantity'];
                    }
                    // Transfer doesn't affect total stock in this warehouse model
                    
                    $inventoryModel->update($movement['inventory_item_id'], [
                        'current_stock' => $newStock,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
                }

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Movement approved successfully',
                    'movement_id' => $id
                ], 200);
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }
        } catch (\Exception $e) {
            log_message('error', 'Approval error: ' . $e->getMessage());
            return $this->fail('Error approving movement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject a stock movement
     * POST /api/approvals/:id/reject
     */
    public function reject($id = null)
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        if (!$id) {
            return $this->failValidationErrors('Movement ID is required');
        }

        try {
            $data = $this->request->getJSON(true) ?? [];
            $movementModel = new StockMovementModel();

            // Get movement
            $movement = $movementModel->find($id);
            if (!$movement) {
                return $this->failNotFound('Movement not found');
            }

            if ($movement['approval_status'] !== 'pending') {
                return $this->fail('This movement has already been reviewed', 400);
            }

            // Update rejection status
            $updateData = [
                'approval_status' => 'rejected',
                'rejected_by' => session('user_id'),
                'rejection_reason' => $data['rejection_reason'] ?? null
            ];
            
            $movementModel->update($id, $updateData);

            return $this->respond([
                'status' => 'success',
                'message' => 'Movement rejected successfully',
                'movement_id' => $id
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Rejection error: ' . $e->getMessage());
            return $this->fail('Error rejecting movement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get approval statistics for manager dashboard
     * GET /api/approvals/stats
     */
    public function stats()
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        try {
            $db = \Config\Database::connect();
            $stats = [
                'pending' => $db->table('stock_movements')->where('approval_status', 'pending')->countAllResults(),
                'approved' => $db->table('stock_movements')->where('approval_status', 'approved')->countAllResults(),
                'rejected' => $db->table('stock_movements')->where('approval_status', 'rejected')->countAllResults(),
            ];

            return $this->respond([
                'status' => 'success',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Stats error: ' . $e->getMessage());
            return $this->fail('Error fetching stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get approval history/audit trail
     * GET /api/approvals/history?limit=20&offset=0
     */
    public function history()
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $limit = $this->request->getGet('limit') ?? 20;
        $offset = $this->request->getGet('offset') ?? 0;

        try {
            $db = \Config\Database::connect();
            $approvals = $db->table('stock_movements sm')
                           ->select('sm.*, u.first_name, u.last_name, am.first_name as approver_first_name, am.last_name as approver_last_name, i.item_name')
                           ->join('users u', 'u.id = sm.performed_by', 'left')
                           ->join('users am', 'am.id = sm.approved_by OR am.id = sm.rejected_by', 'left')
                           ->join('inventory_items i', 'i.id = sm.inventory_item_id')
                           ->where('sm.approval_status !=', 'pending')
                           ->orderBy('sm.updated_at', 'DESC')
                           ->limit($limit, $offset)
                           ->get()
                           ->getResultArray();

            return $this->respond([
                'status' => 'success',
                'data' => $approvals,
                'count' => count($approvals)
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'History error: ' . $e->getMessage());
            return $this->fail('Error fetching history: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Permission check helper
     */
    private function hasPermission(...$roles)
    {
        $userRole = session('user_role');
        return in_array($userRole, $roles);
    }
}

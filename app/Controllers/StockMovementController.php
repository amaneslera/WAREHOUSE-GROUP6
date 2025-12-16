<?php

namespace App\Controllers;

use App\Models\StockMovementModel;
use App\Models\InventoryModel;
use App\Models\WarehouseModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * StockMovementController
 * 
 * Handles all stock movement operations:
 * - Stock IN/OUT
 * - Warehouse transfers
 * - Stock adjustments
 * - Movement history and reports
 * 
 * Supports both web views and RESTful API responses
 * 
 * @package App\Controllers
 */
class StockMovementController extends BaseController
{
    protected $movementModel;
    protected $inventoryModel;
    protected $warehouseModel;
    protected $db;
    
    public function __construct()
    {
        $this->movementModel = new StockMovementModel();
        $this->inventoryModel = new InventoryModel();
        $this->warehouseModel = new WarehouseModel();
        $this->db = \Config\Database::connect();
    }

    // ========================================
    // WEB ROUTES (HTML Views)
    // ========================================

    /**
     * Display all stock movements
     * 
     * @return string|ResponseInterface
     */
    public function index()
    {
        // Check permissions
        if (!$this->checkPermission(['warehouse_manager', 'inventory_auditor', 'warehouse_staff'])) {
            return $this->unauthorizedResponse();
        }

        $filters = [
            'movement_type' => $this->request->getGet('type'),
            'warehouse_id'  => $this->request->getGet('warehouse'),
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to')
        ];

        $data = [
            'movements'  => $this->movementModel->getMovementsWithDetails($filters),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown(),
            'stats'      => $this->movementModel->getMovementStats($filters)
        ];

        return view('stock_movements/index', $data);
    }

    /**
     * Show form to record stock IN
     * 
     * @return string|ResponseInterface
     */
    public function stockInForm()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->unauthorizedResponse();
        }

        $data = [
            'items'      => $this->inventoryModel->getItemsWithRelations(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];

        return view('stock_movements/stock_in', $data);
    }

    /**
     * Show form to record stock OUT
     * 
     * @return string|ResponseInterface
     */
    public function stockOutForm()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->unauthorizedResponse();
        }

        $data = [
            'items'      => $this->inventoryModel->getItemsWithRelations(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];

        return view('stock_movements/stock_out', $data);
    }

    /**
     * Show form for warehouse transfer
     * 
     * @return string|ResponseInterface
     */
    public function transferForm()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->unauthorizedResponse();
        }

        $data = [
            'items'      => $this->inventoryModel->getItemsWithRelations(),
            'warehouses' => $this->warehouseModel->getWarehousesForDropdown()
        ];

        return view('stock_movements/transfer', $data);
    }

    // ========================================
    // API ROUTES (JSON Responses)
    // ========================================

    /**
     * API: Get all movements (with filters)
     * 
     * GET /api/stock-movements
     * 
     * @return ResponseInterface
     */
    public function apiGetMovements()
    {
        if (!$this->checkPermission(['warehouse_manager', 'inventory_auditor', 'warehouse_staff', 'top_management'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $filters = [
            'movement_type'     => $this->request->getGet('type'),
            'warehouse_id'      => $this->request->getGet('warehouse_id'),
            'inventory_item_id' => $this->request->getGet('item_id'),
            'date_from'         => $this->request->getGet('date_from'),
            'date_to'           => $this->request->getGet('date_to')
        ];

        $movements = $this->movementModel->getMovementsWithDetails($filters);

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $movements,
            'count'  => count($movements)
        ]);
    }

    /**
     * API: Record stock IN transaction
     * 
     * POST /api/stock-movements/in
     * 
     * Expected JSON:
     * {
     *   "item_id": 1,
     *   "warehouse_id": 2,
     *   "quantity": 100,
     *   "reference": "PO-2025-001",
     *   "notes": "Purchase order delivery"
     * }
     * 
     * @return ResponseInterface
     */
    public function apiStockIn()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $data = $this->getRequestData();

        // Validate required fields
        if (empty($data['item_id']) || empty($data['warehouse_id']) || empty($data['quantity'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Missing required fields: item_id, warehouse_id, quantity'
            ], 400);
        }

        // Check if item exists
        $item = $this->inventoryModel->find($data['item_id']);
        if (!$item) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Inventory item not found'
            ], 404);
        }

        // Start transaction
        $this->db->transStart();

        // Record movement
        $movementId = $this->movementModel->recordStockIn(
            $data['item_id'],
            $data['warehouse_id'],
            $data['quantity'],
            session('user_id'),
            $data['reference'] ?? null,
            $data['notes'] ?? null
        );

        if (!$movementId) {
            $this->db->transRollback();
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Failed to record stock movement',
                'errors'  => $this->movementModel->errors()
            ], 500);
        }

        // Update inventory stock
        $newStock = $item['current_stock'] + $data['quantity'];
        $this->inventoryModel->updateStock($data['item_id'], $newStock);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Transaction failed'
            ], 500);
        }

        return $this->jsonResponse([
            'status'      => 'success',
            'message'     => 'Stock IN recorded successfully',
            'movement_id' => $movementId,
            'new_stock'   => $newStock
        ], 201);
    }

    /**
     * API: Record stock OUT transaction
     * 
     * POST /api/stock-movements/out
     * 
     * Expected JSON:
     * {
     *   "item_id": 1,
     *   "warehouse_id": 2,
     *   "quantity": 50,
     *   "reference": "DO-2025-001",
     *   "notes": "Delivery to construction site"
     * }
     * 
     * @return ResponseInterface
     */
    public function apiStockOut()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $data = $this->getRequestData();

        // Validate required fields
        if (empty($data['item_id']) || empty($data['warehouse_id']) || empty($data['quantity'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Missing required fields: item_id, warehouse_id, quantity'
            ], 400);
        }

        // Check if item exists
        $item = $this->inventoryModel->find($data['item_id']);
        if (!$item) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Inventory item not found'
            ], 404);
        }

        // Check if sufficient stock
        if ($item['current_stock'] < $data['quantity']) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Insufficient stock',
                'available' => $item['current_stock'],
                'requested' => $data['quantity']
            ], 400);
        }

        // Start transaction
        $this->db->transStart();

        // Record movement
        $movementId = $this->movementModel->recordStockOut(
            $data['item_id'],
            $data['warehouse_id'],
            $data['quantity'],
            session('user_id'),
            $data['reference'] ?? null,
            $data['notes'] ?? null
        );

        if (!$movementId) {
            $this->db->transRollback();
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Failed to record stock movement',
                'errors'  => $this->movementModel->errors()
            ], 500);
        }

        // Update inventory stock
        $newStock = $item['current_stock'] - $data['quantity'];
        $this->inventoryModel->updateStock($data['item_id'], $newStock);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Transaction failed'
            ], 500);
        }

        return $this->jsonResponse([
            'status'      => 'success',
            'message'     => 'Stock OUT recorded successfully',
            'movement_id' => $movementId,
            'new_stock'   => $newStock
        ], 201);
    }

    /**
     * API: Record warehouse transfer
     * 
     * POST /api/stock-movements/transfer
     * 
     * Expected JSON:
     * {
     *   "item_id": 1,
     *   "from_warehouse_id": 2,
     *   "to_warehouse_id": 3,
     *   "quantity": 25,
     *   "reference": "TR-2025-001",
     *   "notes": "Transfer to new warehouse"
     * }
     * 
     * @return ResponseInterface
     */
    public function apiTransfer()
    {
        if (!$this->checkPermission(['warehouse_manager', 'warehouse_staff'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $data = $this->getRequestData();

        // Validate required fields
        if (empty($data['item_id']) || empty($data['from_warehouse_id']) || 
            empty($data['to_warehouse_id']) || empty($data['quantity'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Missing required fields: item_id, from_warehouse_id, to_warehouse_id, quantity'
            ], 400);
        }

        // Prevent same warehouse transfer
        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Cannot transfer to the same warehouse'
            ], 400);
        }

        // Check source item exists and has enough stock
        $sourceItem = $this->inventoryModel
            ->where('id', $data['item_id'])
            ->first();

        if (!$sourceItem) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Item not found'
            ], 404);
        }

        if ($sourceItem['warehouse_id'] != $data['from_warehouse_id']) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Item is not in the source warehouse'
            ], 400);
        }

        if ($sourceItem['current_stock'] < $data['quantity']) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Insufficient stock in source warehouse',
                'available' => $sourceItem['current_stock'],
                'requested' => $data['quantity']
            ], 400);
        }

        // Check if same item exists in destination warehouse
        $destItem = $this->inventoryModel
            ->where('item_id', $sourceItem['item_id'])
            ->where('warehouse_id', $data['to_warehouse_id'])
            ->first();

        // Start transaction
        $this->db->transStart();

        try {
            // Record transfer movement
            $movementId = $this->movementModel->recordTransfer(
                $data['item_id'],
                $data['from_warehouse_id'],
                $data['to_warehouse_id'],
                $data['quantity'],
                session('user_id'),
                $data['reference'] ?? null,
                $data['notes'] ?? null
            );

            if (!$movementId) {
                throw new \Exception('Failed to record transfer movement');
            }

            // Decrease stock in source warehouse
            $newSourceStock = $sourceItem['current_stock'] - $data['quantity'];
            $this->inventoryModel->update($sourceItem['id'], [
                'current_stock' => $newSourceStock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Increase stock in destination warehouse OR create new entry
            if ($destItem) {
                // Item exists in destination - just increase stock
                $newDestStock = $destItem['current_stock'] + $data['quantity'];
                $this->inventoryModel->update($destItem['id'], [
                    'current_stock' => $newDestStock,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Item doesn't exist in destination - create new entry
                $newEntry = [
                    'item_id'       => $sourceItem['item_id'] . '-' . $data['to_warehouse_id'], // Make unique
                    'item_name'     => $sourceItem['item_name'],
                    'category_id'   => $sourceItem['category_id'],
                    'warehouse_id'  => $data['to_warehouse_id'],
                    'current_stock' => $data['quantity'],
                    'minimum_stock' => $sourceItem['minimum_stock'],
                    'unit_price'    => $sourceItem['unit_price'],
                    'unit_of_measure' => $sourceItem['unit_of_measure'] ?? 'pcs',
                    'description'   => $sourceItem['description'],
                    'status'        => 'active'
                ];
                $this->inventoryModel->insert($newEntry);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed to complete');
            }

            return $this->jsonResponse([
                'status'          => 'success',
                'message'         => 'Transfer recorded successfully. Awaiting manager approval.',
                'movement_id'     => $movementId,
                'source_stock'    => $newSourceStock,
                'destination_stock' => $destItem ? ($destItem['current_stock'] + $data['quantity']) : $data['quantity']
            ], 201);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Transfer error: ' . $e->getMessage());
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Record stock adjustment
     * 
     * POST /api/stock-movements/adjustment
     * 
     * @return ResponseInterface
     */
    public function apiAdjustment()
    {
        if (!$this->checkPermission(['warehouse_manager', 'inventory_auditor'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $data = $this->getRequestData();

        // Validate required fields
        if (empty($data['item_id']) || empty($data['warehouse_id']) || !isset($data['quantity'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Missing required fields: item_id, warehouse_id, quantity'
            ], 400);
        }

        // Check if item exists
        $item = $this->inventoryModel->find($data['item_id']);
        if (!$item) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Inventory item not found'
            ], 404);
        }

        // Calculate new stock
        $newStock = $item['current_stock'] + $data['quantity'];
        if ($newStock < 0) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Adjustment would result in negative stock',
                'current_stock' => $item['current_stock'],
                'adjustment' => $data['quantity']
            ], 400);
        }

        // Start transaction
        $this->db->transStart();

        // Record adjustment
        $movementId = $this->movementModel->recordAdjustment(
            $data['item_id'],
            $data['warehouse_id'],
            $data['quantity'],
            session('user_id'),
            $data['reference'] ?? null,
            $data['notes'] ?? null
        );

        if (!$movementId) {
            $this->db->transRollback();
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Failed to record adjustment',
                'errors'  => $this->movementModel->errors()
            ], 500);
        }

        // Update inventory stock
        $this->inventoryModel->updateStock($data['item_id'], $newStock);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Transaction failed'
            ], 500);
        }

        return $this->jsonResponse([
            'status'      => 'success',
            'message'     => 'Stock adjustment recorded successfully',
            'movement_id' => $movementId,
            'old_stock'   => $item['current_stock'],
            'adjustment'  => $data['quantity'],
            'new_stock'   => $newStock
        ], 201);
    }

    /**
     * API: Get movement statistics
     * 
     * GET /api/stock-movements/stats
     * 
     * @return ResponseInterface
     */
    public function apiGetStats()
    {
        if (!$this->checkPermission(['warehouse_manager', 'inventory_auditor', 'top_management'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $filters = [
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to')
        ];

        $stats = $this->movementModel->getMovementStats($filters);

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $stats
        ]);
    }

    /**
     * API: Get item movement history
     * 
     * GET /api/stock-movements/item/{id}
     * 
     * @param int $id Item ID
     * @return ResponseInterface
     */
    public function apiGetItemHistory($id)
    {
        if (!$this->checkPermission(['warehouse_manager', 'inventory_auditor', 'warehouse_staff'])) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $history = $this->movementModel->getItemHistory($id);

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $history,
            'count'  => count($history)
        ]);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if user has required permission
     * 
     * @param array $allowedRoles
     * @return bool
     */
    private function checkPermission(array $allowedRoles): bool
    {
        $userRole = session('user_role');
        return in_array($userRole, $allowedRoles);
    }

    /**
     * Return unauthorized response
     * 
     * @return ResponseInterface
     */
    private function unauthorizedResponse()
    {
        session()->setFlashdata('error', 'Unauthorized access');
        return redirect()->to('/login');
    }

    /**
     * Return JSON response
     * 
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }

    /**
     * Get request data (from JSON or POST)
     * 
     * @return array
     */
    private function getRequestData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            return $this->request->getJSON(true) ?? [];
        }
        
        return $this->request->getPost() ?? [];
    }
}

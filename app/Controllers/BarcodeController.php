<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\StockMovementModel;
use App\Models\WarehouseModel;
use CodeIgniter\RESTful\ResourceController;

class BarcodeController extends ResourceController
{
    protected $modelName = 'App\Models\InventoryModel';
    protected $format = 'json';

    /**
     * Lookup item by barcode or ID
     * GET /api/barcode/lookup?barcode=12345
     */
    public function lookup()
    {
        $barcode = $this->request->getGet('barcode');
        
        if (!$barcode) {
            return $this->failValidationErrors('Barcode is required');
        }

        $inventoryModel = new InventoryModel();
        $item = $inventoryModel->where('item_id', $barcode)
                               ->orWhere('barcode', $barcode)
                               ->first();

        if (!$item) {
            return $this->failNotFound('Item not found');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $item
        ], 200);
    }

    /**
     * Get item details with warehouse stock info
     * GET /api/barcode/item/:id
     */
    public function getItem($id = null)
    {
        $inventoryModel = new InventoryModel();
        
        $item = $inventoryModel->select('inventory_items.*, warehouses.warehouse_name')
                               ->join('warehouses', 'warehouses.id = inventory_items.warehouse_id')
                               ->where('inventory_items.id', $id)
                               ->first();

        if (!$item) {
            return $this->failNotFound('Item not found');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $item
        ], 200);
    }

    /**
     * Search items by name or category
     * GET /api/barcode/search?q=iron
     */
    public function search()
    {
        $query = $this->request->getGet('q');
        
        if (strlen($query) < 2) {
            return $this->respond(['status' => 'success', 'data' => []]);
        }

        $inventoryModel = new InventoryModel();
        $items = $inventoryModel->where('item_name LIKE', '%' . $query . '%')
                                ->orWhere('item_id LIKE', '%' . $query . '%')
                                ->limit(10)
                                ->findAll();

        return $this->respond([
            'status' => 'success',
            'data' => $items,
            'count' => count($items)
        ], 200);
    }

    /**
     * Record stock in for scanned item
     * POST /api/barcode/stock-in
     */
    public function stockIn()
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $data = $this->request->getJSON(true);
        
        $rules = [
            'item_id' => 'required',
            'quantity' => 'required|numeric|greater_than[0]',
            'warehouse_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $inventoryModel = new InventoryModel();
        $movementModel = new StockMovementModel();

        // Check if item exists
        $item = $inventoryModel->find($data['item_id']);
        if (!$item) {
            return $this->failNotFound('Item not found');
        }

        try {
            // Update inventory
            $newStock = $item['current_stock'] + $data['quantity'];
            $inventoryModel->update($data['item_id'], [
                'current_stock' => $newStock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Record movement
            $movementModel->insert([
                'inventory_item_id' => $data['item_id'],
                'movement_type' => 'in',
                'from_warehouse_id' => null,
                'to_warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
                'reference_number' => $data['reference'] ?? 'BARCODE-' . time(),
                'performed_by' => session('user_id'),
                'notes' => $data['notes'] ?? null,
                'approval_status' => 'pending'
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Stock in recorded successfully',
                'new_stock' => $newStock
            ], 201);
        } catch (\Exception $e) {
            log_message('error', 'Stock in error: ' . $e->getMessage());
            return $this->fail('Error recording stock in: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Record stock out for scanned item
     * POST /api/barcode/stock-out
     */
    public function stockOut()
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Not authenticated');
        }

        $data = $this->request->getJSON(true);
        
        $rules = [
            'item_id' => 'required',
            'quantity' => 'required|numeric|greater_than[0]',
            'warehouse_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $inventoryModel = new InventoryModel();
        $movementModel = new StockMovementModel();

        // Check if item exists
        $item = $inventoryModel->find($data['item_id']);
        if (!$item) {
            return $this->failNotFound('Item not found');
        }

        // Check if sufficient stock
        if ($item['current_stock'] < $data['quantity']) {
            return $this->fail('Insufficient stock. Available: ' . $item['current_stock'], 400);
        }

        try {
            // Update inventory
            $newStock = $item['current_stock'] - $data['quantity'];
            $inventoryModel->update($data['item_id'], [
                'current_stock' => $newStock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Record movement
            $movementModel->insert([
                'inventory_item_id' => $data['item_id'],
                'movement_type' => 'out',
                'from_warehouse_id' => $data['warehouse_id'],
                'to_warehouse_id' => null,
                'quantity' => $data['quantity'],
                'reference_number' => $data['reference'] ?? 'BARCODE-' . time(),
                'performed_by' => session('user_id'),
                'notes' => $data['notes'] ?? null,
                'approval_status' => 'pending'
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Stock out recorded successfully',
                'new_stock' => $newStock
            ], 201);
        } catch (\Exception $e) {
            log_message('error', 'Stock out error: ' . $e->getMessage());
            return $this->fail('Error recording stock out: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate QR code for item
     * GET /api/barcode/qr/:itemId
     */
    public function generateQR($itemId = null)
    {
        $inventoryModel = new InventoryModel();
        $item = $inventoryModel->find($itemId);

        if (!$item) {
            return $this->failNotFound('Item not found');
        }

        // Using QR code generation - would need QR library installed
        $qrData = [
            'item_id' => $item['item_id'],
            'item_name' => $item['item_name'],
            'warehouse_id' => $item['warehouse_id']
        ];

        return $this->respond([
            'status' => 'success',
            'qr_data' => $qrData,
            'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode(json_encode($qrData))
        ], 200);
    }

    /**
     * Check permission helper
     */
    private function hasPermission(...$roles)
    {
        $userRole = session('user_role');
        return in_array($userRole, $roles);
    }
}

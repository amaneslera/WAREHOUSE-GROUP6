<?php

namespace App\Controllers;

use App\Models\WarehouseModel;
use App\Models\InventoryModel;
use CodeIgniter\RESTful\ResourceController;

class WarehouseController extends ResourceController
{
    protected $modelName = 'App\Models\WarehouseModel';
    protected $format = 'json';

    /**
     * Get all warehouses with inventory summary
     * GET /api/warehouses
     */
    public function index()
    {
        $warehouseModel = new WarehouseModel();
        $inventoryModel = new InventoryModel();

        $warehouses = $warehouseModel->findAll();

        // Add inventory summary to each warehouse
        foreach ($warehouses as &$warehouse) {
            $inventory = $inventoryModel->where('warehouse_id', $warehouse['id'])
                                        ->select('COUNT(*) as item_count, SUM(current_stock * unit_price) as total_value')
                                        ->first();
            
            $warehouse['item_count'] = $inventory['item_count'] ?? 0;
            $warehouse['total_value'] = $inventory['total_value'] ?? 0;
            $warehouse['capacity_used'] = 0; // Add if you have capacity tracking
        }

        return $this->respond([
            'status' => 'success',
            'data' => $warehouses,
            'count' => count($warehouses)
        ], 200);
    }

    /**
     * Get specific warehouse with inventory details
     * GET /api/warehouses/:id
     */
    public function show($id = null)
    {
        $warehouseModel = new WarehouseModel();
        $inventoryModel = new InventoryModel();

        $warehouse = $warehouseModel->find($id);

        if (!$warehouse) {
            return $this->failNotFound('Warehouse not found');
        }

        // Get inventory summary
        $inventory = $inventoryModel->where('warehouse_id', $id)
                                    ->select('COUNT(*) as item_count, SUM(current_stock * unit_price) as total_value')
                                    ->first();

        $warehouse['item_count'] = $inventory['item_count'] ?? 0;
        $warehouse['total_value'] = $inventory['total_value'] ?? 0;

        return $this->respond([
            'status' => 'success',
            'data' => $warehouse
        ], 200);
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * StockMovementModel
 * 
 * Handles all stock movement transactions including:
 * - Stock IN (receiving from vendors)
 * - Stock OUT (dispatching to clients/projects)
 * - Transfers between warehouses
 * - Stock adjustments (corrections, damages)
 * 
 * @package App\Models
 */
class StockMovementModel extends Model
{
    protected $table = 'stock_movements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'inventory_item_id',
        'movement_type',
        'quantity',
        'from_warehouse_id',
        'to_warehouse_id',
        'reference_number',
        'notes',
        'performed_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'inventory_item_id' => 'required|integer',
        'movement_type'     => 'required|in_list[in,out,transfer,adjustment]',
        'quantity'          => 'required|integer|greater_than[0]',
        'performed_by'      => 'required|integer',
    ];

    protected $validationMessages = [
        'inventory_item_id' => [
            'required' => 'Inventory item is required',
            'integer'  => 'Invalid inventory item'
        ],
        'movement_type' => [
            'required' => 'Movement type is required',
            'in_list'  => 'Invalid movement type'
        ],
        'quantity' => [
            'required'      => 'Quantity is required',
            'integer'       => 'Quantity must be a number',
            'greater_than'  => 'Quantity must be greater than 0'
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get stock movements with related data (item, warehouse, user)
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getMovementsWithDetails($filters = [])
    {
        $builder = $this->db->table('stock_movements sm');
        $builder->select('
            sm.*,
            ii.item_id,
            ii.item_name,
            wf.warehouse_name as from_warehouse,
            wt.warehouse_name as to_warehouse,
            CONCAT(u.first_name, " ", u.last_name) as performed_by_name
        ');
        $builder->join('inventory_items ii', 'ii.id = sm.inventory_item_id', 'left');
        $builder->join('warehouses wf', 'wf.id = sm.from_warehouse_id', 'left');
        $builder->join('warehouses wt', 'wt.id = sm.to_warehouse_id', 'left');
        $builder->join('users u', 'u.id = sm.performed_by', 'left');

        // Apply filters
        if (!empty($filters['movement_type'])) {
            $builder->where('sm.movement_type', $filters['movement_type']);
        }
        if (!empty($filters['warehouse_id'])) {
            $builder->groupStart()
                ->where('sm.from_warehouse_id', $filters['warehouse_id'])
                ->orWhere('sm.to_warehouse_id', $filters['warehouse_id'])
                ->groupEnd();
        }
        if (!empty($filters['inventory_item_id'])) {
            $builder->where('sm.inventory_item_id', $filters['inventory_item_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('sm.created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('sm.created_at <=', $filters['date_to']);
        }

        $builder->orderBy('sm.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    /**
     * Get movements for a specific inventory item
     * 
     * @param int $itemId
     * @return array
     */
    public function getItemHistory($itemId)
    {
        return $this->getMovementsWithDetails(['inventory_item_id' => $itemId]);
    }

    /**
     * Get recent movements (last 50)
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentMovements($limit = 50)
    {
        return $this->getMovementsWithDetails();
    }

    /**
     * Record stock IN transaction
     * 
     * @param int $itemId
     * @param int $warehouseId
     * @param int $quantity
     * @param int $userId
     * @param string|null $reference
     * @param string|null $notes
     * @return bool|int
     */
    public function recordStockIn($itemId, $warehouseId, $quantity, $userId, $reference = null, $notes = null)
    {
        $data = [
            'inventory_item_id' => $itemId,
            'movement_type'     => 'in',
            'quantity'          => $quantity,
            'to_warehouse_id'   => $warehouseId,
            'reference_number'  => $reference,
            'notes'             => $notes,
            'performed_by'      => $userId
        ];

        return $this->insert($data);
    }

    /**
     * Record stock OUT transaction
     * 
     * @param int $itemId
     * @param int $warehouseId
     * @param int $quantity
     * @param int $userId
     * @param string|null $reference
     * @param string|null $notes
     * @return bool|int
     */
    public function recordStockOut($itemId, $warehouseId, $quantity, $userId, $reference = null, $notes = null)
    {
        $data = [
            'inventory_item_id' => $itemId,
            'movement_type'     => 'out',
            'quantity'          => $quantity,
            'from_warehouse_id' => $warehouseId,
            'reference_number'  => $reference,
            'notes'             => $notes,
            'performed_by'      => $userId
        ];

        return $this->insert($data);
    }

    /**
     * Record warehouse TRANSFER transaction
     * 
     * @param int $itemId
     * @param int $fromWarehouseId
     * @param int $toWarehouseId
     * @param int $quantity
     * @param int $userId
     * @param string|null $reference
     * @param string|null $notes
     * @return bool|int
     */
    public function recordTransfer($itemId, $fromWarehouseId, $toWarehouseId, $quantity, $userId, $reference = null, $notes = null)
    {
        $data = [
            'inventory_item_id' => $itemId,
            'movement_type'     => 'transfer',
            'quantity'          => $quantity,
            'from_warehouse_id' => $fromWarehouseId,
            'to_warehouse_id'   => $toWarehouseId,
            'reference_number'  => $reference,
            'notes'             => $notes,
            'performed_by'      => $userId
        ];

        return $this->insert($data);
    }

    /**
     * Record stock ADJUSTMENT transaction (corrections, damages, etc.)
     * 
     * @param int $itemId
     * @param int $warehouseId
     * @param int $quantity (can be negative for deductions)
     * @param int $userId
     * @param string|null $reference
     * @param string|null $notes
     * @return bool|int
     */
    public function recordAdjustment($itemId, $warehouseId, $quantity, $userId, $reference = null, $notes = null)
    {
        $data = [
            'inventory_item_id' => $itemId,
            'movement_type'     => 'adjustment',
            'quantity'          => $quantity,
            'from_warehouse_id' => $warehouseId,
            'reference_number'  => $reference,
            'notes'             => $notes,
            'performed_by'      => $userId
        ];

        return $this->insert($data);
    }

    /**
     * Get movement statistics by type
     * 
     * @param array $filters
     * @return array
     */
    public function getMovementStats($filters = [])
    {
        $builder = $this->db->table('stock_movements');
        $builder->select('
            movement_type,
            COUNT(*) as transaction_count,
            SUM(quantity) as total_quantity
        ');

        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        $builder->groupBy('movement_type');
        return $builder->get()->getResultArray();
    }
}

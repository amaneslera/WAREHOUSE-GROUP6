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

    /**
     * Get total movement summary (IN, OUT, TRANSFER)
     * 
     * @param array $dateRange Optional date range ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     * @return array
     */
    public function getTotalMovementSummary($dateRange = [])
    {
        $builder = $this->db->table('stock_movements');
        $builder->select('
            movement_type,
            COUNT(*) as transaction_count,
            SUM(quantity) as total_quantity
        ');

        if (!empty($dateRange['from'])) {
            $builder->where('created_at >=', $dateRange['from']);
        }
        if (!empty($dateRange['to'])) {
            $builder->where('created_at <=', $dateRange['to']);
        }

        $builder->groupBy('movement_type');
        $results = $builder->get()->getResultArray();

        // Transform into structured format
        $summary = [
            'in' => ['count' => 0, 'quantity' => 0],
            'out' => ['count' => 0, 'quantity' => 0],
            'transfer' => ['count' => 0, 'quantity' => 0],
            'adjustment' => ['count' => 0, 'quantity' => 0]
        ];

        foreach ($results as $row) {
            if (isset($summary[$row['movement_type']])) {
                $summary[$row['movement_type']] = [
                    'count' => (int)$row['transaction_count'],
                    'quantity' => (int)$row['total_quantity']
                ];
            }
        }

        return $summary;
    }

    /**
     * Get most moved items across all warehouses
     * 
     * @param int $limit
     * @return array
     */
    public function getMostMovedItems($limit = 10)
    {
        $builder = $this->db->table('stock_movements sm');
        $builder->select('
            i.id as item_id,
            i.item_id as item_code,
            i.item_name,
            c.category_name,
            COUNT(sm.id) as movement_count,
            SUM(sm.quantity) as total_quantity_moved,
            SUM(CASE WHEN sm.movement_type = "in" THEN sm.quantity ELSE 0 END) as quantity_in,
            SUM(CASE WHEN sm.movement_type = "out" THEN sm.quantity ELSE 0 END) as quantity_out,
            SUM(CASE WHEN sm.movement_type = "transfer" THEN sm.quantity ELSE 0 END) as quantity_transferred
        ');
        $builder->join('inventory_items i', 'i.id = sm.inventory_item_id');
        $builder->join('categories c', 'c.id = i.category_id', 'left');
        $builder->groupBy('i.id, i.item_id, i.item_name, c.category_name');
        $builder->orderBy('movement_count', 'DESC');
        $builder->limit($limit);
        return $builder->get()->getResultArray();
    }

    /**
     * Get warehouse turnover rate (basic calculation)
     * Turnover = Total OUT / Average Stock
     * 
     * @param int $warehouseId
     * @param int $days Period in days (default 30)
     * @return array
     */
    public function getWarehouseTurnoverRate($warehouseId, $days = 30)
    {
        $dateFrom = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Get total OUT movements
        $builder = $this->db->table('stock_movements sm');
        $builder->select('SUM(sm.quantity) as total_out');
        $builder->where('sm.from_warehouse_id', $warehouseId);
        $builder->where('sm.movement_type', 'out');
        $builder->where('sm.created_at >=', $dateFrom);
        $outResult = $builder->get()->getRowArray();
        $totalOut = (int)($outResult['total_out'] ?? 0);

        // Get average stock for warehouse
        $builder = $this->db->table('inventory_items');
        $builder->select('AVG(current_stock) as avg_stock, SUM(current_stock) as total_stock');
        $builder->where('warehouse_id', $warehouseId);
        $stockResult = $builder->get()->getRowArray();
        $avgStock = (float)($stockResult['avg_stock'] ?? 0);
        $totalStock = (int)($stockResult['total_stock'] ?? 0);

        // Calculate turnover rate
        $turnoverRate = $avgStock > 0 ? round($totalOut / $avgStock, 2) : 0;

        return [
            'warehouse_id' => $warehouseId,
            'period_days' => $days,
            'total_out' => $totalOut,
            'average_stock' => round($avgStock, 2),
            'current_total_stock' => $totalStock,
            'turnover_rate' => $turnoverRate,
            'turnover_percentage' => round($turnoverRate * 100, 2)
        ];
    }
}

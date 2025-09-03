<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'item_id',
        'item_name', 
        'category_id',
        'warehouse_id',
        'current_stock',
        'minimum_stock',
        'unit_price',
        'unit_of_measure',
        'description',
        'status',
        'supplier_info'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'item_id'       => 'required',  // We'll handle uniqueness in controller for updates
        'item_name'     => 'required|min_length[3]|max_length[255]',
        'category_id'   => 'required|integer',
        'warehouse_id'  => 'required|integer',
        'current_stock' => 'required|integer|greater_than_equal_to[0]',
        'minimum_stock' => 'required|integer|greater_than_equal_to[0]',
        'unit_price'    => 'required|decimal|greater_than[0]',
    ];

    protected $validationMessages = [
        'item_id' => [
            'required'   => 'Item ID is required',
        ],
        'item_name' => [
            'required'   => 'Item name is required',
            'min_length' => 'Item name must be at least 3 characters long'
        ],
        'current_stock' => [
            'required' => 'Current stock is required',
            'integer'  => 'Current stock must be a number',
            'greater_than_equal_to' => 'Current stock cannot be negative'
        ],
        'unit_price' => [
            'required'     => 'Unit price is required',
            'greater_than' => 'Unit price must be greater than 0'
        ],
    ];

    // Method to check if item_id is unique for new records
    public function isItemIdUnique($itemId, $excludeId = null)
    {
        $builder = $this->where('item_id', $itemId);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() === 0;
    }

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    
    public function updateStock($id, $newStock)
    {
        $item = $this->find($id);
        if (!$item) {
            return false;
        }

        $status = 'active';
        if ($newStock == 0) {
            $status = 'inactive';
        } elseif ($newStock <= $item['minimum_stock']) {
            $status = 'active'; 
        }

        return $this->update($id, [
            'current_stock' => $newStock,
            'status' => $status
        ]);
    }

    public function getLowStockItems()
    {
        return $this->where('current_stock <=', 'minimum_stock', false)
                    ->orWhere('current_stock', 0)
                    ->findAll();
    }

    public function getItemsByWarehouse($warehouseId)
    {
        return $this->where('warehouse_id', $warehouseId)->findAll();
    }

    public function getItemsByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)->findAll();
    }

    public function getTotalValue()
    {
        $items = $this->findAll();
        $totalValue = 0;
        
        foreach ($items as $item) {
            $totalValue += ($item['current_stock'] * $item['unit_price']);
        }
        
        return $totalValue;
    }

    public function getWarehouseStats()
    {
        
        $builder = $this->db->table('inventory_items i');
        $builder->select('w.warehouse_name, COUNT(i.id) as item_count, SUM(i.current_stock) as total_items, SUM(i.current_stock * i.unit_price) as total_value');
        $builder->join('warehouses w', 'w.id = i.warehouse_id');
        $builder->groupBy('w.id, w.warehouse_name');
        $results = $builder->get()->getResultArray();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['warehouse_name']] = [
                'total_items' => (int)$result['total_items'],
                'total_value' => (float)$result['total_value'],
                'item_count' => (int)$result['item_count']
            ];
        }

        return $stats;
    }

    // Get items with category and warehouse names
    public function getItemsWithRelations()
    {
        $builder = $this->db->table('inventory_items i');
        $builder->select('i.*, c.category_name, w.warehouse_name');
        $builder->join('categories c', 'c.id = i.category_id');
        $builder->join('warehouses w', 'w.id = i.warehouse_id');
        $builder->orderBy('i.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'warehouse_name',
        'location',
        'capacity',
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'warehouse_name' => 'required|min_length[3]|max_length[100]|is_unique[warehouses.warehouse_name,id,{id}]',
        'status'         => 'required|in_list[active,inactive,maintenance]',
    ];

    protected $validationMessages = [
        'warehouse_name' => [
            'required'   => 'Warehouse name is required',
            'min_length' => 'Warehouse name must be at least 3 characters long',
            'is_unique'  => 'Warehouse name already exists'
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getWarehousesForDropdown()
    {
        $warehouses = $this->where('status', 'active')->findAll();
        $dropdown = [];
        foreach ($warehouses as $warehouse) {
            $dropdown[$warehouse['id']] = $warehouse['warehouse_name'];
        }
        return $dropdown;
    }

    public function getActiveWarehouses()
    {
        return $this->where('status', 'active')->findAll();
    }
}

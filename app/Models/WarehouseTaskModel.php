<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseTaskModel extends Model
{
    protected $table = 'warehouse_tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'po_id',
        'assigned_staff_id',
        'warehouse_id',
        'status',
        'scheduled_at',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

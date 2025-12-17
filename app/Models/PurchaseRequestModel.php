<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseRequestModel extends Model
{
    protected $table = 'purchase_requests';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'pr_number',
        'requested_by',
        'warehouse_id',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}

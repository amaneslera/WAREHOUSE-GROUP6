<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseRequestItemModel extends Model
{
    protected $table = 'purchase_request_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'purchase_request_id',
        'inventory_item_id',
        'quantity',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

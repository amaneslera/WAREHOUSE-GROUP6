<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'po_number',
        'purchase_request_id',
        'vendor_id',
        'warehouse_id',
        'status',
        'po_approval_status',
        'po_approved_by',
        'po_approved_at',
        'po_approval_notes',
        'expected_delivery_date',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}

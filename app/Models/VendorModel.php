<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * VendorModel
 * 
 * Manages vendor/supplier information for Accounts Payable
 * 
 * @package App\Models
 */
class VendorModel extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'vendor_code',
        'vendor_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'payment_terms',
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'vendor_code' => 'required|is_unique[vendors.vendor_code,id,{id}]',
        'vendor_name' => 'required|min_length[3]|max_length[255]',
        'email'       => 'permit_empty|valid_email',
        'status'      => 'required|in_list[active,inactive,blocked]'
    ];

    protected $validationMessages = [
        'vendor_code' => [
            'required'  => 'Vendor code is required',
            'is_unique' => 'Vendor code already exists'
        ],
        'vendor_name' => [
            'required' => 'Vendor name is required'
        ]
    ];

    /**
     * Get active vendors for dropdown
     * 
     * @return array
     */
    public function getVendorsForDropdown()
    {
        $vendors = $this->where('status', 'active')->findAll();
        $dropdown = [];
        foreach ($vendors as $vendor) {
            $dropdown[$vendor['id']] = $vendor['vendor_name'];
        }
        return $dropdown;
    }

    /**
     * Get vendor with AP statistics
     * 
     * @param int $vendorId
     * @return array|null
     */
    public function getVendorWithStats($vendorId)
    {
        $vendor = $this->find($vendorId);
        if (!$vendor) {
            return null;
        }

        // Get AP stats
        $builder = $this->db->table('accounts_payable');
        $builder->select('
            COUNT(*) as total_invoices,
            SUM(invoice_amount) as total_amount,
            SUM(balance) as total_balance
        ');
        $builder->where('vendor_id', $vendorId);
        $stats = $builder->get()->getRowArray();

        $vendor['stats'] = $stats;
        return $vendor;
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AccountsPayableModel
 *
 * Manages accounts payable invoices
 *
 * @package App\Models
 */
class AccountsPayableModel extends Model
{
    protected $table = 'accounts_payable';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'invoice_number',
        'vendor_id',
        'invoice_date',
        'due_date',
        'invoice_amount',
        'paid_amount',
        'balance',
        'status',
        'description',
        'payment_method',
        'payment_reference',
        'warehouse_id',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'invoice_number' => 'required|is_unique[accounts_payable.invoice_number,id,{id}]',
        'vendor_id'      => 'required|is_not_unique[vendors.id]',
        'invoice_date'   => 'required|valid_date',
        'due_date'       => 'required|valid_date',
        'invoice_amount' => 'required|decimal',
        'status'         => 'required|in_list[pending,partial,paid,overdue,cancelled]'
    ];

    protected $validationMessages = [
        'invoice_number' => [
            'required'  => 'Invoice number is required',
            'is_unique' => 'Invoice number already exists'
        ],
        'vendor_id' => [
            'required' => 'Vendor is required'
        ]
    ];

    /**
     * Get pending invoices
     *
     * @return array
     */
    public function getPendingInvoices()
    {
        return $this->where('status', 'pending')->findAll();
    }

    /**
     * Get overdue invoices
     *
     * @return array
     */
    public function getOverdueInvoices()
    {
        return $this->where('status', 'overdue')->findAll();
    }

    /**
     * Get total amount due
     *
     * @return float
     */
    public function getTotalAmountDue()
    {
        $result = $this->selectSum('balance')->whereIn('status', ['pending', 'partial', 'overdue'])->get()->getRow();
        return $result->balance ?? 0;
    }

    /**
     * Get processed today count
     *
     * @return int
     */
    public function getProcessedTodayCount()
    {
        return $this->where('DATE(updated_at)', date('Y-m-d'))->countAllResults();
    }

    /**
     * Approve invoice
     *
     * @param int $id
     * @return bool
     */
    public function approveInvoice($id)
    {
        return $this->update($id, ['status' => 'approved']);
    }

    /**
     * Mark invoice as paid
     *
     * @param int $id
     * @return bool
     */
    public function markAsPaid($id)
    {
        $invoice = $this->find($id);
        if ($invoice) {
            return $this->update($id, [
                'status' => 'paid',
                'paid_amount' => $invoice['invoice_amount'],
                'balance' => 0
            ]);
        }
        return false;
    }

    /**
     * Get invoice with vendor details
     *
     * @param int $id
     * @return array|null
     */
    public function getInvoiceWithVendor($id)
    {
        return $this->select('accounts_payable.*, vendors.vendor_name, vendors.vendor_code')
                    ->join('vendors', 'vendors.id = accounts_payable.vendor_id')
                    ->find($id);
    }
}

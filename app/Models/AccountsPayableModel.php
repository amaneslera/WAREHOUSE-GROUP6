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
        'po_reference',
        'delivery_receipt',
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
        'stock_movement_ids',
        'matching_status',
        'discrepancy_notes',
        'matched_by',
        'matched_at',
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

    /**
     * Get invoices with vendor details
     *
     * @param array $filters
     * @return array
     */
    public function getInvoicesWithVendors($filters = [])
    {
        $builder = $this->select('accounts_payable.*, vendors.vendor_name, vendors.vendor_code')
                        ->join('vendors', 'vendors.id = accounts_payable.vendor_id');

        // Apply filters
        if (isset($filters['status'])) {
            $builder->where('accounts_payable.status', $filters['status']);
        }
        
        if (isset($filters['vendor_id'])) {
            $builder->where('accounts_payable.vendor_id', $filters['vendor_id']);
        }
        
        if (isset($filters['date_from'])) {
            $builder->where('accounts_payable.invoice_date >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('accounts_payable.invoice_date <=', $filters['date_to']);
        }

        return $builder->orderBy('accounts_payable.created_at', 'DESC')->findAll();
    }

    /**
     * Get partially paid invoices
     *
     * @return array
     */
    public function getPartiallyPaidInvoices()
    {
        return $this->where('status', 'partial')->findAll();
    }

    /**
     * Get AP statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_invoices' => $this->countAllResults(false),
            'total_amount' => $this->selectSum('invoice_amount')->first()['invoice_amount'] ?? 0,
            'total_paid' => $this->selectSum('paid_amount')->first()['paid_amount'] ?? 0,
            'total_outstanding' => $this->getTotalAmountDue(),
            'pending_count' => $this->where('status', 'pending')->countAllResults(false),
            'partial_count' => $this->where('status', 'partial')->countAllResults(false),
            'paid_count' => $this->where('status', 'paid')->countAllResults(false),
            'overdue_count' => $this->where('status', 'overdue')->countAllResults(false),
            'processed_today' => $this->getProcessedTodayCount()
        ];
    }

    /**
     * Get AP aging report (invoices grouped by age)
     *
     * @return array
     */
    public function getAgingReport()
    {
        $invoices = $this->whereIn('status', ['pending', 'partial', 'overdue'])->findAll();
        
        $aging = [
            'current' => ['count' => 0, 'amount' => 0],      // 0-30 days
            '30_days' => ['count' => 0, 'amount' => 0],      // 31-60 days
            '60_days' => ['count' => 0, 'amount' => 0],      // 61-90 days
            '90_plus' => ['count' => 0, 'amount' => 0]       // 90+ days
        ];

        foreach ($invoices as $invoice) {
            $daysOverdue = (time() - strtotime($invoice['due_date'])) / (60 * 60 * 24);
            
            if ($daysOverdue <= 30) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $invoice['balance'];
            } elseif ($daysOverdue <= 60) {
                $aging['30_days']['count']++;
                $aging['30_days']['amount'] += $invoice['balance'];
            } elseif ($daysOverdue <= 90) {
                $aging['60_days']['count']++;
                $aging['60_days']['amount'] += $invoice['balance'];
            } else {
                $aging['90_plus']['count']++;
                $aging['90_plus']['amount'] += $invoice['balance'];
            }
        }

        return $aging;
    }

    /**
     * Match invoice with documents
     *
     * @param int $id
     * @param array $matchData
     * @return bool
     */
    public function matchInvoice($id, $matchData)
    {
        $updateData = [
            'po_reference' => $matchData['po_reference'] ?? null,
            'delivery_receipt' => $matchData['delivery_receipt'] ?? null,
            'stock_movement_ids' => $matchData['stock_movement_ids'] ?? null,
            'matching_status' => $matchData['matching_status'] ?? 'matched',
            'discrepancy_notes' => $matchData['discrepancy_notes'] ?? null,
            'matched_by' => session('user_id'),
            'matched_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($id, $updateData);
    }

    /**
     * Flag discrepancy for invoice
     *
     * @param int $id
     * @param string $notes
     * @return bool
     */
    public function flagDiscrepancy($id, $notes)
    {
        return $this->update($id, [
            'matching_status' => 'discrepancy',
            'discrepancy_notes' => $notes,
            'matched_by' => session('user_id'),
            'matched_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get related stock movements for invoice
     *
     * @param int $id
     * @return array
     */
    public function getRelatedStockMovements($id)
    {
        $invoice = $this->find($id);
        if (!$invoice || empty($invoice['stock_movement_ids'])) {
            return [];
        }

        $movementIds = explode(',', $invoice['stock_movement_ids']);
        $stockMovementModel = new \App\Models\StockMovementModel();

        return $stockMovementModel->whereIn('id', $movementIds)->findAll();
    }

    /**
     * Get invoices with matching status
     *
     * @param string $status
     * @return array
     */
    public function getInvoicesByMatchingStatus($status)
    {
        return $this->where('matching_status', $status)->findAll();
    }

    /**
     * Get invoice with full matching details
     *
     * @param int $id
     * @return array|null
     */
    public function getInvoiceWithMatchingDetails($id)
    {
        $invoice = $this->select('accounts_payable.*, vendors.vendor_name, vendors.vendor_code, users.first_name as matched_by_name')
                        ->join('vendors', 'vendors.id = accounts_payable.vendor_id')
                        ->join('users', 'users.id = accounts_payable.matched_by', 'left')
                        ->find($id);

        if ($invoice && !empty($invoice['stock_movement_ids'])) {
            $invoice['related_movements'] = $this->getRelatedStockMovements($id);
        }

        return $invoice;
    }

    /**
     * Get matching statistics
     *
     * @return array
     */
    public function getMatchingStatistics()
    {
        return [
            'total_invoices' => $this->countAllResults(false),
            'matched_count' => $this->where('matching_status', 'matched')->countAllResults(false),
            'unmatched_count' => $this->where('matching_status', 'unmatched')->countAllResults(false),
            'discrepancy_count' => $this->where('matching_status', 'discrepancy')->countAllResults(false),
        ];
    }
}

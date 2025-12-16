<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AccountsReceivableModel
 * 
 * Manages AR invoices, client billing, and receivable tracking
 * 
 * @package App\Models
 */
class AccountsReceivableModel extends Model
{
    protected $table = 'accounts_receivable';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'invoice_number',
        'client_id',
        'invoice_date',
        'due_date',
        'invoice_amount',
        'received_amount',
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
        'invoice_number' => 'required|is_unique[accounts_receivable.invoice_number,id,{id}]',
        'client_id'      => 'required|integer|is_not_unique[clients.id]',
        'invoice_date'   => 'required|valid_date',
        'due_date'       => 'required|valid_date',
        'invoice_amount' => 'required|decimal|greater_than[0]',
        'status'         => 'required|in_list[pending,partial,paid,overdue,cancelled]'
    ];

    protected $validationMessages = [
        'invoice_number' => [
            'required'  => 'Invoice number is required',
            'is_unique' => 'Invoice number already exists'
        ],
        'client_id' => [
            'required' => 'Client is required',
            'is_not_unique' => 'Client does not exist'
        ],
        'invoice_amount' => [
            'required' => 'Invoice amount is required',
            'greater_than' => 'Invoice amount must be greater than zero'
        ]
    ];

    protected $beforeInsert = ['setInitialBalance'];
    protected $beforeUpdate = ['updateStatus'];

    /**
     * Set initial balance equal to invoice amount on creation
     * 
     * @param array $data
     * @return array
     */
    protected function setInitialBalance(array $data)
    {
        if (isset($data['data']['invoice_amount'])) {
            $data['data']['balance'] = $data['data']['invoice_amount'];
            $data['data']['received_amount'] = 0;
        }
        return $data;
    }

    /**
     * Automatically update status based on balance and due date
     * 
     * @param array $data
     * @return array
     */
    protected function updateStatus(array $data)
    {
        if (isset($data['id'])) {
            $invoice = $this->find($data['id'][0]);
            
            if ($invoice && isset($data['data']['balance'])) {
                $balance = $data['data']['balance'];
                $dueDate = $invoice['due_date'];
                
                // Determine status
                if ($balance <= 0) {
                    $data['data']['status'] = 'paid';
                } elseif ($balance < $invoice['invoice_amount']) {
                    $data['data']['status'] = 'partial';
                } elseif (strtotime($dueDate) < time() && $balance > 0) {
                    $data['data']['status'] = 'overdue';
                } else {
                    $data['data']['status'] = 'pending';
                }
            }
        }
        return $data;
    }

    /**
     * Get pending invoices
     * 
     * @return array
     */
    public function getPendingInvoices()
    {
        return $this->whereIn('status', ['pending', 'partial'])->findAll();
    }

    /**
     * Get overdue invoices
     * 
     * @return array
     */
    public function getOverdueInvoices()
    {
        return $this->where('status', 'overdue')
                    ->orWhere('due_date <', date('Y-m-d'))
                    ->where('balance >', 0)
                    ->findAll();
    }

    /**
     * Get total outstanding balance
     * 
     * @return float
     */
    public function getTotalOutstanding()
    {
        $result = $this->selectSum('balance')
                       ->whereIn('status', ['pending', 'partial', 'overdue'])
                       ->first();
        
        return $result['balance'] ?? 0;
    }

    /**
     * Get count of invoices processed today
     * 
     * @return int
     */
    public function getProcessedTodayCount()
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
    }

    /**
     * Update invoice balance after payment
     * 
     * @param int $id Invoice ID
     * @param float $paymentAmount
     * @return bool
     */
    public function recordPaymentAmount($id, $paymentAmount)
    {
        $invoice = $this->find($id);
        
        if (!$invoice) {
            return false;
        }

        $newReceivedAmount = $invoice['received_amount'] + $paymentAmount;
        $newBalance = $invoice['invoice_amount'] - $newReceivedAmount;

        // Determine new status
        $newStatus = 'pending';
        if ($newBalance <= 0) {
            $newStatus = 'paid';
            $newBalance = 0;
        } elseif ($newBalance < $invoice['invoice_amount']) {
            $newStatus = 'partial';
        } elseif (strtotime($invoice['due_date']) < time()) {
            $newStatus = 'overdue';
        }

        return $this->update($id, [
            'received_amount' => $newReceivedAmount,
            'balance' => $newBalance,
            'status' => $newStatus
        ]);
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
        
        if (!$invoice) {
            return false;
        }

        return $this->update($id, [
            'received_amount' => $invoice['invoice_amount'],
            'balance' => 0,
            'status' => 'paid'
        ]);
    }

    /**
     * Cancel invoice
     * 
     * @param int $id
     * @return bool
     */
    public function cancelInvoice($id)
    {
        return $this->update($id, ['status' => 'cancelled']);
    }

    /**
     * Get invoice with client details
     * 
     * @param int $id
     * @return array|null
     */
    public function getInvoiceWithClient($id)
    {
        return $this->select('accounts_receivable.*, clients.client_name, clients.client_code, clients.email, clients.phone')
                    ->join('clients', 'clients.id = accounts_receivable.client_id')
                    ->where('accounts_receivable.id', $id)
                    ->first();
    }

    /**
     * Get invoices with client details
     * 
     * @param array $filters
     * @return array
     */
    public function getInvoicesWithClients($filters = [])
    {
        $builder = $this->select('accounts_receivable.*, clients.client_name, clients.client_code')
                        ->join('clients', 'clients.id = accounts_receivable.client_id');

        // Apply filters
        if (isset($filters['status'])) {
            $builder->where('accounts_receivable.status', $filters['status']);
        }
        
        if (isset($filters['client_id'])) {
            $builder->where('accounts_receivable.client_id', $filters['client_id']);
        }
        
        if (isset($filters['date_from'])) {
            $builder->where('accounts_receivable.invoice_date >=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $builder->where('accounts_receivable.invoice_date <=', $filters['date_to']);
        }

        return $builder->orderBy('accounts_receivable.created_at', 'DESC')->findAll();
    }

    /**
     * Get AR statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_invoices' => $this->countAllResults(false),
            'total_amount' => $this->selectSum('invoice_amount')->first()['invoice_amount'] ?? 0,
            'total_received' => $this->selectSum('received_amount')->first()['received_amount'] ?? 0,
            'total_outstanding' => $this->getTotalOutstanding(),
            'pending_count' => $this->where('status', 'pending')->countAllResults(false),
            'partial_count' => $this->where('status', 'partial')->countAllResults(false),
            'paid_count' => $this->where('status', 'paid')->countAllResults(false),
            'overdue_count' => $this->where('status', 'overdue')->countAllResults(false),
            'processed_today' => $this->getProcessedTodayCount()
        ];
    }

    /**
     * Get aging report (invoices grouped by age)
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
}

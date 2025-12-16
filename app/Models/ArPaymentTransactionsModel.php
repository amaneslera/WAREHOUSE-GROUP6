<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ArPaymentTransactionsModel
 * 
 * Manages AR payment transactions and receipts
 * 
 * @package App\Models
 */
class ArPaymentTransactionsModel extends Model
{
    protected $table = 'ar_payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'ar_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'processed_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;

    protected $validationRules = [
        'ar_id'          => 'required|integer|is_not_unique[accounts_receivable.id]',
        'payment_date'   => 'required|valid_date',
        'amount'         => 'required|decimal|greater_than[0]',
        'payment_method' => 'required|in_list[bank_transfer,cash,check,credit_card,debit_card,online]',
        'processed_by'   => 'required|integer'
    ];

    protected $validationMessages = [
        'ar_id' => [
            'required' => 'AR invoice is required',
            'is_not_unique' => 'AR invoice does not exist'
        ],
        'amount' => [
            'required' => 'Payment amount is required',
            'greater_than' => 'Payment amount must be greater than zero'
        ],
        'payment_method' => [
            'required' => 'Payment method is required',
            'in_list' => 'Invalid payment method'
        ]
    ];

    /**
     * Record a payment and update AR invoice balance
     * 
     * @param array $data Payment data
     * @return int|bool Payment transaction ID or false
     */
    public function recordPayment($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Insert payment transaction
            if (!$this->insert($data)) {
                $db->transRollback();
                return false;
            }

            $paymentId = $this->getInsertID();

            // Update AR invoice balance
            $arModel = new \App\Models\AccountsReceivableModel();
            if (!$arModel->recordPaymentAmount($data['ar_id'], $data['amount'])) {
                $db->transRollback();
                return false;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return false;
            }

            return $paymentId;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Payment recording failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payments for specific AR invoice
     * 
     * @param int $arId
     * @return array
     */
    public function getPaymentsForInvoice($arId)
    {
        return $this->where('ar_id', $arId)
                    ->orderBy('payment_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get total payments received today
     * 
     * @return float
     */
    public function getTotalPaymentsToday()
    {
        $result = $this->selectSum('amount')
                       ->where('DATE(payment_date)', date('Y-m-d'))
                       ->first();
        
        return $result['amount'] ?? 0;
    }

    /**
     * Get total payments received this month
     * 
     * @return float
     */
    public function getTotalPaymentsThisMonth()
    {
        $result = $this->selectSum('amount')
                       ->where('YEAR(payment_date)', date('Y'))
                       ->where('MONTH(payment_date)', date('m'))
                       ->first();
        
        return $result['amount'] ?? 0;
    }

    /**
     * Get count of pending invoices (awaiting payment)
     * 
     * @return int
     */
    public function getPendingPaymentsCount()
    {
        $arModel = new \App\Models\AccountsReceivableModel();
        return $arModel->whereIn('status', ['pending', 'partial', 'overdue'])
                       ->countAllResults();
    }

    /**
     * Get payments with invoice and client details
     * 
     * @param array $filters
     * @return array
     */
    public function getPaymentsWithDetails($filters = [])
    {
        $builder = $this->select('ar_payment_transactions.*, accounts_receivable.invoice_number, clients.client_name, users.first_name, users.last_name')
                        ->join('accounts_receivable', 'accounts_receivable.id = ar_payment_transactions.ar_id')
                        ->join('clients', 'clients.id = accounts_receivable.client_id')
                        ->join('users', 'users.id = ar_payment_transactions.processed_by');

        // Apply filters
        if (isset($filters['ar_id'])) {
            $builder->where('ar_payment_transactions.ar_id', $filters['ar_id']);
        }

        if (isset($filters['client_id'])) {
            $builder->where('accounts_receivable.client_id', $filters['client_id']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('ar_payment_transactions.payment_date >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('ar_payment_transactions.payment_date <=', $filters['date_to']);
        }

        if (isset($filters['payment_method'])) {
            $builder->where('ar_payment_transactions.payment_method', $filters['payment_method']);
        }

        return $builder->orderBy('ar_payment_transactions.payment_date', 'DESC')->findAll();
    }

    /**
     * Get payment statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_payments' => $this->countAllResults(false),
            'total_amount' => $this->selectSum('amount')->first()['amount'] ?? 0,
            'payments_today' => $this->where('DATE(payment_date)', date('Y-m-d'))->countAllResults(false),
            'amount_today' => $this->getTotalPaymentsToday(),
            'amount_this_month' => $this->getTotalPaymentsThisMonth(),
            'pending_invoices' => $this->getPendingPaymentsCount()
        ];
    }

    /**
     * Get payment methods distribution
     * 
     * @return array
     */
    public function getPaymentMethodsDistribution()
    {
        return $this->select('payment_method, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('payment_method')
                    ->findAll();
    }

    /**
     * Get recent payments
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentPayments($limit = 10)
    {
        return $this->select('ar_payment_transactions.*, accounts_receivable.invoice_number, clients.client_name')
                    ->join('accounts_receivable', 'accounts_receivable.id = ar_payment_transactions.ar_id')
                    ->join('clients', 'clients.id = accounts_receivable.client_id')
                    ->orderBy('ar_payment_transactions.created_at', 'DESC')
                    ->limit($limit)
                    ->find();
    }
}

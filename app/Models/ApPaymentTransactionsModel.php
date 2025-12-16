<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ApPaymentTransactionsModel
 *
 * Manages AP payment transactions
 *
 * @package App\Models
 */
class ApPaymentTransactionsModel extends Model
{
    protected $table = 'ap_payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'ap_id',
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
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'ap_id'          => 'required|is_not_unique[accounts_payable.id]',
        'payment_date'   => 'required|valid_date',
        'amount'         => 'required|decimal',
        'payment_method' => 'required',
        'processed_by'   => 'required|is_not_unique[users.id]'
    ];

    /**
     * Record payment and update invoice
     *
     * @param array $data
     * @return bool
     */
    public function recordPayment($data)
    {
        $this->db->transStart();

        // Insert payment transaction
        $this->insert($data);

        // Update invoice
        $apModel = new AccountsPayableModel();
        $invoice = $apModel->find($data['ap_id']);

        if ($invoice) {
            $newPaidAmount = $invoice['paid_amount'] + $data['amount'];
            $newBalance = $invoice['invoice_amount'] - $newPaidAmount;

            $status = 'partial';
            if ($newBalance <= 0) {
                $status = 'paid';
                $newBalance = 0;
            }

            $apModel->update($data['ap_id'], [
                'paid_amount' => $newPaidAmount,
                'balance' => $newBalance,
                'status' => $status,
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['reference_number']
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Get payments for invoice
     *
     * @param int $apId
     * @return array
     */
    public function getPaymentsForInvoice($apId)
    {
        return $this->where('ap_id', $apId)->findAll();
    }

    /**
     * Get total payments today
     *
     * @return float
     */
    public function getTotalPaymentsToday()
    {
        $result = $this->selectSum('amount')
                       ->where('DATE(payment_date)', date('Y-m-d'))
                       ->get()->getRow();
        return $result->amount ?? 0;
    }

    /**
     * Get total payments this month
     *
     * @return float
     */
    public function getTotalPaymentsThisMonth()
    {
        $result = $this->selectSum('amount')
                       ->where('MONTH(payment_date)', date('m'))
                       ->where('YEAR(payment_date)', date('Y'))
                       ->get()->getRow();
        return $result->amount ?? 0;
    }

    /**
     * Get pending payments count
     *
     * @return int
     */
    public function getPendingPaymentsCount()
    {
        $apModel = new AccountsPayableModel();
        return $apModel->whereIn('status', ['pending', 'partial'])->countAllResults();
    }
}

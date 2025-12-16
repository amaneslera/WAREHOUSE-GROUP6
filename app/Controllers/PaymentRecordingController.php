<?php

namespace App\Controllers;

use App\Models\AccountsPayableModel;
use App\Models\ApPaymentTransactionsModel;
use App\Models\VendorModel;

/**
 * PaymentRecordingController
 *
 * Handles payment recording for Accounts Payable Clerk
 */
class PaymentRecordingController extends BaseController
{
    protected $apModel;
    protected $paymentModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->apModel = new AccountsPayableModel();
        $this->paymentModel = new ApPaymentTransactionsModel();
        $this->vendorModel = new VendorModel();
    }

    /**
     * Display payment recording page
     */
    public function index()
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $data = [
            'payments' => $this->getPaymentsWithDetails(),
            'total_today' => $this->paymentModel->getTotalPaymentsToday(),
            'total_month' => $this->paymentModel->getTotalPaymentsThisMonth(),
            'pending_count' => $this->paymentModel->getPendingPaymentsCount(),
            'title' => 'Payment Recording'
        ];

        return view('accounts_payable/payment_recording', $data);
    }

    /**
     * Show payment form
     */
    public function create()
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $data = [
            'invoices' => $this->getPendingInvoices(),
            'title' => 'Record Payment'
        ];

        return view('accounts_payable/payment_form', $data);
    }

    /**
     * Store payment
     */
    public function store()
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $rules = [
            'ap_id' => 'required|is_not_unique[accounts_payable.id]',
            'payment_date' => 'required|valid_date',
            'amount' => 'required|decimal',
            'payment_method' => 'required',
            'reference_number' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ap_id' => $this->request->getPost('ap_id'),
            'payment_date' => $this->request->getPost('payment_date'),
            'amount' => $this->request->getPost('amount'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference_number' => $this->request->getPost('reference_number'),
            'notes' => $this->request->getPost('notes'),
            'processed_by' => session('user_id')
        ];

        if ($this->paymentModel->recordPayment($data)) {
            session()->setFlashdata('success', 'Payment recorded successfully');
            return redirect()->to('/payment-recording');
        } else {
            session()->setFlashdata('error', 'Failed to record payment');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Get payments with invoice and vendor details
     */
    private function getPaymentsWithDetails()
    {
        return $this->paymentModel->select('ap_payment_transactions.*, accounts_payable.invoice_number, vendors.vendor_name')
                                  ->join('accounts_payable', 'accounts_payable.id = ap_payment_transactions.ap_id')
                                  ->join('vendors', 'vendors.id = accounts_payable.vendor_id')
                                  ->orderBy('ap_payment_transactions.payment_date', 'DESC')
                                  ->findAll();
    }

    /**
     * Get pending invoices for payment
     */
    private function getPendingInvoices()
    {
        return $this->apModel->select('accounts_payable.id, accounts_payable.invoice_number, accounts_payable.balance, vendors.vendor_name')
                             ->join('vendors', 'vendors.id = accounts_payable.vendor_id')
                             ->whereIn('accounts_payable.status', ['pending', 'partial'])
                             ->findAll();
    }
}

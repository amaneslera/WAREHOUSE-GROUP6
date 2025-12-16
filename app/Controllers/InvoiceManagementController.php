<?php

namespace App\Controllers;

use App\Models\AccountsPayableModel;
use App\Models\VendorModel;

/**
 * InvoiceManagementController
 *
 * Handles invoice management for Accounts Payable Clerk
 */
class InvoiceManagementController extends BaseController
{
    protected $apModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->apModel = new AccountsPayableModel();
        $this->vendorModel = new VendorModel();
    }

    /**
     * Display invoice management page
     */
    public function index()
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $data = [
            'invoices' => $this->getInvoicesWithVendors(),
            'title' => 'Invoice Management'
        ];

        return view('accounts_payable/invoice_management', $data);
    }

    /**
     * Approve invoice
     */
    public function approve($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        if ($this->apModel->approveInvoice($id)) {
            session()->setFlashdata('success', 'Invoice approved successfully');
        } else {
            session()->setFlashdata('error', 'Failed to approve invoice');
        }

        return redirect()->to('/invoice-management');
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        if ($this->apModel->markAsPaid($id)) {
            session()->setFlashdata('success', 'Invoice marked as paid');
        } else {
            session()->setFlashdata('error', 'Failed to mark invoice as paid');
        }

        return redirect()->to('/invoice-management');
    }

    /**
     * View invoice details
     */
    public function view($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $invoice = $this->apModel->getInvoiceWithVendor($id);

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'invoice' => $invoice,
            'title' => 'Invoice Details'
        ];

        return view('accounts_payable/invoice_view', $data);
    }

    /**
     * Get invoices with vendor details
     */
    private function getInvoicesWithVendors()
    {
        return $this->apModel->select('accounts_payable.*, vendors.vendor_name, vendors.vendor_code')
                             ->join('vendors', 'vendors.id = accounts_payable.vendor_id')
                             ->findAll();
    }
}

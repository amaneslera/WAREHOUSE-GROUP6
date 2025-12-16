<?php

namespace App\Controllers;

use App\Models\AccountsPayableModel;
use App\Models\VendorModel;
use App\Models\AuditLogModel;

/**
 * InvoiceManagementController
 *
 * Handles invoice management for Accounts Payable Clerk
 */
class InvoiceManagementController extends BaseController
{
    protected $apModel;
    protected $vendorModel;
    protected $auditModel;

    public function __construct()
    {
        $this->apModel = new AccountsPayableModel();
        $this->vendorModel = new VendorModel();
        $this->auditModel = new AuditLogModel();
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

        $oldInvoice = $this->apModel->find($id);

        if ($this->apModel->approveInvoice($id)) {
            // Log the action
            $this->auditModel->logAction(
                'approve',
                'accounts_payable',
                $id,
                ['status' => $oldInvoice['status'] ?? null],
                ['status' => 'approved'],
                "Approved invoice #{$oldInvoice['invoice_number']}"
            );

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

        $oldInvoice = $this->apModel->find($id);

        if ($this->apModel->markAsPaid($id)) {
            // Log the action
            $this->auditModel->logAction(
                'mark_paid',
                'accounts_payable',
                $id,
                ['status' => $oldInvoice['status'] ?? null, 'paid_amount' => $oldInvoice['paid_amount'] ?? 0, 'balance' => $oldInvoice['balance'] ?? 0],
                ['status' => 'paid', 'paid_amount' => $oldInvoice['invoice_amount'], 'balance' => 0],
                "Marked invoice #{$oldInvoice['invoice_number']} as paid"
            );

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

        $invoice = $this->apModel->getInvoiceWithMatchingDetails($id);

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
     * Match invoice with documents
     */
    public function match($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $invoice = $this->apModel->getInvoiceWithMatchingDetails($id);

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get potential stock movements for this vendor and warehouse
        $stockMovementModel = new \App\Models\StockMovementModel();
        $potentialMovements = $stockMovementModel->select('stock_movements.*, inventory_items.item_name, warehouses.warehouse_name')
                                                 ->join('inventory_items', 'inventory_items.id = stock_movements.item_id')
                                                 ->join('warehouses', 'warehouses.id = stock_movements.warehouse_id')
                                                 ->where('stock_movements.movement_type', 'IN')
                                                 ->where('stock_movements.vendor_id', $invoice['vendor_id'])
                                                 ->orderBy('stock_movements.created_at', 'DESC')
                                                 ->findAll();

        $data = [
            'invoice' => $invoice,
            'potential_movements' => $potentialMovements,
            'title' => 'Match Invoice Documents'
        ];

        return view('accounts_payable/invoice_match', $data);
    }

    /**
     * Store invoice matching
     */
    public function storeMatch($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $rules = [
            'po_reference' => 'permit_empty|max_length[100]',
            'delivery_receipt' => 'permit_empty|max_length[100]',
            'stock_movement_ids' => 'permit_empty',
            'discrepancy_notes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldInvoice = $this->apModel->find($id);

        $matchData = [
            'po_reference' => $this->request->getPost('po_reference'),
            'delivery_receipt' => $this->request->getPost('delivery_receipt'),
            'stock_movement_ids' => $this->request->getPost('stock_movement_ids'),
            'matching_status' => $this->request->getPost('has_discrepancy') ? 'discrepancy' : 'matched',
            'discrepancy_notes' => $this->request->getPost('discrepancy_notes')
        ];

        if ($this->apModel->matchInvoice($id, $matchData)) {
            // Log the action
            $this->auditModel->logAction(
                'match_invoice',
                'accounts_payable',
                $id,
                [
                    'matching_status' => $oldInvoice['matching_status'] ?? null,
                    'po_reference' => $oldInvoice['po_reference'] ?? null,
                    'delivery_receipt' => $oldInvoice['delivery_receipt'] ?? null
                ],
                $matchData,
                "Matched invoice #{$oldInvoice['invoice_number']} with documents"
            );

            $message = $matchData['matching_status'] === 'discrepancy' ?
                      'Invoice matched with discrepancies flagged' :
                      'Invoice successfully matched with documents';
            session()->setFlashdata('success', $message);
        } else {
            session()->setFlashdata('error', 'Failed to match invoice');
        }

        return redirect()->to('/invoice-management/view/' . $id);
    }

    /**
     * Flag discrepancy for invoice
     */
    public function flagDiscrepancy($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $rules = [
            'discrepancy_notes' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldInvoice = $this->apModel->find($id);
        $notes = $this->request->getPost('discrepancy_notes');

        if ($this->apModel->flagDiscrepancy($id, $notes)) {
            // Log the action
            $this->auditModel->logAction(
                'flag_discrepancy',
                'accounts_payable',
                $id,
                ['matching_status' => $oldInvoice['matching_status'] ?? null, 'discrepancy_notes' => $oldInvoice['discrepancy_notes'] ?? null],
                ['matching_status' => 'discrepancy', 'discrepancy_notes' => $notes],
                "Flagged discrepancy for invoice #{$oldInvoice['invoice_number']}"
            );

            session()->setFlashdata('success', 'Discrepancy flagged successfully');
        } else {
            session()->setFlashdata('error', 'Failed to flag discrepancy');
        }

        return redirect()->to('/invoice-management/view/' . $id);
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

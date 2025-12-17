<?php

namespace App\Controllers;

use App\Models\VendorModel;

/**
 * SupplierManagementController
 *
 * Handles supplier management for Accounts Payable Clerk
 */
class SupplierManagementController extends BaseController
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    /**
     * Display supplier management page
     */
    public function index()
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $data = [
            'suppliers' => $this->getSuppliersWithStats(),
            'active_count' => $this->vendorModel->where('status', 'active')->countAllResults(),
            'total_outstanding' => $this->getTotalOutstanding(),
            'avg_payment_terms' => $this->getAveragePaymentTerms(),
            'title' => 'Supplier Management'
        ];

        return view('accounts_payable/supplier_management', $data);
    }

    /**
     * Show edit supplier form
     */
    public function edit($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $supplier = $this->vendorModel->find($id);

        if (!$supplier) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'supplier' => $supplier,
            'title' => 'Edit Supplier'
        ];

        return view('accounts_payable/supplier_edit', $data);
    }

    /**
     * Update supplier
     */
    public function update($id)
    {
        if (session('user_role') !== 'accounts_payable_clerk') {
            return redirect()->to('/login');
        }

        $rules = [
            'vendor_code' => 'required|is_unique[vendors.vendor_code,id,' . $id . ']',
            'vendor_name' => 'required|min_length[3]|max_length[255]|is_unique[vendors.vendor_name,id,' . $id . ']',
            'email' => 'permit_empty|valid_email',
            'status' => 'required|in_list[active,inactive,blocked]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'vendor_code' => $this->request->getPost('vendor_code'),
            'vendor_name' => $this->request->getPost('vendor_name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'tax_id' => $this->request->getPost('tax_id'),
            'payment_terms' => $this->request->getPost('payment_terms'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->vendorModel->update($id, $data)) {
            session()->setFlashdata('success', 'Supplier updated successfully');
            return redirect()->to('/supplier-management');
        } else {
            session()->setFlashdata('error', 'Failed to update supplier');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Get suppliers with AP stats
     */
    private function getSuppliersWithStats()
    {
        $suppliers = $this->vendorModel->findAll();

        foreach ($suppliers as &$supplier) {
            $stats = $this->vendorModel->getVendorWithStats($supplier['id']);
            $supplier['stats'] = $stats['stats'] ?? ['total_invoices' => 0, 'total_amount' => 0, 'total_balance' => 0];
        }

        return $suppliers;
    }

    /**
     * Get total outstanding balance
     */
    private function getTotalOutstanding()
    {
        $builder = $this->vendorModel->db->table('accounts_payable');
        $result = $builder->selectSum('balance')->get()->getRow();
        return $result->balance ?? 0;
    }

    /**
     * Get average payment terms (simplified)
     */
    private function getAveragePaymentTerms()
    {
        $suppliers = $this->vendorModel->where('payment_terms IS NOT NULL')->findAll();
        if (empty($suppliers)) {
            return 'N/A';
        }

        $terms = array_column($suppliers, 'payment_terms');
        return implode(', ', array_unique($terms));
    }
}

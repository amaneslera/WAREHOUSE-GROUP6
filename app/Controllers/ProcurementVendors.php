<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\VendorModel;

class ProcurementVendors extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function requireProcurement()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        $role = session('user_role');
        if ($role !== 'procurement_officer' && $role !== 'PROCUREMENT_OFFICER') {
            return redirect()->to('/login');
        }

        return null;
    }

    private function generateVendorCode(): string
    {
        $like = 'VND-%';

        $row = $this->db->table('vendors')
            ->selectMax('vendor_code')
            ->like('vendor_code', $like, 'after')
            ->get()
            ->getRowArray();

        $max = $row['vendor_code'] ?? null;
        $next = 1;
        if ($max) {
            $parts = explode('-', $max);
            $last = end($parts);
            if (is_numeric($last)) {
                $next = ((int) $last) + 1;
            }
        }

        return 'VND-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $vendorModel = new VendorModel();

        return view('dashboard/procurement/vendors_list', [
            'title' => 'Vendors',
            'active' => 'vendors',
            'vendors' => $vendorModel->orderBy('vendor_name', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        return view('dashboard/procurement/vendors_create', [
            'title' => 'Create Vendor',
            'active' => 'vendors',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $rules = [
            'vendor_name' => 'required|min_length[3]|max_length[255]|is_unique[vendors.vendor_name]',
            'contact_person' => 'permit_empty|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[150]',
            'phone' => 'permit_empty|max_length[50]',
            'address' => 'permit_empty',
            'payment_terms' => 'permit_empty|max_length[100]',
            'status' => 'required|in_list[active,inactive,blocked]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $vendorModel = new VendorModel();
        $auditModel = new AuditLogModel();

        $data = [
            'vendor_code' => $this->generateVendorCode(),
            'vendor_name' => $this->request->getPost('vendor_name'),
            'contact_person' => $this->request->getPost('contact_person') ?: null,
            'email' => $this->request->getPost('email') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
            'payment_terms' => $this->request->getPost('payment_terms') ?: null,
            'status' => $this->request->getPost('status'),
        ];

        if ($this->db->fieldExists('created_by', 'vendors')) {
            $data['created_by'] = (int) session('user_id');
        }

        $vendorId = $vendorModel->insert($data, true);
        if (! $vendorId) {
            return redirect()->back()->withInput()->with('error', 'Failed to create vendor.');
        }

        $auditModel->logAction(
            'vendor_create',
            'procurement',
            (int) $vendorId,
            null,
            [
                'vendor_code' => $data['vendor_code'],
                'vendor_name' => $data['vendor_name'],
                'status' => $data['status'],
            ],
            'Created vendor ' . $data['vendor_name']
        );

        return redirect()->to('/procurement/vendors')->with('success', 'Vendor created successfully.');
    }

    public function edit($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find((int) $id);
        if (! $vendor) {
            return redirect()->to('/procurement/vendors')->with('error', 'Vendor not found.');
        }

        return view('dashboard/procurement/vendors_edit', [
            'title' => 'Edit Vendor',
            'active' => 'vendors',
            'vendor' => $vendor,
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $vendorModel = new VendorModel();
        $auditModel = new AuditLogModel();

        $vendor = $vendorModel->find((int) $id);
        if (! $vendor) {
            return redirect()->to('/procurement/vendors')->with('error', 'Vendor not found.');
        }

        $rules = [
            'vendor_name' => 'required|min_length[3]|max_length[255]|is_unique[vendors.vendor_name,id,' . ((int) $id) . ']',
            'contact_person' => 'permit_empty|max_length[150]',
            'email' => 'permit_empty|valid_email|max_length[150]',
            'phone' => 'permit_empty|max_length[50]',
            'address' => 'permit_empty',
            'payment_terms' => 'permit_empty|max_length[100]',
            'status' => 'required|in_list[active,inactive,blocked]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newData = [
            'vendor_name' => $this->request->getPost('vendor_name'),
            'contact_person' => $this->request->getPost('contact_person') ?: null,
            'email' => $this->request->getPost('email') ?: null,
            'phone' => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
            'payment_terms' => $this->request->getPost('payment_terms') ?: null,
            'status' => $this->request->getPost('status'),
        ];

        $vendorModel->update((int) $id, $newData);

        $action = 'vendor_update';
        if (($vendor['status'] ?? null) !== ($newData['status'] ?? null) && in_array($newData['status'], ['inactive', 'blocked'], true)) {
            $action = 'vendor_deactivate';
        }

        $auditModel->logAction(
            $action,
            'procurement',
            (int) $id,
            [
                'vendor_name' => $vendor['vendor_name'] ?? null,
                'status' => $vendor['status'] ?? null,
            ],
            [
                'vendor_name' => $newData['vendor_name'],
                'status' => $newData['status'],
            ],
            'Updated vendor ' . ($vendor['vendor_name'] ?? '')
        );

        return redirect()->to('/procurement/vendors')->with('success', 'Vendor updated successfully.');
    }

    public function updateStatus($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $vendorModel = new VendorModel();
        $auditModel = new AuditLogModel();

        $vendor = $vendorModel->find((int) $id);
        if (! $vendor) {
            return redirect()->to('/procurement/vendors')->with('error', 'Vendor not found.');
        }

        $status = $this->request->getPost('status');
        if (! in_array($status, ['active', 'inactive', 'blocked'], true)) {
            return redirect()->to('/procurement/vendors')->with('error', 'Invalid status.');
        }

        $vendorModel->update((int) $id, ['status' => $status]);

        $action = 'vendor_update';
        if (($vendor['status'] ?? null) !== $status && in_array($status, ['inactive', 'blocked'], true)) {
            $action = 'vendor_deactivate';
        }

        $auditModel->logAction(
            $action,
            'procurement',
            (int) $id,
            ['status' => $vendor['status'] ?? null],
            ['status' => $status],
            'Changed vendor status for ' . ($vendor['vendor_name'] ?? '')
        );

        return redirect()->to('/procurement/vendors')->with('success', 'Vendor status updated.');
    }
}

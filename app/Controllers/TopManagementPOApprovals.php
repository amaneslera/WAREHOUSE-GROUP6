<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PurchaseOrderModel;

class TopManagementPOApprovals extends BaseController
{
    private function requireTopManagement()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'top_management') {
            return redirect()->to('/login');
        }

        return null;
    }

    public function purchaseOrders()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $pos = $db->table('purchase_orders po')
            ->select('po.*, pr.pr_number, v.vendor_name, w.warehouse_name, u.first_name, u.last_name')
            ->join('purchase_requests pr', 'pr.id = po.purchase_request_id', 'left')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->join('users u', 'u.id = po.created_by', 'left')
            ->where('po.po_approval_status', 'pending')
            ->orderBy('po.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $poIds = array_values(array_filter(array_map(static fn ($r) => (int) ($r['id'] ?? 0), $pos)));
        $itemsByPo = [];
        if ($poIds !== []) {
            $rows = $db->table('purchase_order_items poi')
                ->select('poi.purchase_order_id, poi.quantity, ii.item_name, ii.unit_of_measure')
                ->join('inventory_items ii', 'ii.id = poi.inventory_item_id', 'left')
                ->whereIn('poi.purchase_order_id', $poIds)
                ->orderBy('ii.item_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $rid = (int) ($row['purchase_order_id'] ?? 0);
                if (! isset($itemsByPo[$rid])) {
                    $itemsByPo[$rid] = [];
                }
                $itemsByPo[$rid][] = $row;
            }
        }

        return view('dashboard/top_management/po_approvals', [
            'title' => 'PO Approvals',
            'pos' => $pos,
            'itemsByPo' => $itemsByPo,
        ]);
    }

    public function approvePO($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $poModel = new PurchaseOrderModel();
        $auditModel = new AuditLogModel();

        $po = $poModel->find($id);
        if (! $po) {
            return redirect()->to('/top-management/po-approvals')->with('error', 'PO not found.');
        }

        if (($po['po_approval_status'] ?? 'pending') !== 'pending') {
            return redirect()->to('/top-management/po-approvals')->with('error', 'Only pending POs can be approved.');
        }

        if ((int) ($po['created_by'] ?? 0) === (int) session('user_id')) {
            return redirect()->to('/top-management/po-approvals')->with('error', 'You cannot approve your own PO.');
        }

        $poModel->update($id, [
            'po_approval_status' => 'approved',
            'po_approved_by' => (int) session('user_id'),
            'po_approved_at' => date('Y-m-d H:i:s'),
            'po_approval_notes' => $this->request->getPost('po_approval_notes') ?: null,
        ]);

        $auditModel->logAction('po_approve', 'top_management', (int) $id, ['po_approval_status' => 'pending'], ['po_approval_status' => 'approved'], 'Approved PO ' . ($po['po_number'] ?? ''));

        return redirect()->to('/top-management/po-approvals')->with('success', 'PO approved.');
    }

    public function rejectPO($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $poModel = new PurchaseOrderModel();
        $auditModel = new AuditLogModel();

        $po = $poModel->find($id);
        if (! $po) {
            return redirect()->to('/top-management/po-approvals')->with('error', 'PO not found.');
        }

        if (($po['po_approval_status'] ?? 'pending') !== 'pending') {
            return redirect()->to('/top-management/po-approvals')->with('error', 'Only pending POs can be rejected.');
        }

        if ((int) ($po['created_by'] ?? 0) === (int) session('user_id')) {
            return redirect()->to('/top-management/po-approvals')->with('error', 'You cannot reject your own PO.');
        }

        $poModel->update($id, [
            'po_approval_status' => 'rejected',
            'po_approved_by' => (int) session('user_id'),
            'po_approved_at' => date('Y-m-d H:i:s'),
            'po_approval_notes' => $this->request->getPost('po_approval_notes') ?: null,
        ]);

        $auditModel->logAction('po_reject', 'top_management', (int) $id, ['po_approval_status' => 'pending'], ['po_approval_status' => 'rejected'], 'Rejected PO ' . ($po['po_number'] ?? ''));

        return redirect()->to('/top-management/po-approvals')->with('success', 'PO rejected.');
    }
}

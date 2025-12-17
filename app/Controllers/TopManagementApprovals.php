<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PurchaseRequestModel;

class TopManagementApprovals extends BaseController
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

    public function purchaseRequests()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $db = \Config\Database::connect();
        $prs = $db->table('purchase_requests pr')
            ->select('pr.*, u.first_name, u.last_name, w.warehouse_name')
            ->join('users u', 'u.id = pr.requested_by', 'left')
            ->join('warehouses w', 'w.id = pr.warehouse_id', 'left')
            ->whereIn('pr.status', ['submitted'])
            ->orderBy('pr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $prIds = array_values(array_filter(array_map(static fn ($r) => (int) ($r['id'] ?? 0), $prs)));
        $itemsByPr = [];
        if ($prIds !== []) {
            $rows = $db->table('purchase_request_items pri')
                ->select('pri.purchase_request_id, pri.quantity, ii.item_name, ii.unit_of_measure')
                ->join('inventory_items ii', 'ii.id = pri.inventory_item_id', 'left')
                ->whereIn('pri.purchase_request_id', $prIds)
                ->orderBy('ii.item_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $rid = (int) ($row['purchase_request_id'] ?? 0);
                if (! isset($itemsByPr[$rid])) {
                    $itemsByPr[$rid] = [];
                }
                $itemsByPr[$rid][] = $row;
            }
        }

        return view('dashboard/top_management/pr_approvals', [
            'title' => 'PR Approvals',
            'prs' => $prs,
            'itemsByPr' => $itemsByPr,
        ]);
    }

    public function approvePR($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $auditModel = new AuditLogModel();

        $pr = $prModel->find($id);
        if (! $pr) {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'PR not found.');
        }

        if (($pr['status'] ?? '') !== 'submitted') {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'Only submitted PRs can be approved.');
        }

        if ((int) ($pr['requested_by'] ?? 0) === (int) session('user_id')) {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'You cannot approve your own PR.');
        }

        $prModel->update($id, [
            'status' => 'approved',
            'approved_by' => (int) session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $auditModel->logAction('pr_approve', 'top_management', (int) $id, ['status' => 'submitted'], ['status' => 'approved'], 'Approved PR ' . ($pr['pr_number'] ?? ''));

        return redirect()->to('/top-management/pr-approvals')->with('success', 'PR approved.');
    }

    public function rejectPR($id)
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $auditModel = new AuditLogModel();

        $pr = $prModel->find($id);
        if (! $pr) {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'PR not found.');
        }

        if (($pr['status'] ?? '') !== 'submitted') {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'Only submitted PRs can be rejected.');
        }

        if ((int) ($pr['requested_by'] ?? 0) === (int) session('user_id')) {
            return redirect()->to('/top-management/pr-approvals')->with('error', 'You cannot reject your own PR.');
        }

        $prModel->update($id, [
            'status' => 'rejected',
            'approved_by' => (int) session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $auditModel->logAction('pr_reject', 'top_management', (int) $id, ['status' => 'submitted'], ['status' => 'rejected'], 'Rejected PR ' . ($pr['pr_number'] ?? ''));

        return redirect()->to('/top-management/pr-approvals')->with('success', 'PR rejected.');
    }
}

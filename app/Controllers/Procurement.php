<?php

namespace App\Controllers;

use App\Models\AccountsPayableModel;
use App\Models\AuditLogModel;
use App\Models\InventoryModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseRequestItemModel;
use App\Models\PurchaseRequestModel;
use App\Models\VendorModel;
use App\Models\WarehouseModel;

class Procurement extends BaseController
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

    private function generateNumber(string $prefix, string $table, string $field): string
    {
        $year = date('Y');
        $like = $prefix . '-' . $year . '-%';

        $row = $this->db->table($table)
            ->selectMax($field)
            ->like($field, $like, 'after')
            ->get()
            ->getRowArray();

        $max = $row[$field] ?? null;
        $next = 1;
        if ($max) {
            $parts = explode('-', $max);
            $last = end($parts);
            if (is_numeric($last)) {
                $next = ((int) $last) + 1;
            }
        }

        return $prefix . '-' . $year . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function computeReceivingForPO(string $poNumber): array
    {
        $received = $this->db->table('stock_movements')
            ->select('inventory_item_id, SUM(quantity) as received_qty')
            ->where('movement_type', 'in')
            ->where('approval_status', 'approved')
            ->where('reference_number', $poNumber)
            ->groupBy('inventory_item_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($received as $r) {
            $map[(int) $r['inventory_item_id']] = (int) ($r['received_qty'] ?? 0);
        }

        return $map;
    }

    private function syncPOStatus(int $poId): void
    {
        $poModel = new PurchaseOrderModel();
        $poItemModel = new PurchaseOrderItemModel();

        $po = $poModel->find($poId);
        if (! $po) {
            return;
        }

        if (in_array($po['status'] ?? 'pending', ['cancelled'], true)) {
            return;
        }

        $items = $poItemModel->where('purchase_order_id', $poId)->findAll();
        if ($items === []) {
            return;
        }

        $receivedMap = $this->computeReceivingForPO($po['po_number']);

        $orderedTotal = 0;
        $receivedTotal = 0;
        foreach ($items as $it) {
            $qty = (int) ($it['quantity'] ?? 0);
            $orderedTotal += $qty;
            $receivedTotal += min($qty, (int) ($receivedMap[(int) $it['inventory_item_id']] ?? 0));
        }

        $newStatus = 'pending';
        if ($receivedTotal <= 0) {
            $newStatus = 'pending';
        } elseif ($receivedTotal < $orderedTotal) {
            $newStatus = 'partial';
        } else {
            $newStatus = 'complete';
        }

        if (($po['status'] ?? null) !== $newStatus) {
            $poModel->update($poId, ['status' => $newStatus]);
        }
    }

    public function index()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $prModel = new PurchaseRequestModel();
        $poModel = new PurchaseOrderModel();

        $lowStock = $inventoryModel->getLowStockItems();

        $pendingPRs = $prModel->whereIn('status', ['draft', 'submitted'])->countAllResults(false);
        $openPOs = $poModel->whereIn('status', ['pending', 'partial'])->countAllResults(false);
        $delayedPOs = $poModel->whereIn('status', ['pending', 'partial'])
            ->where('expected_delivery_date IS NOT NULL')
            ->where('expected_delivery_date <', date('Y-m-d'))
            ->countAllResults(false);

        return view('dashboard/procurement/index', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'low_stock_count' => is_array($lowStock) ? count($lowStock) : 0,
            'pending_pr_count' => $pendingPRs,
            'open_po_count' => $openPOs,
            'delayed_po_count' => $delayedPOs,
            'low_stock_items' => array_slice($lowStock ?? [], 0, 10),
        ]);
    }

    public function purchaseRequests()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $rows = $this->db->table('purchase_requests pr')
            ->select('pr.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = pr.requested_by', 'left')
            ->orderBy('pr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('dashboard/procurement/pr_list', [
            'title' => 'Purchase Requests',
            'active' => 'prs',
            'prs' => $rows,
        ]);
    }

    public function createPR()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $warehouseModel = new WarehouseModel();

        return view('dashboard/procurement/pr_create', [
            'title' => 'Create PR',
            'active' => 'prs',
            'items' => $inventoryModel->orderBy('item_name', 'ASC')->findAll(),
            'warehouses' => $warehouseModel->where('status', 'active')->findAll(),
        ]);
    }

    public function storePR()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $rules = [
            'warehouse_id' => 'permit_empty|integer',
            'notes' => 'permit_empty',
            'inventory_item_id' => 'required',
            'quantity' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors and try again.');
        }

        $itemIds = (array) $this->request->getPost('inventory_item_id');
        $qtys = (array) $this->request->getPost('quantity');
        $notes = (array) $this->request->getPost('item_notes');

        $rows = [];
        foreach ($itemIds as $i => $itemId) {
            $q = (int) ($qtys[$i] ?? 0);
            if ((int) $itemId <= 0 || $q <= 0) {
                continue;
            }
            $rows[] = [
                'inventory_item_id' => (int) $itemId,
                'quantity' => $q,
                'notes' => $notes[$i] ?? null,
            ];
        }

        if ($rows === []) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one valid item.');
        }

        $prModel = new PurchaseRequestModel();
        $prItemModel = new PurchaseRequestItemModel();
        $auditModel = new AuditLogModel();

        $prNumber = $this->generateNumber('PR', 'purchase_requests', 'pr_number');

        $this->db->transStart();

        $prId = $prModel->insert([
            'pr_number' => $prNumber,
            'requested_by' => (int) session('user_id'),
            'warehouse_id' => $this->request->getPost('warehouse_id') ?: null,
            'status' => 'draft',
            'notes' => $this->request->getPost('notes'),
        ], true);

        foreach ($rows as $r) {
            $r['purchase_request_id'] = (int) $prId;
            $prItemModel->insert($r);
        }

        $auditModel->logAction('pr_create', 'procurement', (int) $prId, null, ['pr_number' => $prNumber], 'Created PR ' . $prNumber);

        $this->db->transComplete();

        return redirect()->to('/procurement/prs')->with('success', 'PR created successfully.');
    }

    public function submitPR($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $auditModel = new AuditLogModel();

        $pr = $prModel->find($id);
        if (! $pr) {
            return redirect()->to('/procurement/prs')->with('error', 'PR not found.');
        }

        if (($pr['status'] ?? '') !== 'draft') {
            return redirect()->to('/procurement/prs')->with('error', 'Only draft PRs can be submitted.');
        }

        $prModel->update($id, ['status' => 'submitted']);
        $auditModel->logAction('pr_submit', 'procurement', (int) $id, ['status' => 'draft'], ['status' => 'submitted'], 'Submitted PR ' . ($pr['pr_number'] ?? ''));

        return redirect()->to('/procurement/prs')->with('success', 'PR submitted for approval.');
    }

    public function viewPR($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $pr = $prModel->find($id);
        if (! $pr) {
            return redirect()->to('/procurement/prs')->with('error', 'PR not found.');
        }

        $items = $this->db->table('purchase_request_items pri')
            ->select('pri.*, ii.item_name, ii.unit_of_measure')
            ->join('inventory_items ii', 'ii.id = pri.inventory_item_id', 'left')
            ->where('pri.purchase_request_id', $id)
            ->get()
            ->getResultArray();

        $po = $this->db->table('purchase_orders')
            ->where('purchase_request_id', $id)
            ->get()
            ->getRowArray();

        return view('dashboard/procurement/pr_view', [
            'title' => 'View PR',
            'active' => 'prs',
            'pr' => $pr,
            'items' => $items,
            'po' => $po,
        ]);
    }

    public function purchaseOrders()
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $poIds = $this->db->table('purchase_orders')->select('id')->get()->getResultArray();
        foreach ($poIds as $row) {
            if (! empty($row['id'])) {
                $this->syncPOStatus((int) $row['id']);
            }
        }

        $rows = $this->db->table('purchase_orders po')
            ->select('po.*, v.vendor_name, w.warehouse_name')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->orderBy('po.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('dashboard/procurement/po_list', [
            'title' => 'Purchase Orders',
            'active' => 'pos',
            'pos' => $rows,
        ]);
    }

    public function createPOFromPR($prId)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $pr = $prModel->find($prId);
        if (! $pr) {
            return redirect()->to('/procurement/prs')->with('error', 'PR not found.');
        }

        if (($pr['status'] ?? '') !== 'approved') {
            return redirect()->to('/procurement/prs/' . $prId)->with('error', 'PR must be approved before creating a PO.');
        }

        $existing = $this->db->table('purchase_orders')->where('purchase_request_id', $prId)->get()->getRowArray();
        if ($existing) {
            return redirect()->to('/procurement/pos/' . $existing['id'])->with('error', 'PO already exists for this PR.');
        }

        $items = $this->db->table('purchase_request_items pri')
            ->select('pri.*, ii.item_name, ii.unit_of_measure, ii.unit_price')
            ->join('inventory_items ii', 'ii.id = pri.inventory_item_id', 'left')
            ->where('pri.purchase_request_id', $prId)
            ->get()
            ->getResultArray();

        $vendorModel = new VendorModel();
        $warehouseModel = new WarehouseModel();

        return view('dashboard/procurement/po_create_from_pr', [
            'title' => 'Create PO',
            'active' => 'pos',
            'pr' => $pr,
            'items' => $items,
            'vendors' => $vendorModel->where('status', 'active')->findAll(),
            'warehouses' => $warehouseModel->where('status', 'active')->findAll(),
        ]);
    }

    public function storePOFromPR($prId)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $prModel = new PurchaseRequestModel();
        $pr = $prModel->find($prId);
        if (! $pr) {
            return redirect()->to('/procurement/prs')->with('error', 'PR not found.');
        }

        if (($pr['status'] ?? '') !== 'approved') {
            return redirect()->to('/procurement/prs/' . $prId)->with('error', 'PR must be approved before creating a PO.');
        }

        $rules = [
            'vendor_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'expected_delivery_date' => 'permit_empty|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors and try again.');
        }

        $existing = $this->db->table('purchase_orders')->where('purchase_request_id', $prId)->get()->getRowArray();
        if ($existing) {
            return redirect()->to('/procurement/pos/' . $existing['id'])->with('error', 'PO already exists for this PR.');
        }

        $poModel = new PurchaseOrderModel();
        $poItemModel = new PurchaseOrderItemModel();
        $prItemModel = new PurchaseRequestItemModel();
        $auditModel = new AuditLogModel();

        $poNumber = $this->generateNumber('PO', 'purchase_orders', 'po_number');

        $this->db->transStart();

        $poId = $poModel->insert([
            'po_number' => $poNumber,
            'purchase_request_id' => (int) $prId,
            'vendor_id' => (int) $this->request->getPost('vendor_id'),
            'warehouse_id' => (int) $this->request->getPost('warehouse_id'),
            'status' => 'pending',
            'po_approval_status' => 'pending',
            'expected_delivery_date' => $this->request->getPost('expected_delivery_date') ?: null,
            'created_by' => (int) session('user_id'),
        ], true);

        $prItems = $prItemModel->where('purchase_request_id', $prId)->findAll();
        foreach ($prItems as $pri) {
            $poItemModel->insert([
                'purchase_order_id' => (int) $poId,
                'inventory_item_id' => (int) $pri['inventory_item_id'],
                'quantity' => (int) $pri['quantity'],
                'unit_price' => null,
            ]);
        }

        $auditModel->logAction('po_create', 'procurement', (int) $poId, null, ['po_number' => $poNumber, 'pr_id' => (int) $prId], 'Created PO ' . $poNumber);

        $this->db->transComplete();

        return redirect()->to('/procurement/pos/' . $poId)->with('success', 'PO created successfully.');
    }

    public function viewPO($id)
    {
        if ($redirect = $this->requireProcurement()) {
            return $redirect;
        }

        $poModel = new PurchaseOrderModel();
        $po = $poModel->find($id);
        if (! $po) {
            return redirect()->to('/procurement/pos')->with('error', 'PO not found.');
        }

        $this->syncPOStatus((int) $id);
        $po = $poModel->find($id);

        $items = $this->db->table('purchase_order_items poi')
            ->select('poi.*, ii.item_name, ii.unit_of_measure')
            ->join('inventory_items ii', 'ii.id = poi.inventory_item_id', 'left')
            ->where('poi.purchase_order_id', $id)
            ->get()
            ->getResultArray();

        $receivedMap = $this->computeReceivingForPO($po['po_number']);

        $vendor = $this->db->table('vendors')->where('id', $po['vendor_id'])->get()->getRowArray();
        $warehouse = $this->db->table('warehouses')->where('id', $po['warehouse_id'])->get()->getRowArray();

        $apModel = new AccountsPayableModel();
        $invoices = $apModel->where('po_reference', $po['po_number'])->findAll();

        return view('dashboard/procurement/po_view', [
            'title' => 'View PO',
            'active' => 'pos',
            'po' => $po,
            'vendor' => $vendor,
            'warehouse' => $warehouse,
            'items' => $items,
            'received_map' => $receivedMap,
            'invoices' => $invoices,
        ]);
    }
}

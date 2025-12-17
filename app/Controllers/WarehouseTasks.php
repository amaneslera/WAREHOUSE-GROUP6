<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\InventoryModel;
use App\Models\StockMovementModel;
use App\Models\WarehouseTaskModel;

class WarehouseTasks extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function requireManager()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'warehouse_manager') {
            return redirect()->to('/login');
        }

        return null;
    }

    private function requireStaff()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'warehouse_staff') {
            return redirect()->to('/login');
        }

        return null;
    }

    public function managerIndex()
    {
        if ($redirect = $this->requireManager()) {
            return $redirect;
        }

        $rows = $this->db->table('purchase_orders po')
            ->select('po.*, v.vendor_name, w.warehouse_name, wt.id as task_id')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->join('warehouse_tasks wt', 'wt.po_id = po.id', 'left')
            ->where('po.status', 'pending')
            ->where('wt.id', null)
            ->orderBy('po.expected_delivery_date', 'ASC')
            ->orderBy('po.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $poIds = array_values(array_filter(array_map(static fn ($r) => (int) ($r['id'] ?? 0), $rows)));
        $itemsByPo = [];
        if ($poIds !== []) {
            $itemRows = $this->db->table('purchase_order_items poi')
                ->select('poi.purchase_order_id, poi.quantity, ii.item_name, ii.unit_of_measure')
                ->join('inventory_items ii', 'ii.id = poi.inventory_item_id', 'left')
                ->whereIn('poi.purchase_order_id', $poIds)
                ->orderBy('ii.item_name', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($itemRows as $row) {
                $rid = (int) ($row['purchase_order_id'] ?? 0);
                if (! isset($itemsByPo[$rid])) {
                    $itemsByPo[$rid] = [];
                }
                $itemsByPo[$rid][] = $row;
            }
        }

        return view('dashboard/manager/po_receiving_tasks', [
            'title' => 'Incoming Purchase Orders',
            'pos' => $rows,
            'itemsByPo' => $itemsByPo,
        ]);
    }

    public function createTask($poId)
    {
        if ($redirect = $this->requireManager()) {
            return $redirect;
        }

        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.vendor_name, w.warehouse_name')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->where('po.id', $poId)
            ->get()
            ->getRowArray();

        if (! $po) {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'PO not found.');
        }

        if (($po['status'] ?? 'pending') !== 'pending') {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task can only be created for open POs.');
        }

        $existing = $this->db->table('warehouse_tasks')->where('po_id', $poId)->get()->getRowArray();
        if ($existing) {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task already exists for this PO.');
        }

        $items = $this->db->table('purchase_order_items poi')
            ->select('poi.*, ii.item_name, ii.unit_of_measure')
            ->join('inventory_items ii', 'ii.id = poi.inventory_item_id', 'left')
            ->where('poi.purchase_order_id', $poId)
            ->get()
            ->getResultArray();

        $staff = $this->db->table('users')
            ->select('id, first_name, last_name, email')
            ->where('role', 'warehouse_staff')
            ->orderBy('first_name', 'ASC')
            ->get()
            ->getResultArray();

        $warehouses = $this->db->table('warehouses')
            ->select('id, warehouse_name')
            ->where('status', 'active')
            ->orderBy('warehouse_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('dashboard/manager/task_create', [
            'title' => 'Create Receiving Task',
            'po' => $po,
            'items' => $items,
            'staff' => $staff,
            'warehouses' => $warehouses,
        ]);
    }

    public function storeTask($poId)
    {
        if ($redirect = $this->requireManager()) {
            return $redirect;
        }

        $rules = [
            'assigned_staff_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'scheduled_at' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors and try again.');
        }

        $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
        if (! $po) {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'PO not found.');
        }

        if (($po['status'] ?? 'pending') !== 'pending') {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task can only be created for open POs.');
        }

        $existing = $this->db->table('warehouse_tasks')->where('po_id', $poId)->get()->getRowArray();
        if ($existing) {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task already exists for this PO.');
        }

        $taskModel = new WarehouseTaskModel();
        $auditModel = new AuditLogModel();

        $taskId = $taskModel->insert([
            'po_id' => (int) $poId,
            'assigned_staff_id' => (int) $this->request->getPost('assigned_staff_id'),
            'warehouse_id' => (int) $this->request->getPost('warehouse_id'),
            'scheduled_at' => $this->request->getPost('scheduled_at'),
            'status' => 'pending',
            'created_by' => (int) session('user_id'),
        ], true);

        $auditModel->logAction(
            'task_create',
            'warehouse_tasks',
            (int) $taskId,
            null,
            ['po_id' => (int) $poId, 'assigned_staff_id' => (int) $this->request->getPost('assigned_staff_id')],
            'Created receiving task for PO ' . ($po['po_number'] ?? '')
        );

        $auditModel->logAction(
            'task_created',
            'warehouse_tasks',
            (int) $taskId,
            null,
            ['po_id' => (int) $poId, 'assigned_staff_id' => (int) $this->request->getPost('assigned_staff_id')],
            'Created receiving task for PO ' . ($po['po_number'] ?? '')
        );

        return redirect()->to('/dashboard/manager/tasks')->with('success', 'Task created successfully.');
    }

    public function staffIndex()
    {
        if ($redirect = $this->requireStaff()) {
            return $redirect;
        }

        $rows = $this->db->table('warehouse_tasks wt')
            ->select('wt.*, po.po_number, po.expected_delivery_date, v.vendor_name, w.warehouse_name')
            ->join('purchase_orders po', 'po.id = wt.po_id', 'left')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = wt.warehouse_id', 'left')
            ->where('wt.assigned_staff_id', (int) session('user_id'))
            ->orderBy('wt.status', 'ASC')
            ->orderBy('wt.scheduled_at', 'ASC')
            ->get()
            ->getResultArray();

        return view('dashboard/staff/tasks', [
            'title' => 'My Receiving Tasks',
            'tasks' => $rows,
        ]);
    }

    public function start($taskId)
    {
        if ($redirect = $this->requireStaff()) {
            return $redirect;
        }

        $taskModel = new WarehouseTaskModel();
        $auditModel = new AuditLogModel();

        $task = $taskModel->find($taskId);
        if (! $task) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Task not found.');
        }

        if ((int) $task['assigned_staff_id'] !== (int) session('user_id')) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Unauthorized task access.');
        }

        if (($task['status'] ?? 'pending') !== 'pending') {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Only pending tasks can be started.');
        }

        $taskModel->update($taskId, ['status' => 'in_progress']);
        $auditModel->logAction('task_start', 'warehouse_tasks', (int) $taskId, ['status' => 'pending'], ['status' => 'in_progress'], 'Started receiving task');

        return redirect()->to('/dashboard/staff/tasks')->with('success', 'Task started.');
    }

    private function getReceivedMapForPO(string $poNumber): array
    {
        $hasApproval = $this->db->fieldExists('approval_status', 'stock_movements');

        $builder = $this->db->table('stock_movements')
            ->select('inventory_item_id, SUM(quantity) as received_qty')
            ->where('movement_type', 'in')
            ->where('reference_number', $poNumber);

        if ($hasApproval) {
            $builder->where('approval_status', 'approved');
        }

        $received = $builder
            ->groupBy('inventory_item_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($received as $r) {
            $map[(int) $r['inventory_item_id']] = (int) ($r['received_qty'] ?? 0);
        }

        return $map;
    }

    private function canCompleteTask(int $poId, string $poNumber): bool
    {
        $poItems = $this->db->table('purchase_order_items')
            ->select('inventory_item_id, quantity')
            ->where('purchase_order_id', $poId)
            ->get()
            ->getResultArray();

        if ($poItems === []) {
            return false;
        }

        $receivedMap = $this->getReceivedMapForPO($poNumber);

        foreach ($poItems as $it) {
            $itemId = (int) ($it['inventory_item_id'] ?? 0);
            $ordered = (int) ($it['quantity'] ?? 0);
            $received = (int) ($receivedMap[$itemId] ?? 0);

            if ($received < $ordered) {
                return false;
            }
        }

        return true;
    }

    public function complete($taskId)
    {
        if ($redirect = $this->requireStaff()) {
            return $redirect;
        }

        $taskModel = new WarehouseTaskModel();
        $auditModel = new AuditLogModel();

        $task = $taskModel->find($taskId);
        if (! $task) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Task not found.');
        }

        if ((int) $task['assigned_staff_id'] !== (int) session('user_id')) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Unauthorized task access.');
        }

        if (($task['status'] ?? '') !== 'in_progress') {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Only in-progress tasks can be completed.');
        }

        $po = $this->db->table('purchase_orders')->where('id', (int) $task['po_id'])->get()->getRowArray();
        if (! $po) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'PO not found for this task.');
        }

        if (! $this->canCompleteTask((int) $po['id'], (string) ($po['po_number'] ?? ''))) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Cannot complete task: receiving is not fully completed yet.');
        }

        $taskModel->update($taskId, ['status' => 'completed']);
        $auditModel->logAction('task_complete', 'warehouse_tasks', (int) $taskId, ['status' => 'in_progress'], ['status' => 'completed'], 'Completed receiving task');

        return redirect()->to('/dashboard/staff/tasks')->with('success', 'Task completed.');
    }

    public function apiReceive($taskId)
    {
        if (! session('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 'error', 'message' => 'Not authenticated.']);
        }

        if (session('user_role') !== 'warehouse_staff') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Unauthorized access.']);
        }

        $taskModel = new WarehouseTaskModel();
        $auditModel = new AuditLogModel();
        $inventoryModel = new InventoryModel();
        $movementModel = new StockMovementModel();

        $task = $taskModel->find($taskId);
        if (! $task) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Task not found.']);
        }

        if ((int) ($task['assigned_staff_id'] ?? 0) !== (int) session('user_id')) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Unauthorized task access.']);
        }

        if (($task['status'] ?? '') !== 'in_progress') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Task must be in progress to receive items.']);
        }

        $payload = $this->request->getJSON(true) ?? [];
        $barcode = (string) ($payload['barcode'] ?? '');
        $qty = (int) ($payload['quantity'] ?? 0);

        if ($barcode === '' || $qty <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'barcode and quantity are required.']);
        }

        $po = $this->db->table('purchase_orders')->where('id', (int) ($task['po_id'] ?? 0))->get()->getRowArray();
        if (! $po) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'PO not found for this task.']);
        }

        $item = null;
        $invBuilder = $this->db->table('inventory_items');
        $invBuilder->groupStart();
        if (is_numeric($barcode)) {
            $invBuilder->orWhere('id', (int) $barcode);
        }
        $invBuilder->orWhere('item_id', $barcode);
        if ($this->db->fieldExists('barcode', 'inventory_items')) {
            $invBuilder->orWhere('barcode', $barcode);
        }
        $invBuilder->groupEnd();
        $item = $invBuilder->get()->getRowArray();

        if (! $item) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Item not found.']);
        }

        $taskWarehouseId = (int) ($task['warehouse_id'] ?? 0);
        if ((int) ($item['warehouse_id'] ?? 0) !== $taskWarehouseId) {
            $sameItem = $this->db->table('inventory_items')
                ->where('item_id', (string) ($item['item_id'] ?? ''))
                ->where('warehouse_id', $taskWarehouseId)
                ->get()
                ->getRowArray();

            if ($sameItem) {
                $item = $sameItem;
            } else {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Scanned item is not available for this warehouse.']);
            }
        }

        $poItem = $this->db->table('purchase_order_items')
            ->where('purchase_order_id', (int) ($po['id'] ?? 0))
            ->where('inventory_item_id', (int) ($item['id'] ?? 0))
            ->get()
            ->getRowArray();

        if (! $poItem) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Item is not part of this PO.']);
        }

        $orderedQty = (int) ($poItem['quantity'] ?? 0);
        $receivedMap = $this->getReceivedMapForPO((string) ($po['po_number'] ?? ''));
        $alreadyReceived = (int) ($receivedMap[(int) ($item['id'] ?? 0)] ?? 0);
        $remaining = $orderedQty - $alreadyReceived;

        if ($remaining <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Ordered quantity already fully received for this item.']);
        }

        if ($qty > $remaining) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Quantity exceeds remaining ordered amount.', 'remaining' => $remaining]);
        }

        $this->db->transStart();

        $movementData = [
            'inventory_item_id' => (int) ($item['id'] ?? 0),
            'movement_type' => 'in',
            'quantity' => $qty,
            'from_warehouse_id' => null,
            'to_warehouse_id' => $taskWarehouseId,
            'reference_number' => (string) ($po['po_number'] ?? ''),
            'notes' => 'Receiving Task #' . (int) $taskId,
            'performed_by' => (int) session('user_id'),
        ];

        if ($this->db->fieldExists('approval_status', 'stock_movements')) {
            $movementData['approval_status'] = 'approved';
        }
        if ($this->db->fieldExists('approved_by', 'stock_movements')) {
            $movementData['approved_by'] = (int) session('user_id');
        }
        if ($this->db->fieldExists('approval_notes', 'stock_movements')) {
            $movementData['approval_notes'] = 'Auto-approved via receiving task';
        }

        $movementId = $movementModel->insert($movementData, true);

        $newStock = (int) ($item['current_stock'] ?? 0) + $qty;
        $inventoryModel->updateStock((int) ($item['id'] ?? 0), $newStock);

        $this->syncPOStatusAfterReceiving((int) ($po['id'] ?? 0), (string) ($po['po_number'] ?? ''));

        $auditModel->logAction(
            'stock_in',
            'warehouse_tasks',
            (int) $taskId,
            null,
            ['stock_movement_id' => (int) $movementId, 'po_number' => (string) ($po['po_number'] ?? ''), 'inventory_item_id' => (int) ($item['id'] ?? 0), 'quantity' => $qty],
            'Received items for PO ' . (string) ($po['po_number'] ?? '')
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Failed to record receiving.']);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 'success',
            'message' => 'Received successfully.',
            'movement_id' => (int) $movementId,
            'remaining' => $remaining - $qty,
        ]);
    }

    private function syncPOStatusAfterReceiving(int $poId, string $poNumber): void
    {
        $po = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
        if (! $po) {
            return;
        }

        if (in_array(($po['status'] ?? 'pending'), ['cancelled'], true)) {
            return;
        }

        $poItems = $this->db->table('purchase_order_items')
            ->select('inventory_item_id, quantity')
            ->where('purchase_order_id', $poId)
            ->get()
            ->getResultArray();

        if ($poItems === []) {
            return;
        }

        $receivedMap = $this->getReceivedMapForPO($poNumber);

        $orderedTotal = 0;
        $receivedTotal = 0;
        foreach ($poItems as $it) {
            $qty = (int) ($it['quantity'] ?? 0);
            $orderedTotal += $qty;
            $receivedTotal += min($qty, (int) ($receivedMap[(int) ($it['inventory_item_id'] ?? 0)] ?? 0));
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
            $this->db->table('purchase_orders')->where('id', $poId)->update(['status' => $newStatus]);
        }
    }
}

<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
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

        if (! $this->db->tableExists('purchase_orders') || ! $this->db->fieldExists('po_approval_status', 'purchase_orders')) {
            return redirect()->to('/dashboard/manager')->with('error', 'PO approval fields are not installed. Please run migrations.');
        }

        $rows = $this->db->table('purchase_orders po')
            ->select('po.*, v.vendor_name, w.warehouse_name, wt.id as task_id, wt.status as task_status, wt.scheduled_at')
            ->join('vendors v', 'v.id = po.vendor_id', 'left')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->join('warehouse_tasks wt', 'wt.po_id = po.id', 'left')
            ->where('po.po_approval_status', 'approved')
            ->orderBy('po.expected_delivery_date', 'ASC')
            ->orderBy('po.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('dashboard/manager/po_receiving_tasks', [
            'title' => 'Receiving Tasks',
            'pos' => $rows,
        ]);
    }

    public function createTask($poId)
    {
        if ($redirect = $this->requireManager()) {
            return $redirect;
        }

        if (! $this->db->tableExists('purchase_orders') || ! $this->db->fieldExists('po_approval_status', 'purchase_orders')) {
            return redirect()->to('/dashboard/manager')->with('error', 'PO approval fields are not installed. Please run migrations.');
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

        if (($po['po_approval_status'] ?? 'pending') !== 'approved') {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task can only be created for Top-Approved POs.');
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

        if (! array_key_exists('po_approval_status', $po) || ($po['po_approval_status'] ?? 'pending') !== 'approved') {
            return redirect()->to('/dashboard/manager/tasks')->with('error', 'Task can only be created for Top-Approved POs.');
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

        return redirect()->to('/dashboard/manager/tasks')->with('success', 'Task created successfully.');
    }

    public function staffIndex()
    {
        if ($redirect = $this->requireStaff()) {
            return $redirect;
        }

        $rows = $this->db->table('warehouse_tasks wt')
            ->select('wt.*, po.po_number, po.po_approval_status, po.expected_delivery_date, v.vendor_name, w.warehouse_name')
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

        if (($po['po_approval_status'] ?? 'pending') !== 'approved') {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'PO is not Top-Approved.');
        }

        if (! $this->canCompleteTask((int) $po['id'], (string) ($po['po_number'] ?? ''))) {
            return redirect()->to('/dashboard/staff/tasks')->with('error', 'Cannot complete task: receiving is not fully approved yet.');
        }

        $taskModel->update($taskId, ['status' => 'completed']);
        $auditModel->logAction('task_complete', 'warehouse_tasks', (int) $taskId, ['status' => 'in_progress'], ['status' => 'completed'], 'Completed receiving task');

        return redirect()->to('/dashboard/staff/tasks')->with('success', 'Task completed.');
    }
}

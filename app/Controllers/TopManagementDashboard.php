<?php

namespace App\Controllers;

use App\Models\AccountsPayableModel;
use App\Models\AccountsReceivableModel;
use App\Models\InventoryModel;
use App\Models\PurchaseRequestModel;
use App\Models\PurchaseOrderModel;

class TopManagementDashboard extends BaseController
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

    private function getPendingPoCount(): int
    {
        $db = \Config\Database::connect();
        if (! $db->fieldExists('po_approval_status', 'purchase_orders')) {
            return 0;
        }

        $poModel = new PurchaseOrderModel();
        return (int) $poModel->where('po_approval_status', 'pending')->countAllResults(false);
    }

    public function index()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $apModel = new AccountsPayableModel();
        $arModel = new AccountsReceivableModel();
        $prModel = new PurchaseRequestModel();
        $poModel = new PurchaseOrderModel();

        $warehouseSummary = $inventoryModel->getStockSummaryByWarehouse();
        $lowStockItems = $inventoryModel->getLowStockItems();

        $apStats = $apModel->getStatistics();
        $arOutstanding = $arModel->getTotalOutstanding();
        $arPendingCount = $arModel->whereIn('status', ['pending', 'partial', 'overdue'])->countAllResults(false);

        $pendingPrCount = $prModel->where('status', 'submitted')->countAllResults(false);

        $pendingPoCount = $this->getPendingPoCount();

        return view('dashboard/top_management/dashboard', [
            'title' => 'Top Management Dashboard',
            'warehouseSummary' => $warehouseSummary,
            'lowStockCount' => is_array($lowStockItems) ? count($lowStockItems) : 0,
            'pendingPrCount' => $pendingPrCount,
            'pendingPoCount' => $pendingPoCount,
            'apStats' => $apStats,
            'arOutstanding' => $arOutstanding,
            'arPendingCount' => $arPendingCount,
        ]);
    }

    public function kpis()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $apModel = new AccountsPayableModel();
        $arModel = new AccountsReceivableModel();
        $prModel = new PurchaseRequestModel();

        $apStats = $apModel->getStatistics();
        $arOutstanding = $arModel->getTotalOutstanding();

        $kpis = [
            'pending_pr' => (int) $prModel->where('status', 'submitted')->countAllResults(false),
            'pending_po' => (int) $this->getPendingPoCount(),
            'low_stock' => is_array($inventoryModel->getLowStockItems()) ? count($inventoryModel->getLowStockItems()) : 0,
            'ap_outstanding' => (float) ($apStats['total_outstanding'] ?? 0),
            'ar_outstanding' => (float) $arOutstanding,
        ];

        return view('dashboard/top_management/kpis', [
            'title' => 'KPIs',
            'kpis' => $kpis,
        ]);
    }

    public function inventorySummary()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $warehouseSummary = $inventoryModel->getStockSummaryByWarehouse();

        return view('dashboard/top_management/inventory_summary', [
            'title' => 'Inventory Summary',
            'warehouseSummary' => $warehouseSummary,
        ]);
    }

    public function financialSummary()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $apModel = new AccountsPayableModel();
        $arModel = new AccountsReceivableModel();
        $db = \Config\Database::connect();

        $apStats = $apModel->getStatistics();
        $arOutstanding = $arModel->getTotalOutstanding();

        $arByStatus = $db->table('accounts_receivable')
            ->select('status, COUNT(*) as cnt')
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $arStatusCounts = [];
        foreach ($arByStatus as $row) {
            $st = (string) ($row['status'] ?? '');
            $arStatusCounts[$st] = (int) ($row['cnt'] ?? 0);
        }

        return view('dashboard/top_management/financial_summary', [
            'title' => 'Financial Summary',
            'apStats' => $apStats,
            'arOutstanding' => $arOutstanding,
            'arStatusCounts' => $arStatusCounts,
        ]);
    }

    public function exportWarehouseSummaryCsv()
    {
        if ($redirect = $this->requireTopManagement()) {
            return $redirect;
        }

        $inventoryModel = new InventoryModel();
        $rows = $inventoryModel->getStockSummaryByWarehouse();

        $filename = 'warehouse_summary_' . date('Ymd_His') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $out = fopen('php://temp', 'w+');
        fputcsv($out, ['warehouse_id', 'warehouse_name', 'total_items', 'total_quantity', 'total_value', 'low_stock_count']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['warehouse_id'] ?? '',
                $r['warehouse_name'] ?? '',
                $r['total_items'] ?? '',
                $r['total_quantity'] ?? '',
                $r['total_value'] ?? '',
                $r['low_stock_count'] ?? '',
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response->setBody($csv);
    }
}

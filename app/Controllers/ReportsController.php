<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\StockMovementModel;
use App\Models\AccountsReceivableModel;
use App\Models\ArPaymentTransactionsModel;
use App\Models\AccountsPayableModel;
use App\Models\ClientModel;
use App\Models\VendorModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * ReportsController
 * 
 * Centralized reporting system for all modules:
 * - Inventory reports (stock, low-stock, movements)
 * - Accounts Receivable reports (outstanding, aging, payment history)
 * - Accounts Payable reports (outstanding, aging, vendor summary)
 * - Warehouse usage dashboard
 * 
 * @package App\Controllers
 */
class ReportsController extends BaseController
{
    protected $inventoryModel;
    protected $stockMovementModel;
    protected $arModel;
    protected $arPaymentModel;
    protected $apModel;
    protected $clientModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->arModel = new AccountsReceivableModel();
        $this->arPaymentModel = new ArPaymentTransactionsModel();
        $this->apModel = new AccountsPayableModel();
        $this->clientModel = new ClientModel();
        $this->vendorModel = new VendorModel();
    }

    // ========================================
    // INVENTORY REPORTS
    // ========================================

    /**
     * GET /api/reports/inventory/summary
     * Current stock summary per warehouse
     * 
     * @return ResponseInterface JSON response
     */
    public function inventorySummary(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['warehouse_manager', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get stock summary by warehouse
            $warehouseSummary = $this->inventoryModel->getStockSummaryByWarehouse();

            // Get overall totals
            $totalValue = $this->inventoryModel->getTotalValue();
            $allItems = $this->inventoryModel->findAll();
            $totalItems = count($allItems);
            $totalQuantity = array_sum(array_column($allItems, 'current_stock'));

            return $this->respond([
                'status' => 'success',
                'message' => 'Inventory summary retrieved successfully',
                'data' => [
                    'warehouse_breakdown' => $warehouseSummary,
                    'overall_summary' => [
                        'total_items' => $totalItems,
                        'total_quantity' => $totalQuantity,
                        'total_value' => $totalValue
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Inventory summary report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve inventory summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/inventory/low-stock
     * Low-stock items with thresholds
     * 
     * @return ResponseInterface JSON response
     */
    public function inventoryLowStock(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['warehouse_manager', 'top_management', 'procurement_officer', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get low stock items
            $lowStockItems = $this->inventoryModel->getLowStockItems();

            // Enhance with warehouse and category names
            $builder = $this->inventoryModel->db->table('inventory_items i');
            $builder->select('i.*, w.warehouse_name, c.category_name');
            $builder->join('warehouses w', 'w.id = i.warehouse_id');
            $builder->join('categories c', 'c.id = i.category_id', 'left');
            $builder->where('i.current_stock <= i.minimum_stock');
            $builder->orWhere('i.current_stock', 0);
            $builder->orderBy('i.current_stock', 'ASC');
            $enhancedLowStock = $builder->get()->getResultArray();

            // Add urgency level
            foreach ($enhancedLowStock as &$item) {
                if ($item['current_stock'] == 0) {
                    $item['urgency'] = 'critical';
                } elseif ($item['current_stock'] <= $item['minimum_stock'] * 0.5) {
                    $item['urgency'] = 'high';
                } else {
                    $item['urgency'] = 'medium';
                }
                $item['reorder_quantity'] = max(0, $item['minimum_stock'] * 2 - $item['current_stock']);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Low stock report retrieved successfully',
                'data' => [
                    'low_stock_items' => $enhancedLowStock,
                    'summary' => [
                        'total_low_stock' => count($enhancedLowStock),
                        'critical_count' => count(array_filter($enhancedLowStock, fn($i) => $i['urgency'] === 'critical')),
                        'high_count' => count(array_filter($enhancedLowStock, fn($i) => $i['urgency'] === 'high')),
                        'medium_count' => count(array_filter($enhancedLowStock, fn($i) => $i['urgency'] === 'medium'))
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Low stock report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve low stock report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/inventory/movements
     * Total stock movement summary (IN, OUT, TRANSFER)
     * 
     * Query Parameters:
     * - date_from: Start date (default: 30 days ago)
     * - date_to: End date (default: today)
     * 
     * @return ResponseInterface JSON response
     */
    public function inventoryMovements(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['warehouse_manager', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get date range from query parameters
            $dateFrom = $this->request->getGet('date_from') ?? date('Y-m-d', strtotime('-30 days'));
            $dateTo = $this->request->getGet('date_to') ?? date('Y-m-d');

            // Get movement summary
            $movementSummary = $this->stockMovementModel->getTotalMovementSummary([
                'from' => $dateFrom,
                'to' => $dateTo
            ]);

            // Get most moved items
            $mostMovedItems = $this->stockMovementModel->getMostMovedItems(10);

            // Calculate totals
            $totalTransactions = array_sum(array_column($movementSummary, 'count'));
            $totalQuantity = array_sum(array_column($movementSummary, 'quantity'));

            return $this->respond([
                'status' => 'success',
                'message' => 'Stock movement report retrieved successfully',
                'data' => [
                    'period' => [
                        'from' => $dateFrom,
                        'to' => $dateTo
                    ],
                    'movement_summary' => $movementSummary,
                    'most_moved_items' => $mostMovedItems,
                    'totals' => [
                        'total_transactions' => $totalTransactions,
                        'total_quantity' => $totalQuantity
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Movement report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve movement report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // ACCOUNTS RECEIVABLE REPORTS
    // ========================================

    /**
     * GET /api/reports/ar/outstanding
     * Outstanding invoices summary
     * 
     * @return ResponseInterface JSON response
     */
    public function arOutstanding(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get pending and partial invoices
            $pendingInvoices = $this->arModel->getPendingInvoices();
            
            // Get total outstanding
            $totalOutstanding = $this->arModel->getTotalOutstanding();

            // Get invoices with client details
            $invoicesWithClients = $this->arModel->getInvoicesWithClients([
                'status' => ['pending', 'partial', 'overdue']
            ]);

            // Group by client
            $byClient = [];
            foreach ($invoicesWithClients as $invoice) {
                $clientId = $invoice['client_id'];
                if (!isset($byClient[$clientId])) {
                    $byClient[$clientId] = [
                        'client_name' => $invoice['client_name'],
                        'client_code' => $invoice['client_code'],
                        'invoice_count' => 0,
                        'total_outstanding' => 0
                    ];
                }
                $byClient[$clientId]['invoice_count']++;
                $byClient[$clientId]['total_outstanding'] += $invoice['balance'];
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AR outstanding report retrieved successfully',
                'data' => [
                    'total_outstanding' => $totalOutstanding,
                    'invoice_count' => count($pendingInvoices),
                    'by_client' => array_values($byClient),
                    'invoices' => $invoicesWithClients
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR outstanding report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AR outstanding report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/ar/aging
     * AR aging report (0-30, 31-60, 61-90, 90+)
     * 
     * @return ResponseInterface JSON response
     */
    public function arAging(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get aging report from model
            $agingReport = $this->arModel->getAgingReport();

            // Calculate totals
            $totalAmount = 0;
            $totalCount = 0;
            foreach ($agingReport as $bucket) {
                $totalAmount += $bucket['amount'];
                $totalCount += $bucket['count'];
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AR aging report retrieved successfully',
                'data' => [
                    'aging_buckets' => $agingReport,
                    'summary' => [
                        'total_outstanding' => $totalAmount,
                        'total_invoices' => $totalCount
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR aging report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AR aging report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/ar/history
     * Payment history per client
     * 
     * Query Parameters:
     * - client_id: Filter by client (optional)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * 
     * @return ResponseInterface JSON response
     */
    public function arHistory(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get query parameters
            $clientId = $this->request->getGet('client_id');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');

            // Build filters
            $filters = [];
            if ($clientId) $filters['client_id'] = $clientId;
            if ($dateFrom) $filters['date_from'] = $dateFrom;
            if ($dateTo) $filters['date_to'] = $dateTo;

            // Get payments with details
            $payments = $this->arPaymentModel->getPaymentsWithDetails($filters);

            // Group by client
            $byClient = [];
            foreach ($payments as $payment) {
                $cid = $payment['client_name'];
                if (!isset($byClient[$cid])) {
                    $byClient[$cid] = [
                        'client_name' => $payment['client_name'],
                        'payment_count' => 0,
                        'total_amount' => 0,
                        'payments' => []
                    ];
                }
                $byClient[$cid]['payment_count']++;
                $byClient[$cid]['total_amount'] += $payment['amount'];
                $byClient[$cid]['payments'][] = $payment;
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AR payment history retrieved successfully',
                'data' => [
                    'filters' => $filters,
                    'by_client' => array_values($byClient),
                    'all_payments' => $payments,
                    'summary' => [
                        'total_payments' => count($payments),
                        'total_amount' => array_sum(array_column($payments, 'amount'))
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR payment history error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AR payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // ACCOUNTS PAYABLE REPORTS
    // ========================================

    /**
     * GET /api/reports/ap/outstanding
     * Unpaid and partially paid invoices
     * 
     * @return ResponseInterface JSON response
     */
    public function apOutstanding(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_payable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get pending and partial invoices
            $pendingInvoices = $this->apModel->getPendingInvoices();
            $partialInvoices = $this->apModel->getPartiallyPaidInvoices();
            $overdueInvoices = $this->apModel->getOverdueInvoices();

            // Get total amount due
            $totalDue = $this->apModel->getTotalAmountDue();

            // Get invoices with vendor details
            $invoicesWithVendors = $this->apModel->getInvoicesWithVendors([
                'status' => ['pending', 'partial', 'overdue']
            ]);

            // Group by vendor
            $byVendor = [];
            foreach ($invoicesWithVendors as $invoice) {
                $vendorId = $invoice['vendor_id'];
                if (!isset($byVendor[$vendorId])) {
                    $byVendor[$vendorId] = [
                        'vendor_name' => $invoice['vendor_name'],
                        'vendor_code' => $invoice['vendor_code'],
                        'invoice_count' => 0,
                        'total_due' => 0
                    ];
                }
                $byVendor[$vendorId]['invoice_count']++;
                $byVendor[$vendorId]['total_due'] += $invoice['balance'];
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AP outstanding report retrieved successfully',
                'data' => [
                    'total_due' => $totalDue,
                    'invoice_count' => count($pendingInvoices) + count($partialInvoices),
                    'pending_count' => count($pendingInvoices),
                    'partial_count' => count($partialInvoices),
                    'overdue_count' => count($overdueInvoices),
                    'by_vendor' => array_values($byVendor),
                    'invoices' => $invoicesWithVendors
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AP outstanding report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AP outstanding report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/ap/aging
     * AP aging report (0-30, 31-60, 61-90, 90+)
     * 
     * @return ResponseInterface JSON response
     */
    public function apAging(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_payable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get aging report from model
            $agingReport = $this->apModel->getAgingReport();

            // Calculate totals
            $totalAmount = 0;
            $totalCount = 0;
            foreach ($agingReport as $bucket) {
                $totalAmount += $bucket['amount'];
                $totalCount += $bucket['count'];
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AP aging report retrieved successfully',
                'data' => [
                    'aging_buckets' => $agingReport,
                    'summary' => [
                        'total_due' => $totalAmount,
                        'total_invoices' => $totalCount
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AP aging report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AP aging report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/reports/ap/history
     * Vendor invoice summary
     * 
     * Query Parameters:
     * - vendor_id: Filter by vendor (optional)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * 
     * @return ResponseInterface JSON response
     */
    public function apHistory(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_payable_clerk', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            // Get query parameters
            $vendorId = $this->request->getGet('vendor_id');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');

            // Build filters
            $filters = [];
            if ($vendorId) $filters['vendor_id'] = $vendorId;
            if ($dateFrom) $filters['date_from'] = $dateFrom;
            if ($dateTo) $filters['date_to'] = $dateTo;

            // Get invoices with vendor details
            $invoices = $this->apModel->getInvoicesWithVendors($filters);

            // Group by vendor
            $byVendor = [];
            foreach ($invoices as $invoice) {
                $vid = $invoice['vendor_id'];
                if (!isset($byVendor[$vid])) {
                    $byVendor[$vid] = [
                        'vendor_id' => $vid,
                        'vendor_name' => $invoice['vendor_name'],
                        'vendor_code' => $invoice['vendor_code'],
                        'invoice_count' => 0,
                        'total_amount' => 0,
                        'total_paid' => 0,
                        'total_balance' => 0,
                        'invoices' => []
                    ];
                }
                $byVendor[$vid]['invoice_count']++;
                $byVendor[$vid]['total_amount'] += $invoice['invoice_amount'];
                $byVendor[$vid]['total_paid'] += $invoice['paid_amount'];
                $byVendor[$vid]['total_balance'] += $invoice['balance'];
                $byVendor[$vid]['invoices'][] = $invoice;
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'AP vendor invoice history retrieved successfully',
                'data' => [
                    'filters' => $filters,
                    'by_vendor' => array_values($byVendor),
                    'all_invoices' => $invoices,
                    'summary' => [
                        'total_invoices' => count($invoices),
                        'total_amount' => array_sum(array_column($invoices, 'invoice_amount')),
                        'total_paid' => array_sum(array_column($invoices, 'paid_amount')),
                        'total_balance' => array_sum(array_column($invoices, 'balance'))
                    ]
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AP vendor history error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AP vendor history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // WAREHOUSE USAGE DASHBOARD
    // ========================================

    /**
     * GET /api/reports/warehouse/usage
     * Warehouse usage dashboard
     * 
     * Query Parameters:
     * - warehouse_id: Specific warehouse (optional)
     * - days: Period in days for turnover calculation (default: 30)
     * 
     * @return ResponseInterface JSON response
     */
    public function warehouseUsage(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['warehouse_manager', 'top_management', 'auditor', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $warehouseId = $this->request->getGet('warehouse_id');
            $days = (int)($this->request->getGet('days') ?? 30);

            // Get warehouse stats
            $warehouseStats = $this->inventoryModel->getWarehouseStats();

            // Get most moved items
            $mostMovedItems = $this->inventoryModel->getMostMovedItems(10);

            $data = [
                'warehouse_stats' => $warehouseStats,
                'most_moved_items' => $mostMovedItems
            ];

            // If specific warehouse requested, add turnover rate
            if ($warehouseId) {
                $turnoverRate = $this->stockMovementModel->getWarehouseTurnoverRate($warehouseId, $days);
                $data['turnover_rate'] = $turnoverRate;
            } else {
                // Get turnover for all warehouses
                $builder = $this->inventoryModel->db->table('warehouses');
                $warehouses = $builder->select('id, warehouse_name')->get()->getResultArray();
                
                $turnoverRates = [];
                foreach ($warehouses as $wh) {
                    $turnoverRates[] = $this->stockMovementModel->getWarehouseTurnoverRate($wh['id'], $days);
                }
                $data['turnover_rates'] = $turnoverRates;
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Warehouse usage report retrieved successfully',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Warehouse usage report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve warehouse usage report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if user has required permission
     * 
     * @param array $allowedRoles
     * @return bool
     */
    private function checkPermission(array $allowedRoles): bool
    {
        $userRole = session()->get('user_role');
        
        if (!$userRole) {
            return false;
        }

        return in_array($userRole, $allowedRoles);
    }

    /**
     * Standard JSON response helper
     * 
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    private function respond(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($data);
    }
}

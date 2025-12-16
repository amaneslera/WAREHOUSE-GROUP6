<?php

namespace App\Controllers;

use App\Models\AccountsReceivableModel;
use App\Models\ArPaymentTransactionsModel;
use App\Models\ClientModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * AccountsReceivableController
 * 
 * Handles Accounts Receivable CRUD operations and payment recording
 * Provides both JSON API and web view endpoints
 */
class AccountsReceivableController extends BaseController
{
    protected $arModel;
    protected $paymentModel;
    protected $clientModel;
    
    public function __construct()
    {
        $this->arModel = new AccountsReceivableModel();
        $this->paymentModel = new ArPaymentTransactionsModel();
        $this->clientModel = new ClientModel();
    }

    // ========================================
    // RESTFUL CRUD OPERATIONS - JSON RESPONSES
    // ========================================

    /**
     * GET /api/accounts-receivable
     * List all AR invoices with pagination and filtering
     * 
     * Query Parameters:
     * - client_id: Filter by client
     * - status: Filter by status (pending/partial/paid/overdue/cancelled)
     * - date_from: Filter by invoice date (from)
     * - date_to: Filter by invoice date (to)
     * - page: Page number for pagination
     * - limit: Items per page (default: 50)
     * 
     * @return ResponseInterface JSON response
     */
    public function index(): ResponseInterface
    {
        try {
            // Check user permission
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Insufficient permissions.'
                ], 403);
            }

            // Get query parameters for filtering
            $clientId = $this->request->getGet('client_id');
            $status = $this->request->getGet('status');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $page = (int) ($this->request->getGet('page') ?? 1);
            $limit = (int) ($this->request->getGet('limit') ?? 50);

            // Build filters
            $filters = [];
            if ($clientId) $filters['client_id'] = $clientId;
            if ($status) $filters['status'] = $status;
            if ($dateFrom) $filters['date_from'] = $dateFrom;
            if ($dateTo) $filters['date_to'] = $dateTo;

            // Get invoices with client details
            $invoices = $this->arModel->getInvoicesWithClients($filters);

            // Apply pagination
            $totalItems = count($invoices);
            $offset = ($page - 1) * $limit;
            $invoices = array_slice($invoices, $offset, $limit);

            // Get statistics
            $stats = $this->arModel->getStatistics();

            return $this->respond([
                'status' => 'success',
                'message' => 'AR invoices retrieved successfully',
                'data' => $invoices,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_items' => $totalItems,
                    'total_pages' => ceil($totalItems / $limit)
                ],
                'statistics' => $stats
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR index error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AR invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounts-receivable/{id}
     * Show a specific AR invoice by ID with payment history
     * 
     * @param int $id Invoice ID
     * @return ResponseInterface JSON response
     */
    public function show($id): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $invoice = $this->arModel->getInvoiceWithClient($id);

            if (!$invoice) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'AR invoice not found',
                    'invoice_id' => $id
                ], 404);
            }

            // Get payment history
            $payments = $this->paymentModel->getPaymentsForInvoice($id);

            // Calculate additional info
            $invoice['payment_history'] = $payments;
            $invoice['payment_count'] = count($payments);
            $invoice['days_overdue'] = max(0, (time() - strtotime($invoice['due_date'])) / (60 * 60 * 24));
            $invoice['is_overdue'] = $invoice['days_overdue'] > 0 && $invoice['balance'] > 0;

            return $this->respond([
                'status' => 'success',
                'message' => 'AR invoice retrieved successfully',
                'data' => $invoice
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR show error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve AR invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounts-receivable
     * Create a new AR invoice
     * 
     * @return ResponseInterface JSON response
     */
    public function store(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied. Only AR clerks can create invoices.'
                ], 403);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            if (empty($input)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided'
                ], 400);
            }

            // Prepare data
            $data = [
                'invoice_number' => $input['invoice_number'] ?? 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                'client_id' => $input['client_id'] ?? null,
                'invoice_date' => $input['invoice_date'] ?? date('Y-m-d'),
                'due_date' => $input['due_date'] ?? null,
                'invoice_amount' => $input['invoice_amount'] ?? 0,
                'description' => $input['description'] ?? '',
                'payment_method' => $input['payment_method'] ?? null,
                'payment_reference' => $input['payment_reference'] ?? null,
                'warehouse_id' => $input['warehouse_id'] ?? null,
                'created_by' => session()->get('user_id')
            ];

            // Validate client exists
            if (!$this->clientModel->find($data['client_id'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Client not found',
                    'client_id' => $data['client_id']
                ], 404);
            }

            // Save to database
            if ($this->arModel->save($data)) {
                $insertId = $this->arModel->getInsertID();
                $createdInvoice = $this->arModel->find($insertId);

                log_message('info', "AR invoice created: ID={$insertId}, Invoice={$data['invoice_number']} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'AR invoice created successfully',
                    'data' => $createdInvoice
                ], 201);

            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to create AR invoice',
                    'errors' => $this->arModel->errors()
                ], 400);
            }

        } catch (Exception $e) {
            log_message('error', 'AR store error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to create AR invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT/PATCH /api/accounts-receivable/{id}
     * Update an existing AR invoice
     * 
     * @param int $id Invoice ID
     * @return ResponseInterface JSON response
     */
    public function update($id = null): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $existingInvoice = $this->arModel->find($id);
            if (!$existingInvoice) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'AR invoice not found',
                    'invoice_id' => $id
                ], 404);
            }

            // Prevent editing paid or cancelled invoices
            if (in_array($existingInvoice['status'], ['paid', 'cancelled'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => "Cannot edit invoice with status: {$existingInvoice['status']}"
                ], 409);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            if (empty($input)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided for update'
                ], 400);
            }

            // Prepare update data
            $data = [];
            $allowedFields = ['invoice_number', 'client_id', 'invoice_date', 'due_date', 
                            'invoice_amount', 'description', 'payment_method', 
                            'payment_reference', 'warehouse_id'];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $data[$field] = $input[$field];
                }
            }

            if ($this->arModel->update($id, $data)) {
                $updatedInvoice = $this->arModel->find($id);

                log_message('info', "AR invoice updated: ID={$id} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'AR invoice updated successfully',
                    'data' => $updatedInvoice
                ], 200);

            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to update AR invoice',
                    'errors' => $this->arModel->errors()
                ], 400);
            }

        } catch (Exception $e) {
            log_message('error', 'AR update error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to update AR invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/accounts-receivable/{id}
     * Cancel an AR invoice (soft delete)
     * 
     * @param int $id Invoice ID
     * @return ResponseInterface JSON response
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'it_administrator'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $invoice = $this->arModel->find($id);
            if (!$invoice) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'AR invoice not found',
                    'invoice_id' => $id
                ], 404);
            }

            // Check if invoice has payments
            if ($invoice['received_amount'] > 0) {
                // Cancel instead of delete if payments exist
                if ($this->arModel->cancelInvoice($id)) {
                    log_message('info', "AR invoice cancelled: ID={$id} by User=" . session()->get('user_id'));

                    return $this->respond([
                        'status' => 'success',
                        'message' => 'Invoice cancelled (has payment history)',
                        'invoice_id' => $id
                    ], 200);
                }
            } else {
                // Soft delete if no payments
                if ($this->arModel->delete($id)) {
                    log_message('info', "AR invoice deleted: ID={$id} by User=" . session()->get('user_id'));

                    return $this->respond([
                        'status' => 'success',
                        'message' => 'AR invoice deleted successfully',
                        'deleted_invoice' => [
                            'id' => $id,
                            'invoice_number' => $invoice['invoice_number']
                        ]
                    ], 200);
                }
            }

            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to delete AR invoice'
            ], 500);

        } catch (Exception $e) {
            log_message('error', 'AR delete error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to delete AR invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/accounts-receivable/{id}/payment
     * Record a payment for an AR invoice
     * 
     * @param int $id Invoice ID
     * @return ResponseInterface JSON response
     */
    public function recordPayment($id = null): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $invoice = $this->arModel->find($id);
            if (!$invoice) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'AR invoice not found'
                ], 404);
            }

            if ($invoice['status'] === 'paid') {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invoice is already fully paid'
                ], 409);
            }

            if ($invoice['status'] === 'cancelled') {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Cannot record payment for cancelled invoice'
                ], 409);
            }

            $input = $this->request->getJSON(true) ?? $this->request->getPost();

            $paymentData = [
                'ar_id' => $id,
                'payment_date' => $input['payment_date'] ?? date('Y-m-d'),
                'amount' => $input['amount'] ?? 0,
                'payment_method' => $input['payment_method'] ?? 'cash',
                'reference_number' => $input['reference_number'] ?? null,
                'notes' => $input['notes'] ?? '',
                'processed_by' => session()->get('user_id')
            ];

            // Validate payment amount
            if ($paymentData['amount'] > $invoice['balance']) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Payment amount exceeds outstanding balance',
                    'balance' => $invoice['balance'],
                    'payment_amount' => $paymentData['amount']
                ], 400);
            }

            $paymentId = $this->paymentModel->recordPayment($paymentData);

            if ($paymentId) {
                $updatedInvoice = $this->arModel->find($id);

                log_message('info', "Payment recorded: AR#{$id}, Amount={$paymentData['amount']} by User=" . session()->get('user_id'));

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Payment recorded successfully',
                    'data' => [
                        'payment_id' => $paymentId,
                        'invoice' => $updatedInvoice
                    ]
                ], 201);
            } else {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to record payment',
                    'errors' => $this->paymentModel->errors()
                ], 400);
            }

        } catch (Exception $e) {
            log_message('error', 'Payment recording error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to record payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounts-receivable/overdue
     * Get overdue invoices report
     * 
     * @return ResponseInterface JSON response
     */
    public function getOverdue(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $overdueInvoices = $this->arModel->getOverdueInvoices();

            $totalOverdue = 0;
            foreach ($overdueInvoices as $invoice) {
                $totalOverdue += $invoice['balance'];
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Overdue invoices retrieved successfully',
                'data' => $overdueInvoices,
                'summary' => [
                    'count' => count($overdueInvoices),
                    'total_amount' => $totalOverdue
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Overdue report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve overdue invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounts-receivable/outstanding
     * Get outstanding balance report
     * 
     * @return ResponseInterface JSON response
     */
    public function getOutstanding(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $pendingInvoices = $this->arModel->getPendingInvoices();
            $totalOutstanding = $this->arModel->getTotalOutstanding();
            $agingReport = $this->arModel->getAgingReport();

            return $this->respond([
                'status' => 'success',
                'message' => 'Outstanding balance report retrieved successfully',
                'data' => [
                    'invoices' => $pendingInvoices,
                    'total_outstanding' => $totalOutstanding,
                    'aging_report' => $agingReport
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Outstanding report error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve outstanding balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounts-receivable/stats
     * Get AR statistics
     * 
     * @return ResponseInterface JSON response
     */
    public function getStats(): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $arStats = $this->arModel->getStatistics();
            $paymentStats = $this->paymentModel->getStatistics();
            $agingReport = $this->arModel->getAgingReport();

            return $this->respond([
                'status' => 'success',
                'message' => 'AR statistics retrieved successfully',
                'data' => [
                    'invoices' => $arStats,
                    'payments' => $paymentStats,
                    'aging' => $agingReport
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'AR stats error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/accounts-receivable/{id}/history
     * Get payment history for an invoice
     * 
     * @param int $id Invoice ID
     * @return ResponseInterface JSON response
     */
    public function getPaymentHistory($id): ResponseInterface
    {
        try {
            if (!$this->checkPermission(['accounts_receivable_clerk', 'top_management'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Access denied'
                ], 403);
            }

            $invoice = $this->arModel->find($id);
            if (!$invoice) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'AR invoice not found'
                ], 404);
            }

            $payments = $this->paymentModel->getPaymentsForInvoice($id);

            return $this->respond([
                'status' => 'success',
                'message' => 'Payment history retrieved successfully',
                'data' => [
                    'invoice' => $invoice,
                    'payments' => $payments,
                    'payment_count' => count($payments)
                ]
            ], 200);

        } catch (Exception $e) {
            log_message('error', 'Payment history error: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve payment history',
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

    // ========================================
    // WEB VIEW METHODS
    // ========================================

    /**
     * Display AR Clerk dashboard
     * 
     * @return string
     */
    public function indexView()
    {
        if (!$this->checkPermission(['accounts_receivable_clerk'])) {
            return redirect()->to('/');
        }
        
        return view('dashboard/accounts_receivable/index');
    }

    /**
     * Display create invoice form
     * 
     * @return string
     */
    public function createView()
    {
        if (!$this->checkPermission(['accounts_receivable_clerk'])) {
            return redirect()->to('/');
        }
        
        return view('dashboard/accounts_receivable/create');
    }

    /**
     * Display payment recording form
     * 
     * @return string
     */
    public function paymentsView()
    {
        if (!$this->checkPermission(['accounts_receivable_clerk'])) {
            return redirect()->to('/');
        }
        
        return view('dashboard/accounts_receivable/payments');
    }

    /**
     * Display reports page
     * 
     * @return string
     */
    public function reportsView()
    {
        if (!$this->checkPermission(['accounts_receivable_clerk'])) {
            return redirect()->to('/');
        }
        
        return view('dashboard/accounts_receivable/reports');
    }
}

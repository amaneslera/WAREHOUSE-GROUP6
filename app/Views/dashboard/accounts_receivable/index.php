<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Receivable Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .stat-card.total { border-left-color: #0d6efd; }
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.overdue { border-left-color: #dc3545; }
        .stat-card.collected { border-left-color: #198754; }
        .badge-status {
            padding: 0.35em 0.65em;
            font-size: 0.85em;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-receipt text-primary"></i> Accounts Receivable Dashboard</h2>
        <div>
            <span class="me-3"><i class="fas fa-user"></i> <?= session('user_fname') . ' ' . session('user_lname') ?></span>
            <span class="badge bg-success me-3"><?= session('user_role') ?></span>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 text-center text-white mb-3">
                <h5><i class="fas fa-bars"></i> Navigation</h5>
            </div>
            <a href="<?= site_url('dashboard/arclerk') ?>" class="active">
                <i class="fas fa-file-invoice"></i> Invoices
            </a>
            <a href="<?= site_url('arclerk/create-invoice') ?>">
                <i class="fas fa-plus-circle"></i> New Invoice
            </a>
            <a href="<?= site_url('arclerk/payments') ?>">
                <i class="fas fa-cash-register"></i> Record Payment
            </a>
            <a href="<?= site_url('arclerk/reports') ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <hr class="bg-secondary">
            <a href="<?= site_url('logout') ?>" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="col-md-10 p-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2><i class="bi bi-speedometer2"></i> Accounts Receivable Dashboard</h2>
                    <p class="text-muted">Issues billing to clients, records payments received, and follows up on unpaid dues.</p>
                </div>
            </div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3">
                <div class="card stat-card total">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Outstanding</h6>
                                <h3 class="mb-0" id="totalOutstanding">₱0.00</h3>
                            </div>
                            <i class="bi bi-cash-stack fs-1 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card pending">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Pending Invoices</h6>
                                <h3 class="mb-0" id="pendingCount">0</h3>
                            </div>
                            <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card overdue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Overdue Invoices</h6>
                                <h3 class="mb-0" id="overdueCount">0</h3>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card collected">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Collected Today</h6>
                                <h3 class="mb-0" id="collectedToday">₱0.00</h3>
                            </div>
                            <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client</label>
                        <select class="form-select" id="filterClient">
                            <option value="">All Clients</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="loadInvoices()">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Invoices</h5>
                <a href="<?= site_url('arclerk/create-invoice') ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> New Invoice
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTableBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav id="pagination" class="mt-3"></nav>
            </div>
        </div>
        </div>
    </div>
</div>

    <!-- Invoice Detail Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="invoiceDetailBody">
                    <!-- Invoice details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?= site_url() ?>';
        let currentPage = 1;

        // Load statistics
        async function loadStats() {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/stats`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const stats = result.data.invoices;
                    document.getElementById('totalOutstanding').textContent = 
                        '₱' + parseFloat(stats.total_outstanding || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('pendingCount').textContent = 
                        (stats.pending_count || 0) + (stats.partial_count || 0);
                    document.getElementById('overdueCount').textContent = stats.overdue_count || 0;
                    
                    const paymentStats = result.data.payments;
                    document.getElementById('collectedToday').textContent = 
                        '₱' + parseFloat(paymentStats.amount_today || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load invoices
        async function loadInvoices(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('invoicesTableBody');
            tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary"></div></td></tr>';

            const params = new URLSearchParams({
                page: page,
                limit: 20
            });

            const status = document.getElementById('filterStatus').value;
            const client = document.getElementById('filterClient').value;
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;

            if (status) params.append('status', status);
            if (client) params.append('client_id', client);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable?${params}`);
                const result = await response.json();

                if (result.status === 'success') {
                    displayInvoices(result.data);
                    displayPagination(result.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${result.message}</td></tr>`;
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading invoices</td></tr>';
            }
        }

        function displayInvoices(invoices) {
            const tbody = document.getElementById('invoicesTableBody');
            
            if (invoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No invoices found</td></tr>';
                return;
            }

            tbody.innerHTML = invoices.map(invoice => `
                <tr onclick="viewInvoice(${invoice.id})">
                    <td><strong>${invoice.invoice_number}</strong></td>
                    <td>${invoice.client_name || 'N/A'}</td>
                    <td>${formatDate(invoice.invoice_date)}</td>
                    <td>${formatDate(invoice.due_date)}</td>
                    <td>₱${parseFloat(invoice.invoice_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td>₱${parseFloat(invoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td>${getStatusBadge(invoice.status)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewInvoice(${invoice.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${invoice.status !== 'paid' && invoice.status !== 'cancelled' ? `
                        <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); recordPayment(${invoice.id})">
                            <i class="bi bi-cash"></i>
                        </button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge badge-status bg-warning text-dark">Pending</span>',
                'partial': '<span class="badge badge-status bg-info">Partial</span>',
                'paid': '<span class="badge badge-status bg-success">Paid</span>',
                'overdue': '<span class="badge badge-status bg-danger">Overdue</span>',
                'cancelled': '<span class="badge badge-status bg-secondary">Cancelled</span>'
            };
            return badges[status] || status;
        }

        function displayPagination(pagination) {
            const nav = document.getElementById('pagination');
            if (pagination.total_pages <= 1) {
                nav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination justify-content-center">';
            
            for (let i = 1; i <= pagination.total_pages; i++) {
                html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadInvoices(${i}); return false;">${i}</a>
                </li>`;
            }
            
            html += '</ul>';
            nav.innerHTML = html;
        }

        async function viewInvoice(id) {
            const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
            const modalBody = document.getElementById('invoiceDetailBody');
            
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
            modal.show();

            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/${id}`);
                const result = await response.json();

                if (result.status === 'success') {
                    const invoice = result.data;
                    modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Invoice #:</strong> ${invoice.invoice_number}</p>
                                <p><strong>Client:</strong> ${invoice.client_name}</p>
                                <p><strong>Status:</strong> ${getStatusBadge(invoice.status)}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Invoice Date:</strong> ${formatDate(invoice.invoice_date)}</p>
                                <p><strong>Due Date:</strong> ${formatDate(invoice.due_date)}</p>
                                <p><strong>Days Overdue:</strong> ${Math.floor(invoice.days_overdue || 0)}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Invoice Amount:</strong> ₱${parseFloat(invoice.invoice_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                                <p><strong>Received:</strong> ₱${parseFloat(invoice.received_amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                                <p><strong>Balance:</strong> ₱${parseFloat(invoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Description:</strong> ${invoice.description || 'N/A'}</p>
                                <p><strong>Payment Method:</strong> ${invoice.payment_method || 'N/A'}</p>
                            </div>
                        </div>
                        <hr>
                        <h6>Payment History (${invoice.payment_count || 0} payments)</h6>
                        ${invoice.payment_history && invoice.payment_history.length > 0 ? `
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${invoice.payment_history.map(p => `
                                        <tr>
                                            <td>${formatDate(p.payment_date)}</td>
                                            <td>₱${parseFloat(p.amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                            <td>${p.payment_method}</td>
                                            <td>${p.reference_number || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : '<p class="text-muted">No payments recorded yet.</p>'}
                    `;
                }
            } catch (error) {
                console.error('Error loading invoice details:', error);
                modalBody.innerHTML = '<div class="alert alert-danger">Error loading invoice details</div>';
            }
        }

        function recordPayment(id) {
            window.location.href = `${baseUrl}/arclerk/payments?invoice_id=${id}`;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-PH');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadInvoices();
        });
    </script>
</body>
</html>

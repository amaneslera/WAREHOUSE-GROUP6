<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Reports - Overdue & Aging</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; border-left-color: #3498db; color: #fff; }
        .top-navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .aging-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .aging-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .aging-current { border-left-color: #198754; }
        .aging-30 { border-left-color: #ffc107; }
        .aging-60 { border-left-color: #fd7e14; }
        .aging-90 { border-left-color: #dc3545; }
        .overdue-row {
            background-color: #fff3cd;
        }
        .critical-overdue {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
<div class="top-navbar bg-white py-3 px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-receipt text-primary"></i> Accounts Receivable Reports</h2>
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
            <a href="<?= site_url('dashboard/arclerk') ?>">
                <i class="fas fa-file-invoice"></i> Invoices
            </a>
            <a href="<?= site_url('arclerk/create-invoice') ?>">
                <i class="fas fa-plus-circle"></i> New Invoice
            </a>
            <a href="<?= site_url('arclerk/payments') ?>">
                <i class="fas fa-cash-register"></i> Record Payment
            </a>
            <a href="<?= site_url('arclerk/reports') ?>" class="active">
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
                    <h2><i class="bi bi-graph-up"></i> Accounts Receivable Reports</h2>
                    <p class="text-muted">Follow up on unpaid dues and analyze aging receivables</p>
                </div>
            </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button">
                    <i class="bi bi-exclamation-triangle"></i> Overdue Invoices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="aging-tab" data-bs-toggle="tab" data-bs-target="#aging" type="button">
                    <i class="bi bi-calendar-range"></i> Aging Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="outstanding-tab" data-bs-toggle="tab" data-bs-target="#outstanding" type="button">
                    <i class="bi bi-cash-stack"></i> Outstanding Balance
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <!-- Overdue Invoices Tab -->
            <div class="tab-pane fade show active" id="overdue" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Total Overdue Amount</h6>
                                <h3 id="totalOverdueAmount">₱0.00</h3>
                                <small id="overdueInvoiceCount">0 invoices</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Average Days Overdue</h6>
                                <h3 id="avgDaysOverdue">0</h3>
                                <small>days past due date</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-danger"></i> Overdue Invoices Requiring Follow-Up</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Balance</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="overdueTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aging Analysis Tab -->
            <div class="tab-pane fade" id="aging" role="tabpanel">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card aging-card aging-current">
                            <div class="card-body">
                                <h6 class="text-muted">Current (0-30 days)</h6>
                                <h4 id="aging_current_amount">₱0.00</h4>
                                <small id="aging_current_count">0 invoices</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card aging-card aging-30">
                            <div class="card-body">
                                <h6 class="text-muted">31-60 days</h6>
                                <h4 id="aging_30_amount">₱0.00</h4>
                                <small id="aging_30_count">0 invoices</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card aging-card aging-60">
                            <div class="card-body">
                                <h6 class="text-muted">61-90 days</h6>
                                <h4 id="aging_60_amount">₱0.00</h4>
                                <small id="aging_60_count">0 invoices</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card aging-card aging-90">
                            <div class="card-body">
                                <h6 class="text-muted">90+ days</h6>
                                <h4 id="aging_90_amount">₱0.00</h4>
                                <small id="aging_90_count">0 invoices</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Aging Analysis Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Receivables are categorized by how many days they are past the due date.
                            This helps prioritize collection efforts.
                        </div>
                        <canvas id="agingChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance Tab -->
            <div class="tab-pane fade" id="outstanding" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Outstanding</h6>
                                <h3 id="totalOutstandingAmount">₱0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Pending Invoices</h6>
                                <h3 id="pendingInvoicesCount">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Collection Rate</h6>
                                <h3 id="collectionRate">0%</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Outstanding Balance by Client</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Total Invoices</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Outstanding</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="outstandingTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="spinner-border text-primary"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const baseUrl = '<?= site_url() ?>';
        let agingChartInstance = null;

        // Load overdue invoices
        async function loadOverdueInvoices() {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/overdue`);
                const result = await response.json();

                if (result.status === 'success') {
                    const invoices = result.data;
                    const summary = result.summary;

                    // Update summary cards
                    document.getElementById('totalOverdueAmount').textContent = 
                        '₱' + parseFloat(summary.total_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('overdueInvoiceCount').textContent = 
                        `${summary.count || 0} invoices`;

                    // Calculate average days overdue
                    let totalDays = 0;
                    invoices.forEach(inv => {
                        const daysOverdue = Math.floor((new Date() - new Date(inv.due_date)) / (1000 * 60 * 60 * 24));
                        totalDays += daysOverdue;
                    });
                    const avgDays = invoices.length > 0 ? Math.floor(totalDays / invoices.length) : 0;
                    document.getElementById('avgDaysOverdue').textContent = avgDays;

                    // Display table
                    displayOverdueTable(invoices);
                }
            } catch (error) {
                console.error('Error loading overdue invoices:', error);
            }
        }

        function displayOverdueTable(invoices) {
            const tbody = document.getElementById('overdueTableBody');

            if (invoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-success">No overdue invoices! 🎉</td></tr>';
                return;
            }

            // Sort by days overdue (descending)
            invoices.sort((a, b) => {
                const daysA = Math.floor((new Date() - new Date(a.due_date)) / (1000 * 60 * 60 * 24));
                const daysB = Math.floor((new Date() - new Date(b.due_date)) / (1000 * 60 * 60 * 24));
                return daysB - daysA;
            });

            tbody.innerHTML = invoices.map(invoice => {
                const daysOverdue = Math.floor((new Date() - new Date(invoice.due_date)) / (1000 * 60 * 60 * 24));
                const isCritical = daysOverdue > 60;
                const rowClass = isCritical ? 'critical-overdue' : 'overdue-row';
                const priority = daysOverdue > 90 ? 'URGENT' : daysOverdue > 60 ? 'HIGH' : daysOverdue > 30 ? 'MEDIUM' : 'LOW';
                const priorityClass = daysOverdue > 90 ? 'danger' : daysOverdue > 60 ? 'warning' : 'info';

                return `
                    <tr class="${rowClass}">
                        <td><strong>${invoice.invoice_number}</strong></td>
                        <td>${invoice.client_name || 'N/A'}</td>
                        <td>${formatDate(invoice.due_date)}</td>
                        <td><span class="badge bg-${priorityClass}">${daysOverdue} days</span></td>
                        <td class="fw-bold">₱${parseFloat(invoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                        <td><span class="badge bg-${priorityClass}">${priority}</span></td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="followUp(${invoice.id})">
                                <i class="bi bi-telephone"></i> Follow Up
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Load aging analysis
        async function loadAgingAnalysis() {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/stats`);
                const result = await response.json();

                if (result.status === 'success') {
                    const aging = result.data.aging;

                    // Update aging cards
                    document.getElementById('aging_current_amount').textContent = 
                        '₱' + parseFloat(aging.current.amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('aging_current_count').textContent = 
                        `${aging.current.count || 0} invoices`;

                    document.getElementById('aging_30_amount').textContent = 
                        '₱' + parseFloat(aging['30_days'].amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('aging_30_count').textContent = 
                        `${aging['30_days'].count || 0} invoices`;

                    document.getElementById('aging_60_amount').textContent = 
                        '₱' + parseFloat(aging['60_days'].amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('aging_60_count').textContent = 
                        `${aging['60_days'].count || 0} invoices`;

                    document.getElementById('aging_90_amount').textContent = 
                        '₱' + parseFloat(aging['90_plus'].amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('aging_90_count').textContent = 
                        `${aging['90_plus'].count || 0} invoices`;

                    // Create chart
                    createAgingChart(aging);
                }
            } catch (error) {
                console.error('Error loading aging analysis:', error);
            }
        }

        function createAgingChart(aging) {
            const ctx = document.getElementById('agingChart').getContext('2d');
            
            if (agingChartInstance) {
                agingChartInstance.destroy();
            }

            agingChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['0-30 days', '31-60 days', '61-90 days', '90+ days'],
                    datasets: [{
                        label: 'Outstanding Amount (₱)',
                        data: [
                            aging.current.amount || 0,
                            aging['30_days'].amount || 0,
                            aging['60_days'].amount || 0,
                            aging['90_plus'].amount || 0
                        ],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.6)',
                            'rgba(255, 193, 7, 0.6)',
                            'rgba(253, 126, 20, 0.6)',
                            'rgba(220, 53, 69, 0.6)'
                        ],
                        borderColor: [
                            'rgb(25, 135, 84)',
                            'rgb(255, 193, 7)',
                            'rgb(253, 126, 20)',
                            'rgb(220, 53, 69)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Load outstanding balance
        async function loadOutstanding() {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/outstanding`);
                const result = await response.json();

                if (result.status === 'success') {
                    const data = result.data;

                    // Update summary
                    document.getElementById('totalOutstandingAmount').textContent = 
                        '₱' + parseFloat(data.total_outstanding || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    
                    const pendingCount = data.invoices.filter(inv => 
                        ['pending', 'partial', 'overdue'].includes(inv.status)
                    ).length;
                    document.getElementById('pendingInvoicesCount').textContent = pendingCount;

                    // Calculate collection rate
                    const statsResponse = await fetch(`${baseUrl}/api/accounts-receivable/stats`);
                    const statsResult = await statsResponse.json();
                    if (statsResult.status === 'success') {
                        const stats = statsResult.data.invoices;
                        const collectionRate = stats.total_amount > 0 
                            ? ((stats.total_received / stats.total_amount) * 100).toFixed(1)
                            : 0;
                        document.getElementById('collectionRate').textContent = `${collectionRate}%`;
                    }

                    // Display outstanding by client
                    displayOutstandingByClient(data.invoices);
                }
            } catch (error) {
                console.error('Error loading outstanding balance:', error);
            }
        }

        function displayOutstandingByClient(invoices) {
            const tbody = document.getElementById('outstandingTableBody');

            // Group by client
            const clientMap = {};
            invoices.forEach(inv => {
                const clientName = inv.client_name || 'Unknown';
                if (!clientMap[clientName]) {
                    clientMap[clientName] = {
                        count: 0,
                        totalAmount: 0,
                        paidAmount: 0,
                        outstanding: 0
                    };
                }
                clientMap[clientName].count++;
                clientMap[clientName].totalAmount += parseFloat(inv.invoice_amount || 0);
                clientMap[clientName].paidAmount += parseFloat(inv.received_amount || 0);
                clientMap[clientName].outstanding += parseFloat(inv.balance || 0);
            });

            if (Object.keys(clientMap).length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No outstanding balance</td></tr>';
                return;
            }

            tbody.innerHTML = Object.entries(clientMap).map(([client, data]) => {
                const hasOverdue = data.outstanding > 0;
                const statusBadge = hasOverdue 
                    ? '<span class="badge bg-warning">Outstanding</span>' 
                    : '<span class="badge bg-success">Clear</span>';

                return `
                    <tr>
                        <td><strong>${client}</strong></td>
                        <td>${data.count}</td>
                        <td>₱${data.totalAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                        <td>₱${data.paidAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                        <td class="fw-bold">₱${data.outstanding.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        }

        function followUp(invoiceId) {
            if (confirm('This will mark the invoice as followed up and you can record the outcome. Continue?')) {
                window.location.href = `${baseUrl}/arclerk/payments?invoice_id=${invoiceId}`;
            }
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-PH');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadOverdueInvoices();
            loadAgingAnalysis();
            loadOutstanding();
        });
    </script>
</body>
</html>

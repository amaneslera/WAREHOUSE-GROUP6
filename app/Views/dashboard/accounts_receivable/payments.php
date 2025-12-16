<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - AR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= site_url('dashboard/arclerk') ?>">
                <i class="bi bi-receipt"></i> Accounts Receivable
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('arclerk/invoices') ?>">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('arclerk/create-invoice') ?>">New Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= site_url('arclerk/payments') ?>">Record Payment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('arclerk/reports') ?>">Reports</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i> 
                        <?= session('user_fname') . ' ' . session('user_lname') ?>
                    </span>
                    <a href="<?= site_url('logout') ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bi bi-cash-coin"></i> Record Payment</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Record a payment received from a client for their outstanding invoice.
                        </div>

                        <!-- Step 1: Select Invoice -->
                        <div id="step1">
                            <h5 class="mb-3">Step 1: Select Invoice</h5>
                            <div class="mb-3">
                                <label for="searchInvoice" class="form-label">Search Invoice</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInvoice" 
                                           placeholder="Search by invoice number or client name">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Client</th>
                                            <th>Due Date</th>
                                            <th>Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoicesTable">
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <div class="spinner-border text-primary"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Step 2: Payment Details -->
                        <div id="step2" style="display: none;">
                            <h5 class="mb-3">Step 2: Payment Details</h5>
                            
                            <div class="alert alert-light border">
                                <h6>Invoice Information</h6>
                                <div class="row">
                                    <div class="col-6"><strong>Invoice #:</strong></div>
                                    <div class="col-6" id="selectedInvoiceNumber">-</div>
                                    <div class="col-6"><strong>Client:</strong></div>
                                    <div class="col-6" id="selectedClient">-</div>
                                    <div class="col-6"><strong>Invoice Amount:</strong></div>
                                    <div class="col-6" id="selectedAmount">₱0.00</div>
                                    <div class="col-6"><strong>Outstanding Balance:</strong></div>
                                    <div class="col-6" id="selectedBalance" class="text-danger fw-bold">₱0.00</div>
                                </div>
                            </div>

                            <form id="paymentForm">
                                <input type="hidden" id="invoice_id" name="invoice_id">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="payment_amount" name="amount" 
                                                   step="0.01" min="0.01" required>
                                        </div>
                                        <small class="text-muted">Maximum: <span id="maxAmount">₱0.00</span></small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="">Select Method</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="check">Check</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="debit_card">Debit Card</option>
                                            <option value="online">Online Payment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reference_number" class="form-label">Reference Number</label>
                                        <input type="text" class="form-control" id="reference_number" name="reference_number" 
                                               placeholder="e.g., Check #, Transaction ID">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2" 
                                              placeholder="Additional notes about this payment"></textarea>
                                </div>

                                <div class="alert alert-success" id="balancePreview" style="display: none;">
                                    <strong>New Balance After Payment:</strong> <span id="newBalance">₱0.00</span>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                    <button type="button" class="btn btn-secondary" onclick="backToStep1()">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="bi bi-check-circle"></i> Record Payment
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="alert alert-success mt-3" id="successAlert" style="display: none;">
                            <i class="bi bi-check-circle"></i> <span id="successMessage"></span>
                        </div>

                        <div class="alert alert-danger mt-3" id="errorAlert" style="display: none;">
                            <i class="bi bi-exclamation-triangle"></i> <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?= site_url() ?>';
        let selectedInvoice = null;
        let allInvoices = [];

        // Load pending/partial invoices
        async function loadInvoices() {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable?status=pending`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    allInvoices = result.data;
                    displayInvoices(allInvoices);
                    
                    // Also load partial payments
                    const partialResponse = await fetch(`${baseUrl}/api/accounts-receivable?status=partial`);
                    const partialResult = await partialResponse.json();
                    if (partialResult.status === 'success') {
                        allInvoices = [...allInvoices, ...partialResult.data];
                        displayInvoices(allInvoices);
                    }

                    // Load overdue
                    const overdueResponse = await fetch(`${baseUrl}/api/accounts-receivable?status=overdue`);
                    const overdueResult = await overdueResponse.json();
                    if (overdueResult.status === 'success') {
                        allInvoices = [...allInvoices, ...overdueResult.data];
                        displayInvoices(allInvoices);
                    }
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
                document.getElementById('invoicesTable').innerHTML = 
                    '<tr><td colspan="5" class="text-center text-danger">Error loading invoices</td></tr>';
            }
        }

        function displayInvoices(invoices) {
            const tbody = document.getElementById('invoicesTable');
            
            if (invoices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No pending invoices</td></tr>';
                return;
            }

            tbody.innerHTML = invoices.map(invoice => `
                <tr>
                    <td><strong>${invoice.invoice_number}</strong></td>
                    <td>${invoice.client_name || 'N/A'}</td>
                    <td>${formatDate(invoice.due_date)}</td>
                    <td class="fw-bold">₱${parseFloat(invoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="selectInvoice(${invoice.id})">
                            <i class="bi bi-cash"></i> Pay
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Search functionality
        document.getElementById('searchInvoice').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filtered = allInvoices.filter(invoice => 
                invoice.invoice_number.toLowerCase().includes(searchTerm) ||
                (invoice.client_name && invoice.client_name.toLowerCase().includes(searchTerm))
            );
            displayInvoices(filtered);
        });

        // Select invoice
        async function selectInvoice(id) {
            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/${id}`);
                const result = await response.json();

                if (result.status === 'success') {
                    selectedInvoice = result.data;
                    
                    // Fill invoice details
                    document.getElementById('invoice_id').value = selectedInvoice.id;
                    document.getElementById('selectedInvoiceNumber').textContent = selectedInvoice.invoice_number;
                    document.getElementById('selectedClient').textContent = selectedInvoice.client_name;
                    document.getElementById('selectedAmount').textContent = 
                        '₱' + parseFloat(selectedInvoice.invoice_amount).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('selectedBalance').textContent = 
                        '₱' + parseFloat(selectedInvoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    document.getElementById('maxAmount').textContent = 
                        '₱' + parseFloat(selectedInvoice.balance).toLocaleString('en-PH', {minimumFractionDigits: 2});
                    
                    // Set max amount
                    document.getElementById('payment_amount').max = selectedInvoice.balance;
                    document.getElementById('payment_amount').value = selectedInvoice.balance;
                    
                    // Show step 2
                    document.getElementById('step1').style.display = 'none';
                    document.getElementById('step2').style.display = 'block';
                    
                    updateBalancePreview();
                }
            } catch (error) {
                console.error('Error loading invoice:', error);
                alert('Error loading invoice details');
            }
        }

        function backToStep1() {
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
            document.getElementById('paymentForm').reset();
            selectedInvoice = null;
        }

        // Update balance preview
        document.getElementById('payment_amount').addEventListener('input', updateBalancePreview);

        function updateBalancePreview() {
            if (!selectedInvoice) return;
            
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
            const currentBalance = parseFloat(selectedInvoice.balance);
            const newBalance = Math.max(0, currentBalance - paymentAmount);
            
            document.getElementById('newBalance').textContent = 
                '₱' + newBalance.toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('balancePreview').style.display = 'block';
        }

        // Handle payment submission
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            // Validate payment amount
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value);
            const balance = parseFloat(selectedInvoice.balance);
            
            if (paymentAmount > balance) {
                errorAlert.style.display = 'block';
                document.getElementById('errorMessage').textContent = 
                    'Payment amount cannot exceed outstanding balance!';
                return;
            }

            if (paymentAmount <= 0) {
                errorAlert.style.display = 'block';
                document.getElementById('errorMessage').textContent = 
                    'Payment amount must be greater than zero!';
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            // Hide alerts
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';

            const paymentData = {
                payment_date: document.getElementById('payment_date').value,
                amount: paymentAmount,
                payment_method: document.getElementById('payment_method').value,
                reference_number: document.getElementById('reference_number').value || null,
                notes: document.getElementById('notes').value || ''
            };

            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable/${selectedInvoice.id}/payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(paymentData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    successAlert.style.display = 'block';
                    document.getElementById('successMessage').textContent = 
                        `Payment of ₱${paymentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})} recorded successfully!`;
                    
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = `${baseUrl}/arclerk/invoices`;
                    }, 2000);
                } else {
                    errorAlert.style.display = 'block';
                    document.getElementById('errorMessage').textContent = 
                        result.message || 'Failed to record payment. Please try again.';
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Record Payment';
                }
            } catch (error) {
                console.error('Error recording payment:', error);
                errorAlert.style.display = 'block';
                document.getElementById('errorMessage').textContent = 
                    'Network error. Please try again.';
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Record Payment';
            }
        });

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-PH');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Check if invoice_id is in URL params
            const urlParams = new URLSearchParams(window.location.search);
            const invoiceId = urlParams.get('invoice_id');
            
            loadInvoices().then(() => {
                if (invoiceId) {
                    selectInvoice(parseInt(invoiceId));
                }
            });
        });
    </script>
</body>
</html>

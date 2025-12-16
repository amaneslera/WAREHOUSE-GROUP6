<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice - AR</title>
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
                        <a class="nav-link active" href="<?= site_url('arclerk/create-invoice') ?>">New Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('arclerk/payments') ?>">Record Payment</a>
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
                        <h4 class="mb-0"><i class="bi bi-file-earmark-plus"></i> Create New Invoice</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Fill in the details below to issue a new billing to a client.
                        </div>

                        <form id="invoiceForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                                           value="INV-<?= date('Ymd') ?>-<?= rand(1000, 9999) ?>" required>
                                    <small class="text-muted">Unique invoice number</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select class="form-select" id="client_id" name="client_id" required>
                                        <option value="">Select Client</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" 
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" 
                                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="invoice_amount" class="form-label">Invoice Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Enter invoice description or details"></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Preferred Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="">Not specified</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="check">Check</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_reference" class="form-label">Payment Reference</label>
                                    <input type="text" class="form-control" id="payment_reference" name="payment_reference" 
                                           placeholder="e.g., PO number, contract reference">
                                </div>
                            </div>

                            <div class="alert alert-light border" id="summaryBox" style="display: none;">
                                <h6>Invoice Summary</h6>
                                <div class="row">
                                    <div class="col-6"><strong>Client:</strong></div>
                                    <div class="col-6" id="summaryClient">-</div>
                                    <div class="col-6"><strong>Invoice Amount:</strong></div>
                                    <div class="col-6" id="summaryAmount">₱0.00</div>
                                    <div class="col-6"><strong>Due Date:</strong></div>
                                    <div class="col-6" id="summaryDue">-</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= site_url('arclerk/invoices') ?>" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="bi bi-check-circle"></i> Create Invoice
                                </button>
                            </div>
                        </form>

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

        // Load clients
        async function loadClients() {
            try {
                const response = await fetch(`${baseUrl}/api/inventory`); // Temporary - should create clients API
                // For now, we'll add sample clients
                const clientSelect = document.getElementById('client_id');
                
                // Add sample clients (in production, fetch from clients API)
                const sampleClients = [
                    {id: 1, name: 'ABC Corporation'},
                    {id: 2, name: 'XYZ Enterprises'},
                    {id: 3, name: 'Global Trading Inc.'},
                    {id: 4, name: 'Metro Retail Co.'},
                    {id: 5, name: 'Prime Distribution'}
                ];

                sampleClients.forEach(client => {
                    const option = document.createElement('option');
                    option.value = client.id;
                    option.textContent = client.name;
                    clientSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading clients:', error);
            }
        }

        // Update summary when fields change
        document.getElementById('client_id').addEventListener('change', function() {
            const summaryBox = document.getElementById('summaryBox');
            if (this.value) {
                summaryBox.style.display = 'block';
                document.getElementById('summaryClient').textContent = 
                    this.options[this.selectedIndex].text;
            }
        });

        document.getElementById('invoice_amount').addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            document.getElementById('summaryAmount').textContent = 
                '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2});
        });

        document.getElementById('due_date').addEventListener('change', function() {
            document.getElementById('summaryDue').textContent = 
                new Date(this.value).toLocaleDateString('en-PH');
        });

        // Handle form submission
        document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';

            // Hide alerts
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';

            // Get form data
            const formData = {
                invoice_number: document.getElementById('invoice_number').value,
                client_id: parseInt(document.getElementById('client_id').value),
                invoice_date: document.getElementById('invoice_date').value,
                due_date: document.getElementById('due_date').value,
                invoice_amount: parseFloat(document.getElementById('invoice_amount').value),
                description: document.getElementById('description').value,
                payment_method: document.getElementById('payment_method').value || null,
                payment_reference: document.getElementById('payment_reference').value || null
            };

            try {
                const response = await fetch(`${baseUrl}/api/accounts-receivable`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    successAlert.style.display = 'block';
                    document.getElementById('successMessage').textContent = 
                        'Invoice created successfully! Invoice #: ' + result.data.invoice_number;
                    
                    // Reset form
                    document.getElementById('invoiceForm').reset();
                    document.getElementById('summaryBox').style.display = 'none';
                    
                    // Generate new invoice number
                    document.getElementById('invoice_number').value = 
                        'INV-<?= date("Ymd") ?>-' + Math.floor(Math.random() * 9000 + 1000);

                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = `${baseUrl}/arclerk/invoices`;
                    }, 2000);
                } else {
                    errorAlert.style.display = 'block';
                    document.getElementById('errorMessage').textContent = 
                        result.message || 'Failed to create invoice. Please check all fields.';
                }
            } catch (error) {
                console.error('Error creating invoice:', error);
                errorAlert.style.display = 'block';
                document.getElementById('errorMessage').textContent = 
                    'Network error. Please try again.';
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Create Invoice';
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadClients();
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Invoice Details</h2>
        <a href="<?= site_url('invoice-management') ?>" class="btn btn-secondary">Back to Invoices</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Invoice #<?= $invoice['invoice_number'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Supplier:</strong> <?= $invoice['vendor_name'] ?> (<?= $invoice['vendor_code'] ?>)</p>
                    <p><strong>Invoice Date:</strong> <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></p>
                    <p><strong>Due Date:</strong> <?= date('M d, Y', strtotime($invoice['due_date'])) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Amount:</strong> $<?= number_format($invoice['invoice_amount'], 2) ?></p>
                    <p><strong>Paid Amount:</strong> $<?= number_format($invoice['paid_amount'], 2) ?></p>
                    <p><strong>Balance:</strong> $<?= number_format($invoice['balance'], 2) ?></p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-<?= $this->getStatusColor($invoice['status']) ?>">
                            <?= ucfirst($invoice['status']) ?>
                        </span>
                    </p>
                </div>
            </div>

            <?php if ($invoice['description']): ?>
            <div class="mt-3">
                <strong>Description:</strong>
                <p><?= $invoice['description'] ?></p>
            </div>
            <?php endif; ?>

            <?php if ($invoice['payment_method']): ?>
            <div class="mt-3">
                <strong>Payment Method:</strong> <?= $invoice['payment_method'] ?>
                <?php if ($invoice['payment_reference']): ?>
                (Ref: <?= $invoice['payment_reference'] ?>)
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function getStatusColor(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'approved': return 'info';
        case 'paid': return 'success';
        case 'overdue': return 'danger';
        case 'partial': return 'secondary';
        default: return 'light';
    }
}
</script>
</body>
</html>

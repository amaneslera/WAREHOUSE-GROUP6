<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Invoice Management</h2>
        <a href="<?= site_url('dashboard/apclerk') ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5>Invoice List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr class="<?= ($invoice['status'] == 'overdue') ? 'table-danger' : '' ?>">
                            <td><?= $invoice['invoice_number'] ?></td>
                            <td><?= $invoice['vendor_name'] ?></td>
                            <td>$<?= number_format($invoice['invoice_amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($invoice['due_date'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $this->getStatusColor($invoice['status']) ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('invoice-management/view/' . $invoice['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if ($invoice['status'] == 'pending'): ?>
                                <a href="<?= site_url('invoice-management/approve/' . $invoice['id']) ?>" class="btn btn-sm btn-success">Approve</a>
                                <?php endif; ?>
                                <?php if (in_array($invoice['status'], ['pending', 'partial'])): ?>
                                <a href="<?= site_url('invoice-management/mark-paid/' . $invoice['id']) ?>" class="btn btn-sm btn-primary">Mark Paid</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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

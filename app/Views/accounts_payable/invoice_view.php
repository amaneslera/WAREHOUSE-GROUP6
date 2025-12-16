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

    <!-- Matching Information -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Document Matching</h5>
            <?php if (($invoice['matching_status'] ?? 'unmatched') === 'unmatched'): ?>
            <a href="<?= site_url('invoice-management/match/' . $invoice['id']) ?>" class="btn btn-primary btn-sm">Match Documents</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Matching Status:</strong>
                        <?php
                        $matchingStatus = $invoice['matching_status'] ?? 'unmatched';
                        $badgeClass = match($matchingStatus) {
                            'matched' => 'success',
                            'discrepancy' => 'danger',
                            'unmatched' => 'warning',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>">
                            <?= ucfirst($matchingStatus) ?>
                        </span>
                    </p>

                    <?php if (!empty($invoice['po_reference'])): ?>
                    <p><strong>PO Reference:</strong> <?= $invoice['po_reference'] ?></p>
                    <?php endif; ?>

                    <?php if (!empty($invoice['delivery_receipt'])): ?>
                    <p><strong>Delivery Receipt:</strong> <?= $invoice['delivery_receipt'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($invoice['matched_by_name'])): ?>
                    <p><strong>Matched By:</strong> <?= $invoice['matched_by_name'] ?></p>
                    <?php endif; ?>

                    <?php if (!empty($invoice['matched_at'])): ?>
                    <p><strong>Matched At:</strong> <?= date('M d, Y H:i', strtotime($invoice['matched_at'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($invoice['discrepancy_notes'])): ?>
            <div class="mt-3">
                <strong>Discrepancy Notes:</strong>
                <div class="alert alert-warning">
                    <?= nl2br($invoice['discrepancy_notes']) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($invoice['related_movements'])): ?>
            <div class="mt-3">
                <strong>Related Stock Movements:</strong>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item</th>
                                <th>Warehouse</th>
                                <th>Quantity</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice['related_movements'] as $movement): ?>
                            <tr>
                                <td><?= $movement['id'] ?></td>
                                <td><?= $movement['item_name'] ?></td>
                                <td><?= $movement['warehouse_name'] ?></td>
                                <td><?= $movement['quantity'] ?></td>
                                <td><?= date('M d, Y', strtotime($movement['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($invoice['status'] == 'pending'): ?>
                <a href="<?= site_url('invoice-management/approve/' . $invoice['id']) ?>" class="btn btn-success">Approve Invoice</a>
                <?php endif; ?>

                <?php if (in_array($invoice['status'], ['pending', 'partial'])): ?>
                <a href="<?= site_url('invoice-management/mark-paid/' . $invoice['id']) ?>" class="btn btn-primary">Mark as Paid</a>
                <?php endif; ?>

                <?php if (($invoice['matching_status'] ?? 'unmatched') === 'unmatched'): ?>
                <a href="<?= site_url('invoice-management/match/' . $invoice['id']) ?>" class="btn btn-info">Match Documents</a>
                <?php endif; ?>

                <?php if (($invoice['matching_status'] ?? 'unmatched') !== 'discrepancy'): ?>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#flagDiscrepancyModal">Flag Discrepancy</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Flag Discrepancy Modal -->
    <div class="modal fade" id="flagDiscrepancyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flag Discrepancy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= site_url('invoice-management/flag-discrepancy/' . $invoice['id']) ?>" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="discrepancy_notes" class="form-label">Discrepancy Notes</label>
                            <textarea class="form-control" id="discrepancy_notes" name="discrepancy_notes" rows="4" required
                                      placeholder="Describe the discrepancy found during matching..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Flag Discrepancy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

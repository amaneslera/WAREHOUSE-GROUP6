<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Match Invoice Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Match Invoice Documents</h2>
        <a href="<?= site_url('invoice-management/view/' . $invoice['id']) ?>" class="btn btn-secondary">Back to Invoice</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Invoice Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Invoice #<?= $invoice['invoice_number'] ?> - <?= $invoice['vendor_name'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Amount:</strong> $<?= number_format($invoice['invoice_amount'], 2) ?></p>
                    <p><strong>Invoice Date:</strong> <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></p>
                    <p><strong>Due Date:</strong> <?= date('M d, Y', strtotime($invoice['due_date'])) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        <span class="badge bg-<?= $this->getStatusColor($invoice['status']) ?>">
                            <?= ucfirst($invoice['status']) ?>
                        </span>
                    </p>
                    <p><strong>Warehouse:</strong>
                        <?php if (!empty($invoice['warehouse_id'])): ?>
                            Warehouse #<?= $invoice['warehouse_id'] ?>
                        <?php else: ?>
                            Not specified
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Matching Form -->
    <form action="<?= site_url('invoice-management/match/' . $invoice['id']) ?>" method="post">
        <div class="card">
            <div class="card-header">
                <h5>Document Matching</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="po_reference" class="form-label">Purchase Order Reference</label>
                            <input type="text" class="form-control" id="po_reference" name="po_reference"
                                   value="<?= old('po_reference', $invoice['po_reference'] ?? '') ?>"
                                   placeholder="Enter PO reference number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="delivery_receipt" class="form-label">Delivery Receipt</label>
                            <input type="text" class="form-control" id="delivery_receipt" name="delivery_receipt"
                                   value="<?= old('delivery_receipt', $invoice['delivery_receipt'] ?? '') ?>"
                                   placeholder="Enter delivery receipt number">
                        </div>
                    </div>
                </div>

                <!-- Stock Movements Selection -->
                <div class="mb-3">
                    <label class="form-label">Related Stock Movements</label>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <?php if (!empty($potentialMovements)): ?>
                            <p class="text-muted small">Select stock movements that correspond to this invoice:</p>
                            <?php foreach ($potentialMovements as $movement): ?>
                            <div class="form-check">
                                <input class="form-check-input stock-movement-checkbox" type="checkbox"
                                       name="stock_movement_ids[]" value="<?= $movement['id'] ?>"
                                       id="movement_<?= $movement['id'] ?>"
                                       <?php
                                       $selectedMovements = explode(',', $invoice['stock_movement_ids'] ?? '');
                                       if (in_array($movement['id'], $selectedMovements)) echo 'checked';
                                       ?>>
                                <label class="form-check-label" for="movement_<?= $movement['id'] ?>">
                                    <strong>#<?= $movement['id'] ?></strong> - <?= $movement['item_name'] ?>
                                    (Qty: <?= $movement['quantity'] ?>, Warehouse: <?= $movement['warehouse_name'] ?>,
                                    Date: <?= date('M d, Y', strtotime($movement['created_at'])) ?>)
                                </label>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No stock movements found for this vendor.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Discrepancy Section -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="has_discrepancy" name="has_discrepancy" value="1">
                        <label class="form-check-label text-warning" for="has_discrepancy">
                            <strong>Flag as having discrepancies</strong>
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="discrepancy_notes_section" style="display: none;">
                    <label for="discrepancy_notes" class="form-label">Discrepancy Notes</label>
                    <textarea class="form-control" id="discrepancy_notes" name="discrepancy_notes" rows="4"
                              placeholder="Describe any discrepancies found (quantity mismatch, pricing issues, etc.)"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Save Matching</button>
                <a href="<?= site_url('invoice-management/view/' . $invoice['id']) ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
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

// Show/hide discrepancy notes based on checkbox
document.getElementById('has_discrepancy').addEventListener('change', function() {
    const section = document.getElementById('discrepancy_notes_section');
    if (this.checked) {
        section.style.display = 'block';
        document.getElementById('discrepancy_notes').required = true;
    } else {
        section.style.display = 'none';
        document.getElementById('discrepancy_notes').required = false;
        document.getElementById('discrepancy_notes').value = '';
    }
});
</script>
</body>
</html>

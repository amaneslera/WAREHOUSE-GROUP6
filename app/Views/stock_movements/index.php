<!-- stock_movements/index.php -->
<h2>Stock Movements</h2>
<div class="mb-3">
	<a href="<?= site_url('stock-movements/in') ?>" class="btn btn-success">Stock In</a>
	<a href="<?= site_url('stock-movements/out') ?>" class="btn btn-danger">Stock Out</a>
	<a href="<?= site_url('stock-movements/transfer') ?>" class="btn btn-primary">Transfer</a>
</div>
<table class="table table-bordered">
	<thead>
		<tr>
			<th>Date</th>
			<th>Type</th>
			<th>Item</th>
			<th>Quantity</th>
			<th>From</th>
			<th>To</th>
			<th>Reference</th>
			<th>Approval</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($movements)): foreach ($movements as $move): ?>
		<tr>
			<td><?= esc($move['created_at'] ?? '') ?></td>
			<td><?= esc($move['movement_type'] ?? '') ?></td>
			<td><?= esc($move['item_name']) ?></td>
			<td><?= esc($move['quantity']) ?></td>
			<td><?= esc($move['from_warehouse'] ?? '-') ?></td>
			<td><?= esc($move['to_warehouse'] ?? '-') ?></td>
			<td><?= esc($move['reference_number'] ?? '-') ?></td>
			<td><?= esc($move['approval_status'] ?? '-') ?></td>
		</tr>
		<?php endforeach; else: ?>
		<tr><td colspan="8" class="text-center">No stock movements found.</td></tr>
		<?php endif; ?>
	</tbody>
</table>
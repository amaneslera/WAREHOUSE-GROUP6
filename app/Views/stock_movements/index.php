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
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($movements)): foreach ($movements as $move): ?>
		<tr>
			<td><?= esc($move['date']) ?></td>
			<td><?= esc($move['type']) ?></td>
			<td><?= esc($move['item_name']) ?></td>
			<td><?= esc($move['quantity']) ?></td>
			<td><?= esc($move['from_location']) ?></td>
			<td><?= esc($move['to_location']) ?></td>
			<td>
				<a href="<?= site_url('stock-movement/view/' . $move['id']) ?>" class="btn btn-sm btn-info">View</a>
			</td>
		</tr>
		<?php endforeach; else: ?>
		<tr><td colspan="7" class="text-center">No stock movements found.</td></tr>
		<?php endif; ?>
	</tbody>
</table>
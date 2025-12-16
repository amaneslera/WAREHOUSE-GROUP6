<!-- stock_movements/transfer.php -->
<h2>Transfer Stock</h2>
<form action="<?= site_url('api/stock-movements/transfer') ?>" method="post">
		<input type="hidden" name="action" value="transfer">
	<?= csrf_field() ?>
	<div class="mb-3">
		<label for="item_id" class="form-label">Item</label>
		<select name="item_id" id="item_id" class="form-select" required>
			<option value="">Select Item</option>
			<?php foreach ($items as $item): ?>
				<option value="<?= $item['id'] ?>"><?= esc($item['name']) ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="mb-3">
		<label for="quantity" class="form-label">Quantity</label>
		<input type="number" name="quantity" id="quantity" class="form-control" required>
	</div>
	<div class="mb-3">
		<label for="from_location" class="form-label">From Location</label>
		<input type="text" name="from_location" id="from_location" class="form-control" required>
	</div>
	<div class="mb-3">
		<label for="to_location" class="form-label">To Location</label>
		<input type="text" name="to_location" id="to_location" class="form-control" required>
	</div>
	<button type="submit" class="btn btn-primary">Transfer</button>
	<a href="<?= site_url('stock-movement') ?>" class="btn btn-secondary">Back</a>
</form>
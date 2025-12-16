<!-- stock_movements/stock_in.php -->
<h2>Record Stock IN</h2>
<form action="<?= site_url('api/stock-movements/in') ?>" method="post">
		<input type="hidden" name="action" value="stock_in">
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
		<label for="location" class="form-label">Location</label>
		<input type="text" name="location" id="location" class="form-control" required>
	</div>
	<button type="submit" class="btn btn-success">Record Stock In</button>
	<a href="<?= site_url('stock-movement') ?>" class="btn btn-secondary">Back</a>
</form>
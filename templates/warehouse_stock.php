<?php if ($manager) { ?><section class="surface records-surface"><div class="section-heading"><h2>Assign an empty bin</h2></div><form method="post" class="module-form"><div class="fields">
<label class="field">Warehouse<select name="warehouse_id"><?php $choices = mysqli_query($conn, "SELECT warehouse_id id,warehouse_name label FROM warehouses WHERE is_active=1 ORDER BY warehouse_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == '') {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select></label>
<label class="field">Product<select name="product_id"><?php $choices = mysqli_query($conn, "SELECT product_id id,product_name label FROM products WHERE is_active=1 ORDER BY product_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == '') {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select></label>
<label class="field">Bin location<input name="bin" maxlength="50" required></label></div><div class="actions"><button class="btn btn-success" name="action" value="create">Assign bin</button></div></form></section><?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Balances by warehouse</h2><a href="stock_movements.php">Record movement</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Warehouse name</th><th>Product name</th><th>Quantity on hand</th><th>Quantity reserved</th><th>Available</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
    <td><?php echo htmlspecialchars($row['quantity_on_hand']); ?></td>
    <td><?php echo htmlspecialchars($row['quantity_reserved']); ?></td>
    <td><?php echo htmlspecialchars($row['available']); ?></td>
    <td><form method="post" class="inline-form">
<input type="hidden" name="warehouse_id" value="<?php echo $row['warehouse_id']; ?>">
<input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
<input aria-label="Bin location" name="bin" maxlength="50" value="<?php if (isset($row['aisle_bin_location'])) { echo htmlspecialchars($row['aisle_bin_location']); } ?>">
<button class="btn btn-sm btn-outline-success" name="action" value="update">Save bin</button>
<?php
$can_remove = false;
if ($manager) {
    if ($row['quantity_on_hand'] == 0) {
        if ($row['quantity_reserved'] == 0) {
            $can_remove = true;
        }
    }
}
if ($can_remove) { ?><button class="btn btn-sm btn-link" name="action" value="delete">Remove</button><?php } ?></form></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

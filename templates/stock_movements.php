<section class="surface records-surface"><div class="section-heading"><h2>Record a movement</h2></div>
<form method="post" class="module-form"><div class="fields">
<label class="field">Movement<select name="action"><option value="transfer">Transfer</option><option value="increase">Increase stock</option><option value="decrease">Decrease stock</option></select></label>
<label class="field">Source warehouse<select name="warehouse_id"><?php $choices = mysqli_query($conn, "SELECT warehouse_id id,warehouse_name label FROM warehouses WHERE is_active=1 ORDER BY warehouse_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == '') {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select></label>
<label class="field">Transfer destination<select name="destination"><option value="">Choose for transfers</option><?php $choices = mysqli_query($conn, "SELECT warehouse_id id,warehouse_name label FROM warehouses WHERE is_active=1 ORDER BY warehouse_name"); ?>
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
<label class="field">Quantity<input type="number" name="quantity" min="1" required></label><label class="field">Reason<input name="notes" maxlength="255" required></label>
</div><div class="actions"><button class="btn btn-success">Post movement</button></div></form></section>
<section class="surface records-surface"><div class="section-heading"><h2>Movement history</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Movement id</th><th>Movement date</th><th>Sku</th><th>Warehouse name</th><th>Movement type</th><th>Quantity change</th><th>Reference type</th><th>Reference id</th><th>Notes</th><th>Username</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['movement_id']); ?></td>
    <td><?php echo htmlspecialchars($row['movement_date']); ?></td>
    <td><?php echo htmlspecialchars($row['sku']); ?></td>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php echo htmlspecialchars($row['movement_type']); ?></td>
    <td><?php echo htmlspecialchars($row['quantity_change']); ?></td>
    <td><?php if (isset($row['reference_type'])) { echo htmlspecialchars($row['reference_type']); } ?></td>
    <td><?php if (isset($row['reference_id'])) { echo htmlspecialchars($row['reference_id']); } ?></td>
    <td><?php if (isset($row['notes'])) { echo htmlspecialchars($row['notes']); } ?></td>
    <td><?php if (isset($row['username'])) { echo htmlspecialchars($row['username']); } ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

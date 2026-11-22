<?php
$selected_party = 0;
$selected_warehouse = 0;
$date = '';
if ($order) {
    $selected_party = $order['customer_id'];
    $selected_warehouse = $order['warehouse_id'];

}
?>
<?php if ($order) { ?>
<p><a href="sales_orders.php">All sales orders</a></p>
<div class="summary"><span class="record-badge"><?php echo htmlspecialchars($order['status']); ?></span>
<h2 class="h5 mt-2"><?php echo htmlspecialchars($order['party']); ?></h2>
<p><?php echo htmlspecialchars($order['warehouse_name']); ?> &middot; <?php echo htmlspecialchars($order['order_date']); ?>
 </p>
<?php $delivery_result = mysqli_query($conn, "SELECT phone,shipping_address FROM customers WHERE customer_id=$selected_party");
$delivery = mysqli_fetch_assoc($delivery_result); ?>
<p>Delivery &middot; <?php if (isset($delivery['phone'])) { echo htmlspecialchars($delivery['phone']); } ?> <?php if (isset($delivery['shipping_address'])) { echo htmlspecialchars($delivery['shipping_address']); } ?></p>
<strong>Order total <?php echo htmlspecialchars($order['total_amount']); ?></strong></div>
<p>Source: <?php echo htmlspecialchars($order['order_source']); ?> | Payment: <?php echo htmlspecialchars($order['payment_method']); ?> | Status: <?php echo htmlspecialchars($order['payment_status']); ?><?php if ($order['transaction_id']) { echo ' | Transaction: ' . htmlspecialchars($order['transaction_id']); } ?></p>
<p>Shipping fee: <?php echo htmlspecialchars($order['shipping_fee']); ?> | Address: <?php echo htmlspecialchars($order['shipping_address']); ?></p>
<?php } ?>
<?php if ($editable) { ?>
<section class="surface records-surface"><div class="section-heading"><h2><?php if ($order) {
        echo 'Order details';
    } else {
        echo 'Start an order';
    } ?></h2></div>
<form method="post" class="module-form"><div class="fields">
<?php if ($is_customer == false) { ?><label class="field">Customer<select name="party_id" required><option value="">Choose</option><?php $choices = mysqli_query($conn, "SELECT customer_id id,customer_name label FROM customers ORDER BY label"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == $selected_party) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select></label><?php } ?>
<label class="field">Warehouse<select name="warehouse_id" required><option value="">Choose</option><?php $choices = mysqli_query($conn, "SELECT warehouse_id id,warehouse_name label FROM warehouses WHERE is_active=1 ORDER BY label"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['id']; ?>" <?php if ($choice['id'] == $selected_warehouse) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['label']); ?></option>
                <?php } ?></select></label>
<?php if ($order == null) { ?><label class="field">First product<select name="product_id" ><option value="">Choose later</option><?php $choices = mysqli_query($conn, "SELECT product_id, sku, product_name FROM products WHERE is_active=1 ORDER BY product_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['product_id']; ?>" <?php if ($choice['product_id'] == $product) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['sku'] . ' / ' . $choice['product_name']); ?></option>
                <?php } ?></select></label>
<label class="field">Quantity<input type="number" min="1" name="quantity" value="1"></label><?php } ?>
</div><div class="actions"><?php if ($order) { ?><button class="btn btn-sm btn-outline-success" name="action" value="header">Save details</button><?php } else { ?><button class="btn btn-sm btn-outline-success" name="action" value="create">Create order</button><?php } ?></div></form></section>
<?php } ?>
<?php if ($order == null) { ?>
<section class="surface records-surface"><div class="section-heading"><h2>Order history</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Order</th><th>Customer</th><th>Warehouse</th><th>Date</th><th>Source</th><th>Payment</th><th>Status</th><th>Total</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><a href="?id=<?php echo $row['so_id']; ?>"><?php echo htmlspecialchars($row['so_number']); ?></a></td>
    <td><?php echo htmlspecialchars($row['party']); ?></td>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
    <td><?php echo htmlspecialchars($row['order_source']); ?></td>
    <td><?php echo htmlspecialchars($row['payment_method']); ?><br><small><?php echo htmlspecialchars($row['payment_status']); ?></small></td>
    <td><span class="record-badge"><?php echo htmlspecialchars($row['status']); ?></span></td>
    <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if (mysqli_num_rows($rows) == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>
<?php } else { ?>
<?php if ($editable) { ?><section class="surface records-surface"><div class="section-heading"><h2>Add an item</h2></div>
<form method="post" class="module-form"><div class="fields"><label class="field">Product<select name="product_id" required><option value="">Choose</option><?php $choices = mysqli_query($conn, "SELECT product_id, sku, product_name FROM products WHERE is_active=1 ORDER BY product_name"); ?>
                <?php while ($choice = mysqli_fetch_assoc($choices)) { ?>
                    <option value="<?php echo $choice['product_id']; ?>" <?php if ($choice['product_id'] == 0) {
                        echo 'selected';
                    } ?>><?php echo htmlspecialchars($choice['sku'] . ' / ' . $choice['product_name']); ?></option>
                <?php } ?></select></label>
<label class="field">Quantity<input name="quantity" type="number" min="1" value="1" required></label></div>
<div class="actions"><button class="btn btn-sm btn-outline-success" name="action" value="add">Add product</button></div></form></section><?php } ?>
<section class="surface records-surface"><div class="section-heading"><h2>Order items</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table"><thead><tr>
<th>Product</th><th>Quantity</th><th>Unit price</th><th>Actions</th></tr></thead><tbody>
<?php while ($item = mysqli_fetch_assoc($lines)) { ?>
<tr><td><?php echo htmlspecialchars($item['product_name']); ?></td><td><?php echo $item['quantity']; ?></td><td><?php echo $item['unit_price']; ?></td>

<td><?php if ($editable) { ?>
<form class="inline-form" method="post"><input type="hidden" name="line_id" value="<?php echo $item['so_item_id']; ?>">
<input type="number" aria-label="New quantity" min="1" name="quantity" value="<?php echo $item['quantity']; ?>" required>
<button class="btn btn-sm btn-outline-success" name="action" value="edit">Update</button><button class="btn btn-sm btn-outline-success" name="action" value="remove">Remove</button></form>

<?php } else {
            echo '—';
        } ?></td></tr>
<?php } ?></tbody></table></div>
<form method="post" class="module-form actions">
<?php if ($can_approve) { ?><button class="btn btn-sm btn-outline-success" name="action" value="confirm">Confirm sale</button><?php } ?>
<?php if ($supervisor) { ?><button class="btn btn-sm btn-outline-success" name="action" value="mark_paid">Mark Paid</button><?php } ?>
<?php if ($can_ship) { ?><button class="btn btn-sm btn-outline-success" name="action" value="ship">Ship order</button><?php } ?>
<?php if ($can_cancel) { ?><button class="btn btn-sm btn-outline-success" name="action" value="cancel">Cancel order</button><?php } ?>
<?php if ($editable) { ?><button class="btn btn-sm btn-outline-success" name="action" value="delete">Delete draft</button><?php } ?>
</form></section><?php } ?>

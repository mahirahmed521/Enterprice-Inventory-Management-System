<div class="metrics">
    <article class="metric metric-tile"><i class="bx bx-receipt" aria-hidden="true"></i>Ready to ship<strong><?php echo $summary['orders_to_ship']; ?></strong></article>
    <article class="metric metric-tile"><i class="bx bx-package" aria-hidden="true"></i>Deliveries due<strong><?php echo $summary['deliveries_due']; ?></strong></article>
</div><h2 class="h5">Quick access</h2><div class="quick-links">
    <a href="products.php">Products</a>
    <a href="warehouse_stock.php">Warehouse stock</a>
    <a href="stock_movements.php">Stock movements</a>
    <a href="purchase_orders.php">Purchase orders</a>
    <a href="sales_orders.php">Sales orders</a>
</div><section class="surface records-surface"><div class="section-heading"><h2>Orders ready to ship</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>So number</th><th>Customer name</th><th>Warehouse name</th><th>Order date</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['so_number']); ?></td>
    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

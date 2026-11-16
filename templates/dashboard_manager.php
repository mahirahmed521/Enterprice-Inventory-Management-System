<div class="metrics">
    <article class="metric metric-tile"><i class="bx bx-receipt" aria-hidden="true"></i>Open purchase orders<strong><?php echo $summary['open_purchases']; ?></strong></article>
    <article class="metric metric-tile"><i class="bx bx-package" aria-hidden="true"></i>Pending sales<strong><?php echo $summary['pending_sales']; ?></strong></article>
</div><h2 class="h5">Quick access</h2><div class="quick-links">
    <a href="products.php">Products</a>
    <a href="categories.php">Categories</a>
    <a href="warehouse_stock.php">Warehouse stock</a>
    <a href="warehouses.php">Warehouses</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="purchase_orders.php">Purchase orders</a>
    <a href="sales_orders.php">Sales orders</a>
    <a href="stock_movements.php">Stock movements</a>
    <a href="reorder_alerts.php">Reorder alerts</a>
</div><section class="surface records-surface"><div class="section-heading"><h2>Products to reorder</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Sku</th><th>Product name</th><th>Available to sell</th><th>Reorder point</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['sku']); ?></td>
    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
    <td><?php echo htmlspecialchars($row['available_to_sell']); ?></td>
    <td><?php echo htmlspecialchars($row['reorder_point']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

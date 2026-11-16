<div class="metrics">
    <article class="metric metric-tile"><i class="bx bx-wallet" aria-hidden="true"></i>Inventory at cost<strong><?php echo $summary['inventory_value']; ?></strong></article>
    <article class="metric metric-tile"><i class="bx bx-package" aria-hidden="true"></i>Active products<strong><?php echo $summary['active_products']; ?></strong></article>
    <article class="metric metric-tile"><i class="bx bx-group" aria-hidden="true"></i>Active accounts<strong><?php echo $summary['active_accounts']; ?></strong></article>
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
    <a href="inventory_valuation.php">Inventory valuation</a>
    <a href="users.php">Users</a>
</div><section class="surface records-surface"><div class="section-heading"><h2>Recent stock movements</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Movement date</th><th>Sku</th><th>Warehouse name</th><th>Movement type</th><th>Quantity change</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['movement_date']); ?></td>
    <td><?php echo htmlspecialchars($row['sku']); ?></td>
    <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
    <td><?php echo htmlspecialchars($row['movement_type']); ?></td>
    <td><?php echo htmlspecialchars($row['quantity_change']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

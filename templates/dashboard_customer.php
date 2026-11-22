<div class="metrics">
    <article class="metric metric-tile"><i class="bx bx-receipt" aria-hidden="true"></i>My open orders<strong><?php echo $summary['open_orders']; ?></strong></article>
    <article class="metric metric-tile"><i class="bx bx-package" aria-hidden="true"></i>My shipments<strong><?php echo $summary['shipments']; ?></strong></article>
</div><h2 class="h5">Quick access</h2><div class="quick-links">
    <a href="../shop.php">Shop</a>
    <a href="sales_orders.php">Sales orders</a>
    <a href="customer_profile.php">Customer profile</a>
</div><section class="surface records-surface"><div class="section-heading"><h2>My latest orders</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>So number</th><th>Order date</th><th>Status</th><th>Total amount</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['so_number']); ?></td>
    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
    <td><?php echo htmlspecialchars($row['status']); ?></td>
    <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if (mysqli_num_rows($rows) == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

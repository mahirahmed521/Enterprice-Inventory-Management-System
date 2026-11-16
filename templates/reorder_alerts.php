<div class="records-banner"><span class="records-banner-icon"><i class="bx bx-bar-chart-alt-2"></i></span><div><strong><?php echo $rows->num_rows; ?></strong><span>Products at or below their reorder point</span></div></div>
<section class="surface records-surface"><div class="section-heading"><h2>By product</h2><a class="text-link" href="purchase_orders.php">Open purchase orders</a></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Sku</th><th>Product name</th><th>Available to sell</th><th>Reorder point</th><th>Reorder quantity</th><th>Company name</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['sku']); ?></td>
    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
    <td><?php echo htmlspecialchars($row['available_to_sell']); ?></td>
    <td><?php echo htmlspecialchars($row['reorder_point']); ?></td>
    <td><?php echo htmlspecialchars($row['reorder_quantity']); ?></td>
    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

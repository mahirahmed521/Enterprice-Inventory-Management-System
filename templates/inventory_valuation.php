<div class="records-banner"><span class="records-banner-icon"><i class="bx bx-bar-chart-alt-2"></i></span><div><strong><?php echo $value['total']; ?></strong><span>Inventory value at current cost</span></div></div>
<section class="surface records-surface"><div class="section-heading"><h2>By product</h2></div>
<div class="table-responsive"><table class="table inventory-table records-table">
<thead><tr><th>Sku</th><th>Product name</th><th>Category name</th><th>Cost price</th><th>Selling price</th><th>Unit margin</th><th>Total on hand</th><th>Total reserved</th><th>Available to sell</th><th>Total inventory cost</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($rows)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['sku']); ?></td>
    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
    <td><?php if (isset($row['category_name'])) { echo htmlspecialchars($row['category_name']); } ?></td>
    <td><?php echo htmlspecialchars($row['cost_price']); ?></td>
    <td><?php echo htmlspecialchars($row['selling_price']); ?></td>
    <td><?php echo htmlspecialchars($row['unit_margin']); ?></td>
    <td><?php echo htmlspecialchars($row['total_on_hand']); ?></td>
    <td><?php echo htmlspecialchars($row['total_reserved']); ?></td>
    <td><?php echo htmlspecialchars($row['available_to_sell']); ?></td>
    <td><?php echo htmlspecialchars($row['total_inventory_cost']); ?></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php if ($rows->num_rows == 0) { ?><p class="empty-state">No records to show.</p><?php } ?>
</section>

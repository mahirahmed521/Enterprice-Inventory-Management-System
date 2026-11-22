<?php
// Dashboard layout. Expects $account, $supervisor, $warehouse_access, $overview_level, $ov and the $ov_* lists.
$first_name = $account['username'];
$role_names = array('ADMIN' => 'Administrator', 'MANAGER' => 'Manager', 'STAFF' => 'Warehouse staff');
$role_label = $role_names[$account['role']];

// Each tile: label, number, small note, link, icon, and whether it needs attention when above zero.
$attention = array();
if ($supervisor) {
    $attention[] = array('Orders to confirm', $ov['pending_orders'], $ov['pending_online'] . ' placed online', 'sales_orders.php', 'bx-receipt', true);
}
$attention[] = array('Ready to ship', $ov['to_ship'], 'Confirmed, waiting for the warehouse', 'sales_orders.php', 'bx-package', true);
$attention[] = array('Deliveries due', $ov['deliveries_due'], 'Supplier orders to receive', 'purchase_orders.php', 'bxs-cart-download', true);
$attention[] = array('Out of stock', $ov['out_stock'], $ov['low_stock'] . ' more running low', 'reorder_alerts.php', 'bx-error-circle', true);
if ($supervisor) {
    $attention[] = array('Awaiting payment', $ov['unpaid_orders'], $ov_currency . number_format($ov['unpaid_amount'], 0) . ' outstanding', 'sales_orders.php', 'bx-time-five', true);
}
?>
<section class="dash-hero">
    <div>
        <span class="eyebrow"><?php echo htmlspecialchars($role_label); ?></span>
        <h2>Welcome back, <?php echo htmlspecialchars($first_name); ?></h2>
        <p><?php echo date('l, d F Y'); ?> &middot; <?php echo $ov['to_ship']; ?> to ship &middot; <?php echo $ov['low_stock'] + $ov['out_stock']; ?> stock alerts</p>
    </div>
    <div class="dash-actions">
        <a class="btn btn-success" href="sales_orders.php"><i class="bx bx-cart" aria-hidden="true"></i> Sales orders</a>
        <?php if ($warehouse_access) { ?><a class="btn btn-outline-secondary" href="purchase_orders.php"><i class="bx bxs-cart-download" aria-hidden="true"></i> Receive stock</a><?php } ?>
        <?php if ($warehouse_access) { ?><a class="btn btn-outline-secondary" href="stock_movements.php"><i class="bx bx-transfer" aria-hidden="true"></i> Stock movement</a><?php } ?>
        <?php if ($supervisor) { ?><a class="btn btn-outline-secondary" href="products.php"><i class="bx bx-package" aria-hidden="true"></i> Products</a><?php } ?>
    </div>
</section>

<section class="attention-grid" aria-label="Needs attention">
<?php $a = 0; while (isset($attention[$a])) { ?>
    <?php
    $tile_class = '';
    if ($attention[$a][5] && $attention[$a][1] > 0) {
        $tile_class = 'has-work';
    }
    ?>
    <a class="attention-card <?php echo $tile_class; ?>" href="<?php echo $attention[$a][3]; ?>">
        <i class="bx <?php echo $attention[$a][4]; ?>" aria-hidden="true"></i>
        <span><?php echo htmlspecialchars($attention[$a][0]); ?></span>
        <strong><?php echo (int) $attention[$a][1]; ?></strong>
        <small><?php echo htmlspecialchars($attention[$a][2]); ?></small>
    </a>
<?php $a = $a + 1; } ?>
</section>

<div class="business-grid work-queues">
    <section class="surface">
        <div class="section-heading"><div><h2>Ready to ship</h2><p>Oldest confirmed orders first</p></div><a href="sales_orders.php" class="text-link">All orders &rarr;</a></div>
        <div class="table-responsive"><table class="table inventory-table records-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Warehouse</th><th>Date</th></tr></thead>
            <tbody>
            <?php $q = 0; while (isset($ov_ship_queue[$q])) { ?>
                <tr>
                    <td><a href="sales_orders.php?id=<?php echo (int) $ov_ship_queue[$q]['so_id']; ?>"><?php echo htmlspecialchars($ov_ship_queue[$q]['so_number']); ?></a></td>
                    <td><?php echo htmlspecialchars($ov_ship_queue[$q]['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($ov_ship_queue[$q]['warehouse_name']); ?></td>
                    <td><?php echo htmlspecialchars($ov_ship_queue[$q]['order_date']); ?></td>
                </tr>
            <?php $q = $q + 1; } ?>
            </tbody>
        </table></div>
        <?php if (count($ov_ship_queue) == 0) { ?><p class="empty-state">Nothing waiting to ship.</p><?php } ?>
    </section>

    <section class="surface">
        <div class="section-heading"><div><h2>Deliveries due</h2><p>Supplier orders still to receive</p></div><a href="purchase_orders.php" class="text-link">Purchase orders &rarr;</a></div>
        <div class="table-responsive"><table class="table inventory-table records-table">
            <thead><tr><th>Order</th><th>Supplier</th><th>Expected</th><th>Status</th></tr></thead>
            <tbody>
            <?php $q = 0; while (isset($ov_delivery_queue[$q])) { ?>
                <tr>
                    <td><a href="purchase_orders.php?id=<?php echo (int) $ov_delivery_queue[$q]['po_id']; ?>"><?php echo htmlspecialchars($ov_delivery_queue[$q]['po_number']); ?></a></td>
                    <td><?php echo htmlspecialchars($ov_delivery_queue[$q]['company_name']); ?></td>
                    <td><?php if ($ov_delivery_queue[$q]['expected_date']) { echo htmlspecialchars($ov_delivery_queue[$q]['expected_date']); } else { echo '&mdash;'; } ?></td>
                    <td><span class="record-badge"><?php echo htmlspecialchars($ov_delivery_queue[$q]['status']); ?></span></td>
                </tr>
            <?php $q = $q + 1; } ?>
            </tbody>
        </table></div>
        <?php if (count($ov_delivery_queue) == 0) { ?><p class="empty-state">No deliveries expected.</p><?php } ?>
    </section>
</div>

<?php if ($supervisor) { ?>
<section class="surface mb-4">
    <div class="section-heading"><div><h2>Awaiting confirmation</h2><p>Pending orders, including those placed online</p></div><a href="sales_orders.php" class="text-link">Review &rarr;</a></div>
    <div class="table-responsive"><table class="table inventory-table records-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Source</th><th>Date</th><th>Total</th></tr></thead>
        <tbody>
        <?php $q = 0; while (isset($ov_confirm_queue[$q])) { ?>
            <tr>
                <td><a href="sales_orders.php?id=<?php echo (int) $ov_confirm_queue[$q]['so_id']; ?>"><?php echo htmlspecialchars($ov_confirm_queue[$q]['so_number']); ?></a></td>
                <td><?php echo htmlspecialchars($ov_confirm_queue[$q]['customer_name']); ?></td>
                <td><span class="record-badge muted-badge"><?php echo htmlspecialchars($ov_confirm_queue[$q]['order_source']); ?></span></td>
                <td><?php echo htmlspecialchars($ov_confirm_queue[$q]['order_date']); ?></td>
                <td><?php echo $ov_currency . number_format((float) $ov_confirm_queue[$q]['total_amount'], 2); ?></td>
            </tr>
        <?php $q = $q + 1; } ?>
        </tbody>
    </table></div>
    <?php if (count($ov_confirm_queue) == 0) { ?><p class="empty-state">No orders waiting for confirmation.</p><?php } ?>
</section>
<?php } ?>

<?php include '../includes/overview_panels.php'; ?>

<h2 class="h5">Quick access</h2>
<div class="quick-links">
    <?php if ($warehouse_access) { ?><a href="products.php">Products</a><a href="warehouse_stock.php">Warehouse stock</a><?php } ?>
    <?php if ($supervisor) { ?><a href="categories.php">Categories</a><a href="warehouses.php">Warehouses</a><a href="suppliers.php">Suppliers</a><a href="customers.php">Customers</a><?php } ?>
    <?php if ($warehouse_access) { ?><a href="purchase_orders.php">Purchase orders</a><?php } ?>
    <a href="sales_orders.php">Sales orders</a>
    <?php if ($warehouse_access) { ?><a href="stock_movements.php">Stock movements</a><?php } ?>
    <?php if ($supervisor) { ?><a href="reorder_alerts.php">Reorder alerts</a><a href="reviews.php">Product reviews</a><?php } ?>
    <?php if ($account['role'] == 'ADMIN') { ?><a href="inventory_valuation.php">Inventory valuation</a><a href="users.php">Users</a><?php } ?>
</div>

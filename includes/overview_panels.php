<?php
// The all-in-one business overview. Needs includes/overview_data.php to have run first.
// $overview_show_money is true for admins (stock cost and margin are admin-only numbers).
if (isset($overview_show_money) == false) {
    $overview_show_money = false;
}
$status_classes = array('PENDING' => 'amber', 'CONFIRMED' => 'blue', 'SHIPPED' => 'teal', 'CANCELLED' => 'rose');
$status_total = $ov['all_orders'];
$pay_names = array('COD' => 'Cash on Delivery', 'BKASH' => 'bKash', 'NAGAD' => 'Nagad', 'CARD' => 'Card', 'BANK' => 'Bank');
$margin = $ov['retail_value'] - $ov['cost_value'];
// Warehouse staff get the operational view: no sales totals, customers, suppliers or reviews.
$ov_staff_view = (isset($overview_level) && $overview_level == 'staff');
?>
<div class="business-heading">
    <div>
        <span class="eyebrow">EVERYTHING IN ONE PLACE</span>
        <h2><?php echo $ov_staff_view ? 'Warehouse overview' : 'Business overview'; ?></h2>
    </div>
    <span>Live from the database &middot; <?php echo htmlspecialchars($ov['today']); ?></span>
</div>

<?php if ($ov_staff_view) { ?>
<section class="business-metrics" aria-label="Warehouse summary">
    <a href="sales_orders.php" class="surface business-metric">
        <span class="stat-icon blue"><i class="bx bx-package" aria-hidden="true"></i></span>
        <div><span>Ready to ship</span><strong><?php echo $ov['to_ship']; ?></strong><small>Confirmed orders waiting for the warehouse</small></div>
    </a>
    <a href="purchase_orders.php" class="surface business-metric">
        <span class="stat-icon teal"><i class="bx bxs-cart-download" aria-hidden="true"></i></span>
        <div><span>Deliveries due</span><strong><?php echo $ov['deliveries_due']; ?></strong><small>Supplier orders to receive</small></div>
    </a>
    <a href="reorder_alerts.php" class="surface business-metric">
        <span class="stat-icon purple"><i class="bx bx-bell" aria-hidden="true"></i></span>
        <div><span>Stock alerts</span><strong><?php echo $ov['low_stock'] + $ov['out_stock']; ?></strong><small><?php echo $ov['low_stock']; ?> low &middot; <?php echo $ov['out_stock']; ?> out of stock</small></div>
    </a>
    <a href="warehouse_stock.php" class="surface business-metric">
        <span class="stat-icon amber"><i class="bx bx-layer" aria-hidden="true"></i></span>
        <div><span>Units on hand</span><strong><?php echo number_format($ov['units_on_hand']); ?></strong><small><?php echo number_format($ov['units_reserved']); ?> reserved for orders</small></div>
    </a>
</section>
<?php } else { ?>
<section class="business-metrics" aria-label="Sales and stock summary">
    <a href="sales_orders.php" class="surface business-metric">
        <span class="stat-icon teal"><i class="bx bx-line-chart" aria-hidden="true"></i></span>
        <div><span>Total sales</span><strong><?php echo $ov_currency . number_format($ov['sales_total'], 0); ?></strong><small><?php echo $ov['sales_orders']; ?> orders &middot; avg <?php echo $ov_currency . number_format($ov['avg_order'], 0); ?></small></div>
    </a>
    <a href="sales_orders.php" class="surface business-metric">
        <span class="stat-icon blue"><i class="bx bx-calendar" aria-hidden="true"></i></span>
        <div><span>Sales this month</span><strong><?php echo $ov_currency . number_format($ov['month_sales'], 0); ?></strong><small><?php echo $ov['month_orders']; ?> orders &middot; today <?php echo $ov_currency . number_format($ov['today_sales'], 0); ?> (<?php echo $ov['today_orders']; ?>)</small></div>
    </a>
    <a href="sales_orders.php" class="surface business-metric">
        <span class="stat-icon amber"><i class="bx bx-time-five" aria-hidden="true"></i></span>
        <div><span>Awaiting payment</span><strong><?php echo $ov_currency . number_format($ov['unpaid_amount'], 0); ?></strong><small><?php echo $ov['unpaid_orders']; ?> unpaid orders &middot; <?php echo $ov['to_ship']; ?> ready to ship</small></div>
    </a>
    <a href="reorder_alerts.php" class="surface business-metric">
        <span class="stat-icon purple"><i class="bx bx-bell" aria-hidden="true"></i></span>
        <div><span>Stock alerts</span><strong><?php echo $ov['low_stock'] + $ov['out_stock']; ?></strong><small><?php echo $ov['low_stock']; ?> low &middot; <?php echo $ov['out_stock']; ?> out of stock</small></div>
    </a>
    <?php if ($overview_show_money) { ?>
    <a href="inventory_valuation.php" class="surface business-metric">
        <span class="stat-icon teal"><i class="bx bx-coin" aria-hidden="true"></i></span>
        <div><span>Stock retail value</span><strong><?php echo $ov_currency . number_format($ov['retail_value'], 0); ?></strong><small>Potential margin <?php echo $ov_currency . number_format($margin, 0); ?></small></div>
    </a>
    <?php } else { ?>
    <a href="warehouse_stock.php" class="surface business-metric">
        <span class="stat-icon teal"><i class="bx bx-layer" aria-hidden="true"></i></span>
        <div><span>Units on hand</span><strong><?php echo number_format($ov['units_on_hand']); ?></strong><small><?php echo number_format($ov['units_reserved']); ?> reserved for orders</small></div>
    </a>
    <?php } ?>
    <a href="purchase_orders.php" class="surface business-metric">
        <span class="stat-icon blue"><i class="bx bxs-cart-download" aria-hidden="true"></i></span>
        <div><span>Open purchase orders</span><strong><?php echo $ov['open_pos']; ?></strong><small><?php echo $ov_currency . number_format($ov['open_po_value'], 0); ?> on order from suppliers</small></div>
    </a>
    <a href="customers.php" class="surface business-metric">
        <span class="stat-icon purple"><i class="bx bx-group" aria-hidden="true"></i></span>
        <div><span>Customers</span><strong><?php echo $ov['customers']; ?></strong><small><?php echo $ov['new_customers']; ?> joined in the last 30 days</small></div>
    </a>
    <a href="reviews.php" class="surface business-metric">
        <span class="stat-icon amber"><i class="bx bx-star" aria-hidden="true"></i></span>
        <div><span>Customer feedback</span><strong><?php if ($ov['reviews'] > 0) { echo number_format($ov['avg_rating'], 1) . ' / 5'; } else { echo '&mdash;'; } ?></strong><small><?php echo $ov['reviews']; ?> reviews &middot; <?php echo $ov['wishlist_items']; ?> wishlist saves</small></div>
    </a>
</section>
<?php } ?>

<?php if ($ov_staff_view == false) { ?>
<div class="business-grid">
    <section class="surface">
        <div class="section-heading">
            <div><h2>Sales, last 14 days</h2><p><?php echo $ov_currency . number_format($ov['chart_sales'], 0); ?> from <?php echo $ov['chart_orders']; ?> orders</p></div>
            <a href="sales_orders.php" class="text-link">All orders &rarr;</a>
        </div>
        <div class="sales-chart" role="img" aria-label="Sales for each of the last 14 days">
            <?php $d = 0; while (isset($ov_chart[$d])) { ?>
                <?php
                $bar_height = 2;
                if ($ov['chart_max'] > 0) {
                    $bar_height = round($ov_chart[$d]['sales'] / $ov['chart_max'] * 100);
                    if ($bar_height < 2) {
                        $bar_height = 2;
                    }
                }
                $is_today = '';
                if ($ov_chart[$d]['date'] == $ov['today']) {
                    $is_today = 'today';
                }
                ?>
                <div class="sales-col <?php echo $is_today; ?>" title="<?php echo htmlspecialchars($ov_chart[$d]['long']); ?>: <?php echo $ov_chart[$d]['orders']; ?> orders, <?php echo $ov_currency . number_format($ov_chart[$d]['sales'], 2); ?>">
                    <span class="sales-val"><?php if ($ov_chart[$d]['sales'] > 0) { echo money_short($ov_chart[$d]['sales']); } ?></span>
                    <div class="sales-bar-track"><div class="sales-bar" style="height:<?php echo $bar_height; ?>%"></div></div>
                    <span class="sales-day"><?php echo $ov_chart[$d]['label']; ?></span>
                </div>
            <?php $d = $d + 1; } ?>
        </div>
        <div class="table-bottom"><span>Cancelled orders are not counted. Bars show order value per day.</span><a href="sales_orders.php" class="text-link">Sales orders &rarr;</a></div>
    </section>

    <section class="surface">
        <div class="section-heading">
            <div><h2>Order pipeline</h2><p><?php echo $status_total; ?> orders in total</p></div>
            <a href="sales_orders.php" class="text-link">Manage &rarr;</a>
        </div>
        <div class="pipeline">
            <?php $statuses = array('PENDING', 'CONFIRMED', 'SHIPPED', 'CANCELLED'); $st = 0; while ($st < 4) { ?>
                <?php
                $status_name = $statuses[$st];
                $share = 0;
                if ($status_total > 0) {
                    $share = round($ov_status[$status_name] / $status_total * 100);
                }
                ?>
                <div class="pipeline-row">
                    <div class="pipeline-label"><span><?php echo ucfirst(strtolower($status_name)); ?></span><strong><?php echo $ov_status[$status_name]; ?></strong></div>
                    <div class="pipeline-track"><div class="pipeline-fill <?php echo $status_classes[$status_name]; ?>" style="width:<?php echo $share; ?>%"></div></div>
                </div>
            <?php $st = $st + 1; } ?>
        </div>
        <div class="pay-mix">
            <p class="pay-mix-title">How customers pay</p>
            <?php $py = 0; while (isset($ov_payments[$py])) { ?>
                <?php
                $pay_code = $ov_payments[$py]['payment_method'];
                $pay_label = $pay_code;
                if (isset($pay_names[$pay_code])) {
                    $pay_label = $pay_names[$pay_code];
                }
                ?>
                <span class="pay-chip"><?php echo htmlspecialchars($pay_label); ?> <b><?php echo (int) $ov_payments[$py]['total']; ?></b></span>
            <?php $py = $py + 1; } ?>
            <?php if (count($ov_payments) == 0) { ?><span class="text-muted small">No paid-for orders yet.</span><?php } ?>
        </div>
    </section>
</div>
<?php } ?>

<div class="business-grid"<?php if ($ov_staff_view) { echo ' style="grid-template-columns:minmax(0,1fr)"'; } ?>>
<?php if ($ov_staff_view == false) { ?>
    <section class="surface">
        <div class="section-heading">
            <div><h2>Best sellers</h2><p>Units sold, cancelled orders excluded</p></div>
            <a href="products.php" class="text-link">Products &rarr;</a>
        </div>
        <div class="mini-list">
            <?php $t = 0; while (isset($ov_top_products[$t])) { ?>
                <a class="mini-row" href="products.php?id=<?php echo $ov_top_products[$t]['product_id']; ?>">
                    <span class="mini-rank"><?php echo $t + 1; ?></span>
                    <img class="mini-thumb" src="<?php echo htmlspecialchars(product_image_url($ov_top_products[$t]['product_image'], '../')); ?>" onerror="this.onerror=null;this.src='../pictures/default.png'" alt="">
                    <div><strong><?php echo htmlspecialchars($ov_top_products[$t]['product_name']); ?></strong><small><?php echo htmlspecialchars($ov_top_products[$t]['sku']); ?></small></div>
                    <div class="mini-figure"><strong><?php echo (int) $ov_top_products[$t]['units']; ?> sold</strong><small><?php echo $ov_currency . number_format((float) $ov_top_products[$t]['revenue'], 0); ?></small></div>
                </a>
            <?php $t = $t + 1; } ?>
            <?php if (count($ov_top_products) == 0) { ?><p class="empty-state">No sales yet. Best sellers appear after the first order.</p><?php } ?>
        </div>
    </section>
<?php } ?>

    <section class="surface">
        <div class="section-heading">
            <div><h2>Stock alerts</h2><p>At or below the reorder point</p></div>
            <a href="reorder_alerts.php" class="text-link">Reorder report &rarr;</a>
        </div>
        <div class="mini-list">
            <?php $a = 0; while (isset($ov_alerts[$a])) { ?>
                <?php
                $alert_class = 'low';
                $alert_text = 'Low';
                if ((int) $ov_alerts[$a]['available_to_sell'] <= 0) {
                    $alert_class = 'out';
                    $alert_text = 'Out';
                }
                ?>
                <a class="mini-row" href="products.php?id=<?php echo $ov_alerts[$a]['product_id']; ?>">
                    <img class="mini-thumb" src="<?php echo htmlspecialchars(product_image_url($ov_alerts[$a]['product_image'], '../')); ?>" onerror="this.onerror=null;this.src='../pictures/default.png'" alt="">
                    <div><strong><?php echo htmlspecialchars($ov_alerts[$a]['product_name']); ?></strong><small><?php echo htmlspecialchars($ov_alerts[$a]['sku']); ?> &middot; reorder at <?php echo (int) $ov_alerts[$a]['reorder_point']; ?></small></div>
                    <div class="mini-figure"><span class="stock-badge <?php echo $alert_class; ?>"><?php echo (int) $ov_alerts[$a]['available_to_sell']; ?> left &middot; <?php echo $alert_text; ?></span></div>
                </a>
            <?php $a = $a + 1; } ?>
            <?php if (count($ov_alerts) == 0) { ?><p class="empty-state">Every active product is above its reorder point.</p><?php } ?>
        </div>
    </section>
</div>

<div class="business-grid">
    <section class="surface">
        <div class="section-heading">
            <div><h2>Recent orders</h2><p>Online and back-office sales</p></div>
            <a href="sales_orders.php" class="text-link">All sales orders &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table inventory-table">
                <thead><tr><th>Order / customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                <?php $o = 0; while (isset($ov_orders[$o])) { ?>
                    <tr>
                        <td><strong><a href="sales_orders.php?id=<?php echo $ov_orders[$o]['so_id']; ?>"><?php echo htmlspecialchars($ov_orders[$o]['so_number']); ?></a></strong><small><?php echo htmlspecialchars($ov_orders[$o]['customer_name']); ?> &middot; <?php echo htmlspecialchars(ucfirst(strtolower($ov_orders[$o]['order_source']))); ?></small></td>
                        <td><span class="record-badge"><?php echo htmlspecialchars($ov_orders[$o]['status']); ?></span></td>
                        <td><span class="record-badge <?php if ($ov_orders[$o]['payment_status'] != 'PAID') { echo 'muted-badge'; } ?>"><?php echo htmlspecialchars($ov_orders[$o]['payment_status']); ?></span></td>
                        <td><?php echo $ov_currency . number_format((float) $ov_orders[$o]['total_amount'], 2); ?></td>
                        <td class="order-date"><?php echo htmlspecialchars($ov_orders[$o]['order_date']); ?></td>
                    </tr>
                <?php $o = $o + 1; } ?>
                </tbody>
            </table>
        </div>
        <?php if (count($ov_orders) == 0) { ?><p class="empty-state">No sales orders yet.</p><?php } ?>
        <div class="table-bottom"><span><?php echo $ov['pending_orders']; ?> pending &middot; <?php echo $ov['to_ship']; ?> confirmed and ready to ship</span><a href="sales_orders.php" class="text-link">Process orders &rarr;</a></div>
    </section>

    <section class="surface">
        <div class="section-heading">
            <div><h2>Warehouses</h2><p>Stock held at each location</p></div>
            <a href="warehouses.php" class="text-link">Locations &rarr;</a>
        </div>
        <div class="warehouse-overview-list">
            <?php $w = 0; while (isset($ov_warehouses[$w])) { ?>
                <article class="warehouse-overview-card">
                    <div class="warehouse-title">
                        <span class="stat-icon teal"><i class="bx bx-buildings" aria-hidden="true"></i></span>
                        <div><h3><?php echo htmlspecialchars($ov_warehouses[$w]['warehouse_name']); ?></h3><small><?php echo htmlspecialchars($ov_warehouses[$w]['warehouse_code']); ?> &middot; <?php if ($ov_warehouses[$w]['is_active'] == 1) { echo 'Active'; } else { echo 'Inactive'; } ?></small></div>
                    </div>
                    <div class="warehouse-numbers">
                        <div><strong><?php echo (int) $ov_warehouses[$w]['product_lines']; ?></strong><span>Product lines</span></div>
                        <div><strong><?php echo number_format((int) $ov_warehouses[$w]['on_hand']); ?></strong><span>On hand</span></div>
                        <div><strong><?php echo number_format((int) $ov_warehouses[$w]['available']); ?></strong><span>Available</span></div>
                    </div>
                    <p><?php if ($ov_warehouses[$w]['manager_name'] != '') { echo htmlspecialchars($ov_warehouses[$w]['manager_name']); } else { echo 'No manager assigned'; } ?> &middot; <?php echo number_format((int) $ov_warehouses[$w]['reserved']); ?> reserved</p>
                </article>
            <?php $w = $w + 1; } ?>
            <?php if (count($ov_warehouses) == 0) { ?><p class="empty-state">No warehouses yet.</p><?php } ?>
        </div>
        <div class="table-bottom"><span><?php echo number_format($ov['units_on_hand']); ?> units on hand in total</span><a href="warehouse_stock.php" class="text-link">View stock &rarr;</a></div>
    </section>
</div>

<div class="business-grid">
    <section class="surface">
        <div class="section-heading">
            <div><h2>Stock movements</h2><p>Latest receipts, shipments and adjustments</p></div>
            <a href="stock_movements.php" class="text-link">History &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table inventory-table">
                <thead><tr><th>When</th><th>SKU / warehouse</th><th>Type</th><th>Change</th></tr></thead>
                <tbody>
                <?php $m = 0; while (isset($ov_movements[$m])) { ?>
                    <tr>
                        <td class="order-date"><?php echo htmlspecialchars(substr($ov_movements[$m]['movement_date'], 0, 16)); ?></td>
                        <td><strong><?php echo htmlspecialchars($ov_movements[$m]['sku']); ?></strong><small><?php echo htmlspecialchars($ov_movements[$m]['warehouse_name']); ?></small></td>
                        <td><span class="record-badge"><?php echo htmlspecialchars(str_replace('_', ' ', $ov_movements[$m]['movement_type'])); ?></span></td>
                        <td><?php if ((int) $ov_movements[$m]['quantity_change'] > 0) { echo '+'; } echo (int) $ov_movements[$m]['quantity_change']; ?></td>
                    </tr>
                <?php $m = $m + 1; } ?>
                </tbody>
            </table>
        </div>
        <?php if (count($ov_movements) == 0) { ?><p class="empty-state">No stock movements yet.</p><?php } ?>
    </section>

    <section class="surface">
        <div class="section-heading">
            <div><h2>Purchasing</h2><p>Latest supplier orders</p></div>
            <a href="purchase_orders.php" class="text-link">All purchase orders &rarr;</a>
        </div>
        <div class="mini-list">
            <?php $p = 0; while (isset($ov_purchases[$p])) { ?>
                <a class="mini-row" href="purchase_orders.php?id=<?php echo $ov_purchases[$p]['po_id']; ?>">
                    <span class="stat-icon blue"><i class="bx bxs-cart-download" aria-hidden="true"></i></span>
                    <div><strong><?php echo htmlspecialchars($ov_purchases[$p]['po_number']); ?></strong><small><?php echo htmlspecialchars($ov_purchases[$p]['company_name']); ?><?php if ($ov_purchases[$p]['expected_date'] != '') { echo ' &middot; due ' . htmlspecialchars($ov_purchases[$p]['expected_date']); } ?></small></div>
                    <div class="mini-figure"><span class="record-badge"><?php echo htmlspecialchars($ov_purchases[$p]['status']); ?></span><small><?php echo $ov_currency . number_format((float) $ov_purchases[$p]['total_amount'], 0); ?></small></div>
                </a>
            <?php $p = $p + 1; } ?>
            <?php if (count($ov_purchases) == 0) { ?><p class="empty-state">No purchase orders yet.</p><?php } ?>
        </div>
    </section>
</div>

<?php if ($ov_staff_view == false) { ?>
<div class="business-grid directory-grid">
    <section class="surface">
        <div class="section-heading">
            <div><h2>Newest customers</h2><p><?php echo $ov['customers']; ?> in the directory</p></div>
            <a href="customers.php" class="text-link">Directory &rarr;</a>
        </div>
        <div class="partner-list">
            <?php $cu = 0; while (isset($ov_customers[$cu])) { ?>
                <a class="partner-row" href="customers.php">
                    <span class="partner-avatar"><i class="bx bx-user" aria-hidden="true"></i></span>
                    <div><strong><?php echo htmlspecialchars($ov_customers[$cu]['customer_name']); ?></strong><small><?php echo htmlspecialchars((string) $ov_customers[$cu]['email']); ?></small><small><?php echo htmlspecialchars((string) $ov_customers[$cu]['phone']); ?></small></div>
                    <i class="bx bx-chevron-right" aria-hidden="true"></i>
                </a>
            <?php $cu = $cu + 1; } ?>
            <?php if (count($ov_customers) == 0) { ?><p class="empty-state">No customers yet.</p><?php } ?>
        </div>
    </section>

    <section class="surface">
        <div class="section-heading">
            <div><h2>Suppliers</h2><p><?php echo $ov['suppliers']; ?> active</p></div>
            <a href="suppliers.php" class="text-link">Directory &rarr;</a>
        </div>
        <div class="partner-list">
            <?php $su = 0; while (isset($ov_suppliers[$su])) { ?>
                <a class="partner-row" href="suppliers.php">
                    <span class="partner-avatar supplier-avatar"><i class="bx bx-store-alt" aria-hidden="true"></i></span>
                    <div><strong><?php echo htmlspecialchars($ov_suppliers[$su]['company_name']); ?></strong><small><?php echo htmlspecialchars((string) $ov_suppliers[$su]['contact_person']); ?></small><small><?php if ($ov_suppliers[$su]['is_active'] == 1) { echo 'Active'; } else { echo 'Inactive'; } ?></small></div>
                    <i class="bx bx-chevron-right" aria-hidden="true"></i>
                </a>
            <?php $su = $su + 1; } ?>
            <?php if (count($ov_suppliers) == 0) { ?><p class="empty-state">No suppliers yet.</p><?php } ?>
        </div>
    </section>
</div>
<?php } ?>

<?php if (count($ov_reviews) > 0) { ?>
<?php if ($ov_staff_view == false) { ?>
<section class="surface mb-4">
    <div class="section-heading">
        <div><h2>Latest reviews</h2><p>What customers are saying</p></div>
        <a href="reviews.php" class="text-link">All reviews &rarr;</a>
    </div>
    <div class="partner-list">
        <?php $rv = 0; while (isset($ov_reviews[$rv])) { ?>
            <a class="partner-row" href="reviews.php">
                <span class="partner-avatar supplier-avatar"><i class="bx bxs-star" aria-hidden="true"></i></span>
                <div><strong><?php echo htmlspecialchars($ov_reviews[$rv]['product_name']); ?> &middot; <?php echo (int) $ov_reviews[$rv]['rating']; ?>/5</strong><small><?php echo htmlspecialchars($ov_reviews[$rv]['customer_name']); ?> &middot; <?php echo htmlspecialchars(substr($ov_reviews[$rv]['created_at'], 0, 10)); ?></small><small><?php echo htmlspecialchars(substr((string) $ov_reviews[$rv]['comment'], 0, 140)); ?></small></div>
                <i class="bx bx-chevron-right" aria-hidden="true"></i>
            </a>
        <?php $rv = $rv + 1; } ?>
    </div>
</section>
<?php } ?>
<?php } ?>

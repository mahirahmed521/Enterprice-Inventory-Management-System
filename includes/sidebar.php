<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$navigation = [
    'WORKSPACE' => [
        ['dashboard.php', 'Overview', 'bx-grid-alt'],
        ['products.php', 'Products', 'bx-package'],
        ['categories.php', 'Categories', 'bx-category'],
        ['warehouse_stock.php', 'Warehouse stock', 'bx-layer'],
        ['warehouses.php', 'Warehouses', 'bx-buildings'],
    ],
    'BUSINESS' => [
        ['suppliers.php', 'Suppliers', 'bx-store-alt'],
        ['customers.php', 'Customers', 'bx-group'],
        ['purchase_orders.php', 'Purchase orders', 'bxs-cart-download'],
        ['sales_orders.php', 'Sales orders', 'bx-cart'],
        ['stock_movements.php', 'Stock movements', 'bx-transfer'],
    ],
    'INSIGHTS & TEAM' => [
        ['inventory_valuation.php', 'Inventory valuation', 'bx-pie-chart-alt-2'],
        ['reorder_alerts.php', 'Reorder alerts', 'bx-bell'],
        ['users.php', 'Users', 'bx-user-circle'],
    ],
];
?>
<aside class="sidebar-wrapper" id="sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-icon"><i class="bx bx-package" aria-hidden="true"></i></span> Stockroom<span class="brand-dot">.</span></a>
    <div class="workspace-switch"><span class="workspace-monogram">S</span><div><strong>My inventory</strong><small>Your business workspace</small></div><i class="bx bx-chevron-down" aria-hidden="true"></i></div>
    <nav aria-label="Main navigation" class="sidebar-navigation">
        <?php foreach ($navigation as $group => $links): ?>
            <p class="nav-label"><?php echo $group; ?></p>
            <?php foreach ($links as [$url, $label, $icon]): ?>
                <a class="sidebar-link <?php echo $currentPage === $url ? 'active' : ''; ?>" href="<?php echo $url; ?>" <?php echo $currentPage === $url ? 'aria-current="page"' : ''; ?>><i class="bx <?php echo $icon; ?>" aria-hidden="true"></i><span><?php echo $label; ?></span><?php if ($currentPage === $url): ?><span class="nav-active-dot"></span><?php endif; ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom"><a class="sidebar-link" href="logout.php"><i class="bx bx-log-out" aria-hidden="true"></i> Log out</a><div class="sidebar-caption"><span class="status-dot"></span> A little order. A better workday.</div></div>
</aside>
<button type="button" class="sidebar-overlay" id="sidebar-overlay" aria-label="Close navigation" hidden></button>

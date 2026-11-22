<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
$nav_role = $_SESSION['role'];
if (strpos($current_page, 'dashboard_') === 0) {
    $current_page = 'dashboard.php';
}
if ($current_page == 'admin_profile.php' || $current_page == 'manager_profile.php' || $current_page == 'staff_profile.php') {
    $current_page = 'profile.php';
}
?>
<aside class="sidebar-wrapper" id="sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-icon"><i class="bx bx-package" aria-hidden="true"></i></span>Stockroom<span class="brand-dot">.</span></a>
    <nav aria-label="Main navigation" class="sidebar-navigation">
        <p class="nav-label">Workspace</p>
        <a class="sidebar-link <?php if ($current_page == 'dashboard.php') {
            echo 'active';
        } ?>" href="dashboard.php" <?php if ($current_page == 'dashboard.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-grid-alt" aria-hidden="true"></i><span>Overview</span></a>
        <?php if ($warehouse_access) { ?>
        <a class="sidebar-link <?php if ($current_page == 'products.php') {
            echo 'active';
        } ?>" href="products.php" <?php if ($current_page == 'products.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-package" aria-hidden="true"></i><span>Products</span></a>
        <a class="sidebar-link <?php if ($current_page == 'warehouse_stock.php') {
            echo 'active';
        } ?>" href="warehouse_stock.php" <?php if ($current_page == 'warehouse_stock.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-layer" aria-hidden="true"></i><span>Warehouse stock</span></a>
        <?php } ?>
        <?php if ($supervisor) { ?>
        <a class="sidebar-link <?php if ($current_page == 'categories.php') {
            echo 'active';
        } ?>" href="categories.php" <?php if ($current_page == 'categories.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-category" aria-hidden="true"></i><span>Categories</span></a>
        <a class="sidebar-link <?php if ($current_page == 'warehouses.php') {
            echo 'active';
        } ?>" href="warehouses.php" <?php if ($current_page == 'warehouses.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-buildings" aria-hidden="true"></i><span>Warehouses</span></a>
        <a class="sidebar-link <?php if ($current_page == 'suppliers.php') {
            echo 'active';
        } ?>" href="suppliers.php" <?php if ($current_page == 'suppliers.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-store-alt" aria-hidden="true"></i><span>Suppliers</span></a>
        <a class="sidebar-link <?php if ($current_page == 'customers.php') {
            echo 'active';
        } ?>" href="customers.php" <?php if ($current_page == 'customers.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-group" aria-hidden="true"></i><span>Customers</span></a>
        <?php } ?>
        <p class="nav-label">Orders</p>
        <?php if ($warehouse_access) { ?>
        <a class="sidebar-link <?php if ($current_page == 'purchase_orders.php') {
            echo 'active';
        } ?>" href="purchase_orders.php" <?php if ($current_page == 'purchase_orders.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bxs-cart-download" aria-hidden="true"></i><span>Purchase orders</span></a>
        <?php } ?>
        <a class="sidebar-link <?php if ($current_page == 'sales_orders.php') {
            echo 'active';
        } ?>" href="sales_orders.php" <?php if ($current_page == 'sales_orders.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-cart" aria-hidden="true"></i><span>Sales orders</span></a>
        <?php if ($supervisor) { ?>
        <a class="sidebar-link <?php if ($current_page == 'reviews.php') { echo 'active'; } ?>" href="reviews.php"><i class="bx bx-star" aria-hidden="true"></i><span>Product reviews</span></a>
        <?php } ?>
        <?php if ($warehouse_access) { ?>
        <a class="sidebar-link <?php if ($current_page == 'stock_movements.php') {
            echo 'active';
        } ?>" href="stock_movements.php" <?php if ($current_page == 'stock_movements.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-transfer" aria-hidden="true"></i><span>Stock movements</span></a>
        <?php } ?>
        <?php if ($supervisor) { ?>
        <p class="nav-label">Reports</p>
        <a class="sidebar-link <?php if ($current_page == 'reorder_alerts.php') {
            echo 'active';
        } ?>" href="reorder_alerts.php" <?php if ($current_page == 'reorder_alerts.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-bell" aria-hidden="true"></i><span>Reorder alerts</span></a>
        <?php } ?>
        <?php if ($nav_role == 'ADMIN') { ?>
        <a class="sidebar-link <?php if ($current_page == 'inventory_valuation.php') {
            echo 'active';
        } ?>" href="inventory_valuation.php" <?php if ($current_page == 'inventory_valuation.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-pie-chart-alt-2" aria-hidden="true"></i><span>Inventory valuation</span></a>
        <a class="sidebar-link <?php if ($current_page == 'users.php') {
            echo 'active';
        } ?>" href="users.php" <?php if ($current_page == 'users.php') {
            echo 'aria-current="page"';
        } ?>><i class="bx bx-user-circle" aria-hidden="true"></i><span>Users</span></a>
        <?php } ?>
        <a class="sidebar-link" href="../home.php"><i class="bx bx-store" aria-hidden="true"></i><span>View storefront</span></a>
    </nav>
    <div class="sidebar-bottom"><a class="sidebar-link <?php if ($current_page == 'profile.php') { echo 'active'; } ?>" href="profile.php"><i class="bx bx-user" aria-hidden="true"></i>My profile</a>
<a class="sidebar-link" href="logout.php"><i class="bx bx-log-out" aria-hidden="true"></i>Log out</a></div>
</aside>
<button type="button" class="sidebar-overlay" id="sidebar-overlay" aria-label="Close navigation" hidden></button>

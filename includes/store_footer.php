<?php
// Shared storefront footer with live warehouse addresses and the mobile bottom bar.
$footer_warehouses = mysqli_query($conn, 'SELECT warehouse_name, location_address FROM warehouses WHERE is_active=1 ORDER BY warehouse_name');
?>
<footer class="store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-lg-3">
                <h3>Customer Care</h3>
                <a href="my_orders.php">Track My Order</a>
                <a href="wishlist.php">My Wishlist</a>
                <a href="cart.php">Shopping Cart</a>
                <a href="register.php">Create an Account</a>
                <a href="login.php">Sign In</a>
            </div>
            <div class="col-6 col-lg-3">
                <h3>Store</h3>
                <a href="home.php">Home</a>
                <a href="shop.php">All Electronics</a>
                <a href="home.php#hp-flash-sale">Flash Sale</a>
            </div>
            <div class="col-12 col-lg-3">
                <h3>Fulfillment Warehouses</h3>
                <?php while ($footer_warehouse = mysqli_fetch_assoc($footer_warehouses)) { ?>
                    <p><strong><?php echo htmlspecialchars($footer_warehouse['warehouse_name']); ?></strong><br><?php echo htmlspecialchars($footer_warehouse['location_address']); ?></p>
                <?php } ?>
            </div>
            <div class="col-12 col-lg-3">
                <h3>Payment Methods</h3>
                <span class="pay-badge">Cash on Delivery</span><span class="pay-badge">bKash</span><span class="pay-badge">Nagad</span><span class="pay-badge">Card</span><span class="pay-badge">Bank</span>
            </div>
        </div>
    </div>
</footer>
<div class="store-copy">&copy; <?php echo date('Y'); ?> Stockroom Electronics. All rights reserved.</div>
<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <a href="home.php"><i class="bx bx-home"></i><span>Home</span></a>
    <a href="shop.php"><i class="bx bx-category"></i><span>Categories</span></a>
    <a href="cart.php"><i class="bx bx-cart"></i><span>Cart</span></a>
    <a href="my_account.php"><i class="bx bx-user"></i><span>Account</span></a>
</nav>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/storefront.js"></script>

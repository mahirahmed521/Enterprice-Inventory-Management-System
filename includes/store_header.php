<?php
// Shared storefront header. The page starts the session and loads $conn before including this.
$cart_count = 0;
$search_value = '';
$header_user = '';
$header_role = '';
$cart_notice_text = '';
$cart_error_text = '';
$store_notice_text = '';
if (isset($_SESSION['cart']) == false) {
    $_SESSION['cart'] = array();
}
if (isset($_GET['q'])) {
    $search_value = $_GET['q'];
}
if (isset($_SESSION['username'])) {
    $header_user = $_SESSION['username'];
}
if (isset($_SESSION['role'])) {
    $header_role = $_SESSION['role'];
}
if (isset($_SESSION['cart_notice'])) {
    $cart_notice_text = $_SESSION['cart_notice'];
    unset($_SESSION['cart_notice']);
}
if (isset($_SESSION['cart_error'])) {
    $cart_error_text = $_SESSION['cart_error'];
    unset($_SESSION['cart_error']);
}
if (isset($_SESSION['store_notice'])) {
    $store_notice_text = $_SESSION['store_notice'];
    unset($_SESSION['store_notice']);
}
$header_cart_keys = array_keys($_SESSION['cart']);
$cart_count = count($header_cart_keys);
$wish_count = 0;
if (isset($_SESSION['user_id'])) {
    $header_user_id = (int) $_SESSION['user_id'];
    $wish_count = (int) db_scalar($conn, "SELECT COUNT(*) FROM wishlist_items w JOIN users u ON u.customer_id=w.customer_id WHERE u.user_id=$header_user_id", 0);
}
?>
<div class="store-utility">
    <div class="container d-flex flex-wrap justify-content-end py-1 gap-3">
        <a href="home.php#hp-flash-sale">Flash Sale</a>
        <a href="shop.php">All Products</a>
        <a href="my_orders.php">Track My Order</a>
        <a href="wishlist.php">My Wishlist</a>
        <?php if ($header_role == 'CUSTOMER') { ?>
            <a href="my_account.php">My Account</a>
            <a href="my_profile.php">My Profile</a>
        <?php } elseif ($header_role == 'ADMIN' || $header_role == 'MANAGER' || $header_role == 'STAFF') { ?>
            <a href="admin/dashboard.php">Staff Dashboard</a>
        <?php } ?>
        <?php if (isset($_SESSION['user_id'])) { ?>
            <span><?php echo htmlspecialchars($header_user); ?></span>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Sign Up</a>
        <?php } ?>
    </div>
</div>
<nav class="navbar store-navbar sticky-top py-2">
    <div class="container flex-nowrap gap-3">
        <a class="store-logo" href="home.php">Stockroom<small>Electronics</small></a>
        <form class="store-search input-group flex-grow-1" method="get" action="shop.php">
            <input class="form-control" type="search" name="q" value="<?php echo htmlspecialchars($search_value); ?>" placeholder="Search in Store..." aria-label="Search products">
            <button class="btn" type="submit" aria-label="Search"><i class="bx bx-search"></i></button>
        </form>
        <a class="store-cart store-wish-link ms-2" href="wishlist.php" aria-label="My wishlist"><i class="bx bx-heart"></i><span class="store-cart-badge"><?php echo $wish_count; ?></span></a>
        <a class="store-cart ms-2" href="cart.php" aria-label="Shopping cart"><i class="bx bx-cart"></i><span class="store-cart-badge"><?php echo $cart_count; ?></span></a>
    </div>
</nav>
<?php if ($cart_notice_text != '') { ?>
<div class="alert alert-success store-alert" role="status">
    <div class="container d-flex flex-wrap align-items-center gap-2">
        <i class="bx bx-check-circle fs-5"></i>
        <span class="me-auto"><?php echo htmlspecialchars($cart_notice_text); ?></span>
        <a class="btn btn-sm btn-outline-orange" href="cart.php">View Cart</a>
        <a class="btn btn-sm btn-orange" href="checkout.php">Checkout Now</a>
        <button type="button" class="btn-close" data-dismiss-alert aria-label="Close"></button>
    </div>
</div>
<?php } ?>
<?php if ($cart_error_text != '') { ?>
<div class="alert alert-warning store-alert" role="alert">
    <div class="container d-flex align-items-center gap-2">
        <span class="me-auto"><?php echo htmlspecialchars($cart_error_text); ?></span>
        <button type="button" class="btn-close" data-dismiss-alert aria-label="Close"></button>
    </div>
</div>
<?php } ?>
<?php if ($store_notice_text != '') { ?>
<div class="alert alert-info store-alert" role="status">
    <div class="container d-flex align-items-center gap-2">
        <span class="me-auto"><?php echo htmlspecialchars($store_notice_text); ?></span>
        <button type="button" class="btn-close" data-dismiss-alert aria-label="Close"></button>
    </div>
</div>
<?php } ?>

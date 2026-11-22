<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

$currency = '৳';
include __DIR__ . '/includes/cart_actions.php';
include __DIR__ . '/includes/current_customer.php';

if ($customer_id == 0) {
    $_SESSION['redirect_after_login'] = 'wishlist.php';
}

$wishlist_products = array();
$wishlist_ready = true;
if ($customer_id > 0) {
    $wishlist_sql = "SELECT v.*, (SELECT AVG(r.rating) FROM product_reviews r WHERE r.product_id=v.product_id) AS avg_rating, (SELECT COUNT(*) FROM product_reviews r2 WHERE r2.product_id=v.product_id) AS review_count FROM wishlist_items w JOIN vw_storefront_products v ON v.product_id=w.product_id WHERE w.customer_id=$customer_id ORDER BY w.wishlist_id DESC";
    $wishlist_result = mysqli_query($conn, $wishlist_sql);
    if ($wishlist_result) {
        while ($wishlist_row = mysqli_fetch_assoc($wishlist_result)) {
            $wishlist_products[] = $wishlist_row;
        }
    } else {
        $wishlist_ready = false;
    }
}
$wishlist_total = count($wishlist_products);
?>
<!doctype html>
<html lang="en">
<head>
    <title>My Wishlist | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <h1 class="h3 mb-3">My Wishlist</h1>
        <?php if ($customer_id == 0) { ?>
            <div class="card"><div class="card-body text-center py-5">
                <i class="bx bx-heart display-4 text-orange"></i>
                <h2 class="h4">Sign in to view your wishlist</h2>
                <p class="text-muted">Save products you like and come back to them later.</p>
                <a class="btn btn-orange" href="login.php?go_to=wishlist.php">Sign In</a>
                <a class="btn btn-outline-secondary" href="shop.php">Browse Products</a>
            </div></div>
        <?php } elseif (!$wishlist_ready) { ?>
            <div class="alert alert-warning">Wishlist storage is not available yet. Make sure the database has the <code>wishlist_items</code> table.</div>
            <a class="btn btn-orange" href="shop.php">Browse Products</a>
        <?php } elseif ($wishlist_total == 0) { ?>
            <div class="card"><div class="card-body text-center py-5">
                <i class="bx bx-heart display-4 text-orange"></i>
                <h2 class="h4">Your wishlist is empty</h2>
                <p class="text-muted">Tap the heart on a product to save it here.</p>
                <a class="btn btn-orange" href="shop.php">Browse Products</a>
            </div></div>
        <?php } else { ?>
            <p class="text-muted mb-3"><?php echo $wishlist_total; ?> saved product(s)</p>
            <div class="row g-2">
                <?php $i = 0; while ($i < $wishlist_total) { $product = $wishlist_products[$i]; ?>
                    <div class="col-6 col-md-4 col-xl-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                <?php $i = $i + 1; } ?>
            </div>
        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

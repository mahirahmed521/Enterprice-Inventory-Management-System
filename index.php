<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$home_user = '';
$home_role = '';
if (isset($_SESSION['username'])) {
    $home_user = $_SESSION['username'];
}
if (isset($_SESSION['role'])) {
    $home_role = $_SESSION['role'];
}
$rating_sql = '(SELECT AVG(r.rating) FROM product_reviews r WHERE r.product_id=v.product_id) AS avg_rating, (SELECT COUNT(*) FROM product_reviews r2 WHERE r2.product_id=v.product_id) AS review_count';

// Add to Cart / Buy Now
include __DIR__ . '/includes/cart_actions.php';

// Categories with parent links and live active product counts
$cat_ids = array();
$cat_parents = array();
$cat_names = array();
$cat_counts = array();
$cat_result = mysqli_query($conn, 'SELECT c.category_id, c.parent_id, c.name, COUNT(p.product_id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.category_id AND p.is_active=1 GROUP BY c.category_id, c.parent_id, c.name ORDER BY c.name');
while ($cat_row = mysqli_fetch_assoc($cat_result)) {
    $cat_ids[] = (int) $cat_row['category_id'];
    $cat_parents[] = (int) $cat_row['parent_id'];
    $cat_names[] = $cat_row['name'];
    $cat_counts[] = (int) $cat_row['product_count'];
}
$cat_total = count($cat_ids);
// A parent category also counts the products of its children
$i = 0;
while ($i < $cat_total) {
    $j = 0;
    while ($j < $cat_total) {
        if ($cat_parents[$j] == $cat_ids[$i]) {
            $cat_counts[$i] = $cat_counts[$i] + $cat_counts[$j];
        }
        $j = $j + 1;
    }
    $i = $i + 1;
}

$catalog_result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM vw_storefront_products');
$catalog_row = mysqli_fetch_assoc($catalog_result);
$warehouse_result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM warehouses WHERE is_active=1');
$warehouse_row = mysqli_fetch_assoc($warehouse_result);
$flash = mysqli_query($conn, "SELECT v.*, $rating_sql FROM vw_storefront_products v WHERE v.is_featured=1 ORDER BY v.product_id DESC LIMIT 6");
$just = mysqli_query($conn, "SELECT v.*, $rating_sql FROM vw_storefront_products v ORDER BY v.product_id DESC LIMIT 12");
?>
<!doctype html>
<html lang="en">
<head>
    <title>Stockroom Electronics | Online Shopping</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">

        <section class="row g-2 mb-2">
            <aside class="col-lg-3 d-none d-lg-block">
                <div class="card cat-rail h-100 py-1">
                    <?php $i = 0; while ($i < $cat_total) { ?>
                        <?php if ($cat_parents[$i] == 0) { ?>
                            <div class="cat-parent">
                                <a class="cat-item" href="shop.php?category_id=<?php echo $cat_ids[$i]; ?>"><span><?php echo htmlspecialchars($cat_names[$i]); ?></span><i class="bx bx-chevron-right"></i></a>
                                <?php
                                $has_children = 0;
                                $j = 0;
                                while ($j < $cat_total) {
                                    if ($cat_parents[$j] == $cat_ids[$i]) {
                                        $has_children = 1;
                                    }
                                    $j = $j + 1;
                                }
                                ?>
                                <?php if ($has_children == 1) { ?>
                                    <div class="cat-flyout">
                                        <?php $j = 0; while ($j < $cat_total) { ?>
                                            <?php if ($cat_parents[$j] == $cat_ids[$i]) { ?>
                                                <a class="cat-item" href="shop.php?category_id=<?php echo $cat_ids[$j]; ?>"><span><?php echo htmlspecialchars($cat_names[$j]); ?></span><small class="text-muted"><?php echo $cat_counts[$j]; ?></small></a>
                                            <?php } ?>
                                        <?php $j = $j + 1; } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php $i = $i + 1; } ?>
                </div>
            </aside>

            <div class="col-lg-6">
                <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="hero-slide slide-orange"><div>
                                <h1>Flash Sale is on</h1>
                                <p class="lead">Featured electronics at live warehouse prices. Pay with COD, bKash or Nagad.</p>
                                <a class="btn btn-light btn-lg text-uppercase" href="#hp-flash-sale">Shop Flash Sale</a>
                            </div></div>
                        </div>
                        <div class="carousel-item">
                            <div class="hero-slide slide-cyan"><div>
                                <h1><?php echo $catalog_row['total']; ?> products in stock</h1>
                                <p class="lead">Shipping from <?php echo $warehouse_row['total']; ?> warehouses, with stock counted live.</p>
                                <a class="btn btn-light btn-lg text-uppercase" href="shop.php">Explore All Electronics</a>
                            </div></div>
                        </div>
                        <div class="carousel-item">
                            <div class="hero-slide slide-dark"><div>
                                <h1>Official warranty on every item</h1>
                                <p class="lead">Buy now and follow your order from placed to shipped.</p>
                                <a class="btn btn-light btn-lg text-uppercase" href="my_orders.php">Track My Order</a>
                            </div></div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                </div>
            </div>

            <aside class="col-lg-3">
                <div class="card h-100"><div class="card-body d-flex flex-column">
                    <?php if (isset($_SESSION['user_id']) == false) { ?>
                        <h2 class="h6 text-muted">Welcome to Stockroom Electronics</h2>
                        <div class="d-grid gap-2 my-3">
                            <a class="btn btn-orange" href="login.php">Login</a>
                            <a class="btn btn-outline-orange" href="register.php">Sign Up</a>
                        </div>
                    <?php } else { ?>
                        <h2 class="h6 text-muted">Welcome back</h2>
                        <p class="fw-bold fs-5 mb-3"><?php echo htmlspecialchars($home_user); ?></p>
                        <div class="d-grid gap-2 mb-3">
                            <?php if ($home_role == 'CUSTOMER') { ?>
                                <a class="btn btn-orange" href="my_orders.php">My Orders</a>
                            <?php } else { ?>
                                <a class="btn btn-orange" href="admin/dashboard.php">Open Dashboard</a>
                            <?php } ?>
                            <a class="btn btn-outline-orange" href="cart.php">My Cart</a>
                        </div>
                    <?php } ?>
                    <div class="welcome-links mt-auto">
                        <a href="my_orders.php"><i class="bx bx-package"></i> Track My Order</a>
                        <a href="shop.php"><i class="bx bx-store"></i> All Products</a>
                        <a href="admin/index.php"><i class="bx bx-building"></i> Staff Portal</a>
                    </div>
                </div></div>
            </aside>
        </section>

        <section class="row row-cols-2 row-cols-md-5 g-2 mb-2">
            <div class="col"><a class="channel-pill" href="#hp-flash-sale"><i class="bx bx-bolt-circle"></i>Flash Deals</a></div>
            <div class="col"><span class="channel-pill"><i class="bx bx-shield-quarter"></i>Official Warranty</span></div>
            <div class="col"><span class="channel-pill"><i class="bx bx-package"></i>Live Warehouse Stock</span></div>
            <div class="col"><span class="channel-pill"><i class="bx bx-run"></i>Fast Delivery</span></div>
            <div class="col"><span class="channel-pill"><i class="bx bx-money"></i>Cash on Delivery &amp; bKash</span></div>
        </section>

        <h2 class="section-title">Flash Sale</h2>
        <section class="card mb-2" id="hp-flash-sale">
            <div class="card-body">
                <div class="flash-bar">
                    <span class="flash-label">On Sale Now</span>
                    <span class="text-muted">Ending in</span>
                    <div class="countdown" data-countdown><b data-hours>00</b><span>:</span><b data-minutes>00</b><span>:</span><b data-seconds>00</b></div>
                    <a class="btn btn-outline-orange btn-sm ms-auto" href="shop.php">Shop All Products</a>
                </div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                    <?php while ($product = mysqli_fetch_assoc($flash)) { ?>
                        <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                    <?php } ?>
                </div>
                <?php if (mysqli_num_rows($flash) == 0) { ?>
                    <p class="text-muted mb-0">No flash sale products yet. Tick Featured on a product in the admin products page to show it here.</p>
                <?php } ?>
            </div>
        </section>

        <h2 class="section-title">Categories</h2>
        <section class="mb-2">
            <div class="cat-grid">
                <?php $i = 0; while ($i < $cat_total) { ?>
                    <a class="cat-tile" href="shop.php?category_id=<?php echo $cat_ids[$i]; ?>"><i class="bx bx-category"></i><strong><?php echo htmlspecialchars($cat_names[$i]); ?></strong><small class="text-muted"><?php echo $cat_counts[$i]; ?> items</small></a>
                <?php $i = $i + 1; } ?>
            </div>
        </section>

        <h2 class="section-title">Just For You</h2>
        <section>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                <?php while ($product = mysqli_fetch_assoc($just)) { ?>
                    <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                <?php } ?>
            </div>
            <?php if (mysqli_num_rows($just) == 0) { ?>
                <div class="card"><div class="card-body text-muted">No products are listed yet.</div></div>
            <?php } ?>
            <div class="text-center mt-3"><a class="btn btn-outline-orange px-5" href="shop.php">Load More</a></div>
        </section>

    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

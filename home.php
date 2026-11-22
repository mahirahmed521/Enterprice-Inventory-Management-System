<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
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
$hero_result = mysqli_query($conn, "SELECT v.product_id, v.product_name, v.product_image, v.category_name, v.selling_price, v.available_to_sell FROM vw_storefront_products v WHERE v.is_featured=1 ORDER BY v.product_id DESC LIMIT 1");
$hero_product = null;
if ($hero_result) {
    $hero_product = mysqli_fetch_assoc($hero_result);
}
if ($hero_product == null) {
    $hero_result = mysqli_query($conn, "SELECT v.product_id, v.product_name, v.product_image, v.category_name, v.selling_price, v.available_to_sell FROM vw_storefront_products v ORDER BY v.product_id DESC LIMIT 1");
    if ($hero_result) {
        $hero_product = mysqli_fetch_assoc($hero_result);
    }
}
$hero_image = 'pictures/default.png';
if ($hero_product != null) {
    $hero_image = product_image_url($hero_product['product_image']);
}
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

        <section class="landing-hero mb-4">
            <div class="landing-copy">
                <span class="landing-eyebrow"><i class="bx bx-sparkles"></i> Your everyday tech, upgraded</span>
                <h1>Find your next<br><span>favorite thing.</span></h1>
                <p>Discover dependable electronics, thoughtful prices, and fast delivery from a team that knows its stock.</p>
                <div class="landing-actions">
                    <a class="btn landing-primary" href="shop.php">Explore products <i class="bx bx-right-arrow-alt"></i></a>
                    <a class="landing-secondary" href="#hp-flash-sale">See today’s deals <i class="bx bx-chevron-down"></i></a>
                </div>
                <div class="landing-proof">
                    <div><strong><?php echo number_format((int) $catalog_row['total']); ?>+</strong><span>products to explore</span></div>
                    <i></i>
                    <div><strong><?php echo number_format((int) $warehouse_row['total']); ?></strong><span>active warehouses</span></div>
                </div>
            </div>
            <div class="landing-art" aria-label="Featured product">
                <div class="landing-orbit orbit-one"></div><div class="landing-orbit orbit-two"></div>
                <div class="landing-art-label"><span class="live-dot"></span> Curated for you</div>
                <?php if ($hero_product != null) { ?>
                    <a class="landing-feature" href="product.php?id=<?php echo (int) $hero_product['product_id']; ?>">
                        <img src="<?php echo htmlspecialchars($hero_image); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="<?php echo htmlspecialchars($hero_product['product_name']); ?>">
                        <span class="landing-feature-copy"><small><?php echo htmlspecialchars((string) $hero_product['category_name']); ?></small><strong><?php echo htmlspecialchars($hero_product['product_name']); ?></strong><b><?php echo $currency . number_format((float) $hero_product['selling_price'], 2); ?></b></span>
                    </a>
                <?php } else { ?>
                    <div class="landing-empty-art"><i class="bx bx-headphone"></i><span>Add products to bring your storefront to life.</span></div>
                <?php } ?>
                <div class="landing-stock"><i class="bx bx-check-circle"></i> Live stock, ready to ship</div>
                <span class="landing-spark spark-a">✦</span><span class="landing-spark spark-b">✧</span>
            </div>
        </section>

        <section class="trust-row mb-4" aria-label="Shopping benefits">
            <div><span class="trust-icon"><i class="bx bx-shield-quarter"></i></span><span><strong>Warranty included</strong><small>Shop with confidence</small></span></div>
            <div><span class="trust-icon"><i class="bx bx-package"></i></span><span><strong>Stock you can trust</strong><small>Updated from our warehouses</small></span></div>
            <div><span class="trust-icon"><i class="bx bx-run"></i></span><span><strong>Quick delivery</strong><small>From nearby fulfillment</small></span></div>
            <div><span class="trust-icon"><i class="bx bx-wallet"></i></span><span><strong>Easy payments</strong><small>COD, bKash and more</small></span></div>
        </section>

        <section class="home-section" id="hp-flash-sale">
            <div class="home-section-heading">
                <div><span class="section-kicker">A little extra value</span><h2>Today’s highlights</h2><p>Handpicked electronics worth a closer look.</p></div>
                <a class="section-link" href="shop.php">Shop all products <i class="bx bx-right-arrow-alt"></i></a>
            </div>
            <div class="card sale-panel"><div class="card-body">
                <div class="flash-bar">
                    <span class="flash-label"><i class="bx bxs-bolt-circle"></i> Featured deals</span>
                    <span class="text-muted">Refreshes in</span>
                    <div class="countdown" data-countdown><b data-hours>00</b><span>:</span><b data-minutes>00</b><span>:</span><b data-seconds>00</b></div>
                    <span class="sale-live ms-auto"><i class="live-dot"></i> Live now</span>
                </div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                    <?php while ($product = mysqli_fetch_assoc($flash)) { ?>
                        <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                    <?php } ?>
                </div>
                <?php if (mysqli_num_rows($flash) == 0) { ?>
                    <p class="text-muted mb-0">No flash sale products yet. Tick Featured on a product in the admin products page to show it here.</p>
                <?php } ?>
            </div></div>
        </section>

        <section class="home-section">
            <div class="home-section-heading">
                <div><span class="section-kicker">Browse your way</span><h2>Shop by category</h2><p>Start with what you’re looking for.</p></div>
                <a class="section-link" href="shop.php">All categories <i class="bx bx-right-arrow-alt"></i></a>
            </div>
            <div class="cat-grid">
                <?php $i = 0; while ($i < $cat_total) { ?>
                    <a class="cat-tile" href="shop.php?category_id=<?php echo $cat_ids[$i]; ?>"><span class="category-icon"><i class="bx bx-category-alt"></i></span><strong><?php echo htmlspecialchars($cat_names[$i]); ?></strong><small><?php echo $cat_counts[$i]; ?> items <i class="bx bx-right-arrow-alt"></i></small></a>
                <?php $i = $i + 1; } ?>
            </div>
            <?php if ($cat_total == 0) { ?><p class="text-muted">New categories are on the way. <a href="shop.php">Browse all products</a>.</p><?php } ?>
        </section>

        <section class="home-section">
            <div class="home-section-heading">
                <div><span class="section-kicker">Fresh from the shelves</span><h2>Popular right now</h2><p>Explore the latest additions to our collection.</p></div>
                <a class="section-link" href="shop.php">View all <i class="bx bx-right-arrow-alt"></i></a>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                <?php while ($product = mysqli_fetch_assoc($just)) { ?>
                    <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                <?php } ?>
            </div>
            <?php if (mysqli_num_rows($just) == 0) { ?>
                <div class="card"><div class="card-body text-muted">No products are listed yet.</div></div>
            <?php } ?>
            <div class="text-center mt-4"><a class="btn landing-more" href="shop.php">Discover all products <i class="bx bx-right-arrow-alt"></i></a></div>
        </section>

        <?php $recent_exclude = 0; include __DIR__ . '/includes/recent_strip.php'; ?>

    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

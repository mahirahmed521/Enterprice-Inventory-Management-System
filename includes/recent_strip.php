<?php
// "Recently viewed" strip. Uses $_SESSION['recent_products'] and skips $recent_skip_id (the product being viewed).
// The page must have $conn, $currency and (optionally) $wish_ids ready.
$recent_skip_id = 0;
if (isset($recent_exclude)) {
    $recent_skip_id = (int) $recent_exclude;
}
$recent_show = array();
if (isset($_SESSION['recent_products'])) {
    $r = 0;
    while (isset($_SESSION['recent_products'][$r]) && count($recent_show) < 6) {
        $recent_candidate = (int) $_SESSION['recent_products'][$r];
        if ($recent_candidate != $recent_skip_id && $recent_candidate > 0) {
            $recent_show[] = $recent_candidate;
        }
        $r = $r + 1;
    }
}
if (count($recent_show) > 0) {
    $recent_list = implode(',', $recent_show);
    $recent_rating_sql = '(SELECT AVG(r.rating) FROM product_reviews r WHERE r.product_id=v.product_id) AS avg_rating, (SELECT COUNT(*) FROM product_reviews r2 WHERE r2.product_id=v.product_id) AS review_count';
    $recent_result = mysqli_query($conn, "SELECT v.*, $recent_rating_sql FROM vw_storefront_products v WHERE v.product_id IN ($recent_list) ORDER BY FIELD(v.product_id, $recent_list)");
    if ($recent_result && mysqli_num_rows($recent_result) > 0) {
        $recent_saved_product = null;
        if (isset($product)) {
            $recent_saved_product = $product;
        }
        ?>
        <h2 class="product-strip-title">Recently Viewed</h2>
        <section class="mb-2">
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                <?php while ($product = mysqli_fetch_assoc($recent_result)) { ?>
                    <div class="col"><?php include __DIR__ . '/product_card.php'; ?></div>
                <?php } ?>
            </div>
        </section>
        <?php
        if ($recent_saved_product != null) {
            $product = $recent_saved_product;
        }
    }
}

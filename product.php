<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$id = 0;
$action = '';
$rating = 5;
$comment = '';
$review_error = '';
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if (isset($_POST['rating'])) {
    $rating = (int) $_POST['rating'];
}
if (isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
}

// Add to Cart / Buy Now (reads product_id and quantity from the form)
$_POST['product_id'] = $id;
include __DIR__ . '/includes/cart_actions.php';

$product_result = mysqli_query($conn, "SELECT * FROM vw_storefront_products WHERE product_id=$id");
$product = null;
if ($product_result) {
    $product = mysqli_fetch_assoc($product_result);
}
if ($product == null) {
    header('Location: shop.php');
    exit;
}

// Remember this product for the "Recently viewed" strip (newest first, 8 at most)
$recent_keep = array($id);
if (isset($_SESSION['recent_products'])) {
    $r = 0;
    while (isset($_SESSION['recent_products'][$r]) && count($recent_keep) < 8) {
        if ((int) $_SESSION['recent_products'][$r] != $id) {
            $recent_keep[] = (int) $_SESSION['recent_products'][$r];
        }
        $r = $r + 1;
    }
}
$_SESSION['recent_products'] = $recent_keep;

// Who is signed in
include __DIR__ . '/includes/current_customer.php';
$already_reviewed = false;
if ($customer_id > 0) {
    $own_review = mysqli_query($conn, "SELECT review_id FROM product_reviews WHERE product_id=$id AND customer_id=$customer_id");
    if (mysqli_num_rows($own_review) > 0) {
        $already_reviewed = true;
    }
}

// Review form
if ($action == 'review') {
    if ($customer_id == 0) {
        $review_error = 'Sign in with a customer account to write a review.';
    } elseif ($already_reviewed) {
        $review_error = 'You have already reviewed this product.';
    } elseif ($rating < 1 || $rating > 5) {
        $review_error = 'Choose a rating from 1 to 5.';
    } elseif (strlen($comment) > 1000) {
        $review_error = 'Keep your review under 1000 characters.';
    } else {
        $sql_text = array('comment' => $comment);
        include __DIR__ . '/includes/quote_fields.php';
        $insert = "INSERT INTO product_reviews (product_id, customer_id, rating, comment) VALUES ($id, $customer_id, $rating, '$sql_text[comment]')";
        if (mysqli_query($conn, $insert)) {
            $_SESSION['store_notice'] = 'Thank you! Your review was posted.';
            header('Location: product.php?id=' . $id . '#reviews');
            exit;
        }
        $review_error = 'Could not save your review. Please try again.';
    }
}

$rating_result = mysqli_query($conn, "SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id=$id");
$rating_row = mysqli_fetch_assoc($rating_result);
$reviews = mysqli_query($conn, "SELECT r.rating, r.comment, r.created_at, c.customer_name FROM product_reviews r JOIN customers c ON c.customer_id=r.customer_id WHERE r.product_id=$id ORDER BY r.created_at DESC");
$warehouses = mysqli_query($conn, 'SELECT warehouse_name, location_address FROM warehouses WHERE is_active=1 ORDER BY warehouse_name');

$p_img = product_image_url($product['product_image']);
$stock_status = $product['stock_status'];
include __DIR__ . '/includes/stock_badge.php';
$available = (int) $product['available_to_sell'];
$buy_disabled = '';
if ($available <= 0) {
    $buy_disabled = 'disabled';
}
$rounded_rating = (int) round((float) $rating_row['average_rating']);
$is_wished = false;
if (isset($wish_ids)) {
    if (in_array($id, $wish_ids)) {
        $is_wished = true;
    }
}

// Related products: same category first, then same brand
$rating_sql = '(SELECT AVG(r.rating) FROM product_reviews r WHERE r.product_id=v.product_id) AS avg_rating, (SELECT COUNT(*) FROM product_reviews r2 WHERE r2.product_id=v.product_id) AS review_count';
$related_category = (int) $product['category_id'];
$related_brand = '';
if ($product['brand'] != '') {
    $sql_text = array('brand' => (string) $product['brand']);
    include __DIR__ . '/includes/quote_fields.php';
    $related_brand = $sql_text['brand'];
}
$related_match = 'v.category_id=-1';
if ($related_category > 0) {
    $related_match = "v.category_id=$related_category";
}
if ($related_brand != '') {
    $related_match = $related_match . " OR v.brand='$related_brand'";
}
$related = mysqli_query($conn, "SELECT v.*, $rating_sql FROM vw_storefront_products v WHERE v.product_id<>$id AND ($related_match) ORDER BY (v.category_id=$related_category) DESC, v.available_to_sell > 0 DESC, v.product_id DESC LIMIT 6");
$spec_lines = array();
if ($product['specifications'] != '') {
    $spec_lines = explode(',', $product['specifications']);
}
$spec_total = count($spec_lines);
?>
<!doctype html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($product['product_name']); ?> | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php?category_id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars((string) $product['category_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['product_name']); ?></li>
        </ol></nav>

        <div class="card mb-2"><div class="card-body"><div class="row g-3">
            <div class="col-lg-4">
                <img class="detail-photo" src="<?php echo htmlspecialchars($p_img); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <div class="mt-2"><span class="badge <?php echo $stock_class; ?>"><?php echo $stock_label; ?></span></div>
            </div>

            <div class="col-lg-5">
                <h1 class="h4 fw-normal"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p class="mb-1">
                    <span class="stars"><?php $star = 1; while ($star <= 5) { if ($star <= $rounded_rating) { echo '&#9733;'; } else { echo '<span class="off">&#9733;</span>'; } $star = $star + 1; } ?></span>
                    <a class="small" href="#reviews"><?php echo (int) $rating_row['review_count']; ?> Ratings</a>
                </p>
                <p class="small text-muted mb-2">
                    Brand: <?php echo htmlspecialchars((string) $product['brand']); ?> &middot;
                    Category: <a href="shop.php?category_id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars((string) $product['category_name']); ?></a> &middot;
                    SKU: <?php echo htmlspecialchars($product['sku']); ?>
                </p>
                <hr class="my-2">
                <div class="detail-price"><?php echo $currency . number_format((float) $product['selling_price'], 2); ?></div>
                <p class="text-muted small">Tax of <?php echo htmlspecialchars($product['tax_rate']); ?>% is added at checkout.</p>
                <form method="post">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="text-muted">Quantity</span>
                        <div class="qty-stepper" data-quantity-stepper data-max="<?php echo $available; ?>">
                            <button type="button" data-minus aria-label="Decrease quantity">-</button>
                            <input type="text" name="quantity" value="1" readonly aria-label="Quantity">
                            <button type="button" data-plus aria-label="Increase quantity">+</button>
                        </div>
                        <span class="small text-muted">Only <?php echo $available; ?> items available</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-cyan btn-lg flex-fill" name="action" value="buy_now" <?php echo $buy_disabled; ?>>Buy Now</button>
                        <button class="btn btn-orange btn-lg flex-fill" name="action" value="add_cart" <?php echo $buy_disabled; ?>>Add to Cart</button>
                    </div>
                </form>
                <form method="post" class="mt-2">
                    <input type="hidden" name="action" value="toggle_wish">
                    <button class="btn btn-outline-secondary w-100" type="submit"><i class="bx <?php if ($is_wished) { echo 'bxs-heart text-orange'; } else { echo 'bx-heart'; } ?>"></i> <?php if ($is_wished) { echo 'Saved to Wishlist (click to remove)'; } else { echo 'Add to Wishlist'; } ?></button>
                </form>
            </div>

            <aside class="col-lg-3">
                <div class="side-panel p-3 h-100">
                    <h2 class="h6 text-muted">Delivery Options</h2>
                    <?php while ($warehouse = mysqli_fetch_assoc($warehouses)) { ?>
                        <p class="small mb-2"><i class="bx bx-map text-orange"></i> <strong><?php echo htmlspecialchars($warehouse['warehouse_name']); ?></strong><br><?php echo htmlspecialchars($warehouse['location_address']); ?></p>
                    <?php } ?>
                    <p class="small mb-2"><i class="bx bx-run text-orange"></i> Standard delivery from our fulfillment warehouse</p>
                    <p class="small mb-3"><i class="bx bx-money text-orange"></i> Cash on Delivery Available<br><span class="badge text-bg-light border">bKash</span> <span class="badge text-bg-light border">Nagad</span></p>
                    <hr>
                    <h2 class="h6 text-muted">Return &amp; Warranty</h2>
                    <p class="small mb-2"><i class="bx bx-shield-quarter text-orange"></i> <?php echo htmlspecialchars((string) $product['warranty_period']); ?></p>
                    <p class="small mb-0"><i class="bx bx-check-circle text-orange"></i> Verified Warehouse Stock</p>
                </div>
            </aside>
        </div></div></div>

        <div class="row g-2">
            <section class="col-12">
                <div class="card"><div class="card-body">
                    <h2 class="h5">Product Details &amp; Specifications</h2>
                    <?php if ($spec_total == 0) { ?>
                        <p class="text-muted mb-0">Specifications have not been added for this product yet.</p>
                    <?php } else { ?>
                        <ul class="mb-0">
                            <?php $s = 0; while ($s < $spec_total) { ?>
                                <li><?php echo htmlspecialchars(trim($spec_lines[$s])); ?></li>
                            <?php $s = $s + 1; } ?>
                        </ul>
                    <?php } ?>
                </div></div>
            </section>

            <section class="col-12" id="reviews">
                <div class="card"><div class="card-body">
                    <h2 class="h5">Ratings &amp; Reviews</h2>
                    <p class="mb-3"><span class="fs-2 fw-medium"><?php echo number_format((float) $rating_row['average_rating'], 1); ?></span><span class="text-muted"> / 5 &middot; <?php echo (int) $rating_row['review_count']; ?> ratings</span></p>
                    <?php while ($review = mysqli_fetch_assoc($reviews)) { ?>
                        <div class="border-bottom py-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($review['created_at'], 0, 10)); ?></small>
                            </div>
                            <div class="stars"><?php $star = 1; while ($star <= 5) { if ($star <= (int) $review['rating']) { echo '&#9733;'; } else { echo '<span class="off">&#9733;</span>'; } $star = $star + 1; } ?></div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) $review['comment'])); ?></p>
                        </div>
                    <?php } ?>
                    <?php if (mysqli_num_rows($reviews) == 0) { ?>
                        <p class="text-muted">No reviews yet. Be the first to share how it works for you.</p>
                    <?php } ?>

                    <h3 class="h6 mt-4">Write a review</h3>
                    <?php if ($review_error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($review_error); ?></div><?php } ?>
                    <?php if (isset($_SESSION['user_id']) == false) { ?>
                        <p class="mb-0"><a href="login.php">Sign in</a> with a customer account to write a review.</p>
                    <?php } elseif ($customer_id == 0) { ?>
                        <p class="mb-0 text-muted">Reviews can be written from customer accounts.</p>
                    <?php } elseif ($already_reviewed) { ?>
                        <p class="mb-0 text-muted">You have already reviewed this product.</p>
                    <?php } else { ?>
                        <form method="post" action="product.php?id=<?php echo $id; ?>#reviews">
                            <input type="hidden" name="action" value="review">
                            <div class="mb-3" style="max-width:220px">
                                <label class="form-label" for="rating">Rating</label>
                                <select class="form-select" id="rating" name="rating">
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Good</option>
                                    <option value="3">3 - Average</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="comment">Your review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" maxlength="1000"><?php echo htmlspecialchars($comment); ?></textarea>
                            </div>
                            <button class="btn btn-orange" type="submit">Post review</button>
                        </form>
                    <?php } ?>
                </div></div>
            </section>
        </div>

        <?php if ($related && mysqli_num_rows($related) > 0) { ?>
            <?php $page_product = $product; ?>
            <h2 class="product-strip-title">You May Also Like</h2>
            <section class="mb-2">
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 row-cols-xl-6 g-2">
                    <?php while ($product = mysqli_fetch_assoc($related)) { ?>
                        <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                    <?php } ?>
                </div>
            </section>
            <?php $product = $page_product; ?>
        <?php } ?>

        <?php $recent_exclude = $id; include __DIR__ . '/includes/recent_strip.php'; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

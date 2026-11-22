<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$q = '';
$category_id = 0;
$brand = '';
$min_price = '';
$max_price = '';
$in_stock = '';
$sort = 'newest';
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
}
if (isset($_GET['category_id'])) {
    $category_id = (int) $_GET['category_id'];
}
if (isset($_GET['brand'])) {
    $brand = $_GET['brand'];
}
if (isset($_GET['min_price'])) {
    $min_price = trim($_GET['min_price']);
}
if (isset($_GET['max_price'])) {
    $max_price = trim($_GET['max_price']);
}
if (isset($_GET['in_stock'])) {
    $in_stock = $_GET['in_stock'];
}
if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
}

// Add to Cart / Buy Now
include __DIR__ . '/includes/cart_actions.php';

// Quote the text filters for SQL
$sql_text = array('q' => $q, 'brand' => $brand);
include __DIR__ . '/includes/quote_fields.php';

$rating_sql = '(SELECT AVG(r.rating) FROM product_reviews r WHERE r.product_id=v.product_id) AS avg_rating, (SELECT COUNT(*) FROM product_reviews r2 WHERE r2.product_id=v.product_id) AS review_count';
$sql = "SELECT v.*, $rating_sql FROM vw_storefront_products v WHERE 1=1";
if ($q != '') {
    $sql = $sql . " AND (v.product_name LIKE '%$sql_text[q]%' OR v.sku LIKE '%$sql_text[q]%' OR v.brand LIKE '%$sql_text[q]%')";
}
if ($category_id > 0) {
    $sql = $sql . " AND (v.category_id=$category_id OR v.category_id IN (SELECT category_id FROM categories WHERE parent_id=$category_id))";
}
if ($brand != '') {
    $sql = $sql . " AND v.brand='$sql_text[brand]'";
}
if ($min_price != '' && is_numeric($min_price)) {
    $sql = $sql . ' AND v.selling_price >= ' . (float) $min_price;
}
if ($max_price != '' && is_numeric($max_price)) {
    $sql = $sql . ' AND v.selling_price <= ' . (float) $max_price;
}
if ($in_stock == '1') {
    $sql = $sql . ' AND v.available_to_sell > 0';
}
if ($sort == 'price_low') {
    $sql = $sql . ' ORDER BY v.selling_price ASC';
} elseif ($sort == 'price_high') {
    $sql = $sql . ' ORDER BY v.selling_price DESC';
} else {
    $sort = 'newest';
    $sql = $sql . ' ORDER BY v.product_id DESC';
}
$products = mysqli_query($conn, $sql);
$product_total = 0;
if ($products) {
    $product_total = mysqli_num_rows($products);
}
$cat_ids = array();
$cat_parents = array();
$cat_names = array();
$cat_result = mysqli_query($conn, 'SELECT category_id, parent_id, name FROM categories ORDER BY name');
while ($cat_row = mysqli_fetch_assoc($cat_result)) {
    $cat_ids[] = (int) $cat_row['category_id'];
    $cat_parents[] = (int) $cat_row['parent_id'];
    $cat_names[] = $cat_row['name'];
}
$cat_total = count($cat_ids);
$open_category = '';
$i = 0;
while ($i < $cat_total) {
    if ($cat_ids[$i] == $category_id) {
        $open_category = $cat_names[$i];
    }
    $i = $i + 1;
}
$brands = mysqli_query($conn, "SELECT DISTINCT brand FROM vw_storefront_products WHERE brand IS NOT NULL AND brand<>'' ORDER BY brand");
?>
<!doctype html>
<html lang="en">
<head>
    <title>Shop | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
            <?php if ($open_category != '') { ?><li class="breadcrumb-item active"><?php echo htmlspecialchars($open_category); ?></li><?php } elseif ($q != '') { ?><li class="breadcrumb-item active">Search: <?php echo htmlspecialchars($q); ?></li><?php } ?>
        </ol></nav>
        <div class="row g-2">

            <aside class="col-lg-3">
                <form class="card" method="get" action="shop.php">
                    <div class="card-header bg-white fw-bold">Filters</div>
                    <div class="card-body">
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <div class="fw-bold mb-1">Categories</div>
                        <div class="mb-3">
                            <a class="d-block py-1 text-decoration-none text-dark" href="shop.php">All products</a>
                            <?php $i = 0; while ($i < $cat_total) { ?>
                                <?php if ($cat_parents[$i] == 0) { ?>
                                    <a class="d-block py-1 text-decoration-none <?php if ($category_id == $cat_ids[$i]) { echo 'text-orange fw-bold'; } else { echo 'text-dark'; } ?>" href="shop.php?category_id=<?php echo $cat_ids[$i]; ?>"><?php echo htmlspecialchars($cat_names[$i]); ?></a>
                                    <?php $j = 0; while ($j < $cat_total) { ?>
                                        <?php if ($cat_parents[$j] == $cat_ids[$i]) { ?>
                                            <a class="d-block py-1 ps-3 small text-decoration-none <?php if ($category_id == $cat_ids[$j]) { echo 'text-orange fw-bold'; } else { echo 'text-muted'; } ?>" href="shop.php?category_id=<?php echo $cat_ids[$j]; ?>"><?php echo htmlspecialchars($cat_names[$j]); ?></a>
                                        <?php } ?>
                                    <?php $j = $j + 1; } ?>
                                <?php } ?>
                            <?php $i = $i + 1; } ?>
                        </div>
                        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                        <label class="form-label" for="f_brand">Brand</label>
                        <select class="form-select mb-3" id="f_brand" name="brand">
                            <option value="">All brands</option>
                            <?php while ($brand_row = mysqli_fetch_assoc($brands)) { ?>
                                <option value="<?php echo htmlspecialchars($brand_row['brand']); ?>" <?php if ($brand == $brand_row['brand']) { echo 'selected'; } ?>><?php echo htmlspecialchars($brand_row['brand']); ?></option>
                            <?php } ?>
                        </select>
                        <label class="form-label">Price range</label>
                        <div class="input-group mb-3">
                            <input class="form-control" type="number" min="0" step="any" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($min_price); ?>">
                            <span class="input-group-text">-</span>
                            <input class="form-control" type="number" min="0" step="any" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($max_price); ?>">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="f_stock" name="in_stock" value="1" <?php if ($in_stock == '1') { echo 'checked'; } ?>>
                            <label class="form-check-label" for="f_stock">In-Stock Only</label>
                        </div>
                        <button class="btn btn-orange w-100 mb-2" type="submit">Apply Filters</button>
                        <a class="btn btn-outline-secondary w-100" href="shop.php">Clear Filters</a>
                    </div>
                </form>
            </aside>

            <section class="col-lg-9">
                <div class="card mb-3">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
                        <div>
                            <strong><?php echo $product_total; ?> items found</strong>
                            <?php if ($q != '') { ?><span class="text-muted"> for &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;</span><?php } ?>
                        </div>
                        <form class="d-flex align-items-center gap-2" method="get" action="shop.php">
                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                            <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
                            <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
                            <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
                            <?php if ($in_stock == '1') { ?><input type="hidden" name="in_stock" value="1"><?php } ?>
                            <label class="form-label mb-0 small" for="sort">Sort</label>
                            <select class="form-select form-select-sm" id="sort" name="sort" onchange="this.form.submit()">
                                <option value="newest" <?php if ($sort == 'newest') { echo 'selected'; } ?>>Best Match / Newest</option>
                                <option value="price_low" <?php if ($sort == 'price_low') { echo 'selected'; } ?>>Price low to high</option>
                                <option value="price_high" <?php if ($sort == 'price_high') { echo 'selected'; } ?>>Price high to low</option>
                            </select>
                        </form>
                    </div>
                </div>

                <?php if ($product_total == 0) { ?>
                    <div class="card"><div class="card-body text-center py-5">
                        <h2 class="h4">No products found</h2>
                        <p class="text-muted">Try a different search or clear your filters.</p>
                        <a class="btn btn-orange" href="shop.php">Show all products</a>
                    </div></div>
                <?php } else { ?>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2">
                        <?php while ($product = mysqli_fetch_assoc($products)) { ?>
                            <div class="col"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </section>

        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

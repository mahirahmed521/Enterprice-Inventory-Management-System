<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
$search = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}
require 'config/db.php';
$sql = "SELECT products.product_id, products.sku, products.product_name, products.selling_price,
               products.unit_of_measure, vw_inventory_valuation.available_to_sell
        FROM products
        JOIN vw_inventory_valuation USING(product_id)
        WHERE products.is_active=1";
if (isset($search[0])) {
    $sql_text = [
        'search' => $search
    ];
    include __DIR__ . '/includes/quote_fields.php';
    $term = $sql_text['search'];
    $sql = $sql . " AND (products.sku LIKE '%$term%' OR products.product_name LIKE '%$term%')";
}
$rows = mysqli_query($conn, $sql . ' ORDER BY products.product_name, products.sku');
if ($rows == false) {
    exit('Could not load the catalog.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Catalog | Stockroom</title>
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="stylesheet" href="assets/css/stockroom.css">
</head>
<body>
<header class="site-header"><div class="bar"><a class="brand" href="index.html"><i class="bx bx-package" aria-hidden="true"></i>Stockroom<span>.</span></a><nav aria-label="Main navigation"><a href="admin/sales_orders.php">My orders</a>
        <?php if (isset($_SESSION['user_id'])) { ?><a href="admin/dashboard.php">My dashboard</a>
        <?php } else { ?><a href="admin/index.php">Sign in</a><?php } ?>
    </nav></div></header>
<main>
    <div class="catalog-head"><h1>Product catalog</h1>
    <form class="catalog-filter"><label>Search by name or SKU <input name="q" value="<?php echo htmlspecialchars($search); ?>"></label> <button>Search</button></form></div>
    <div class="grid">
    <?php while ($product = mysqli_fetch_assoc($rows)) { ?>
        <article class="product-card"><i class="bx bx-package" aria-hidden="true"></i>
            <small><?php echo htmlspecialchars($product['sku']); ?></small>
            <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p class="price"><?php echo htmlspecialchars($product['selling_price']); ?> / <?php echo htmlspecialchars($product['unit_of_measure']); ?></p>
            <p><?php echo htmlspecialchars($product['available_to_sell']); ?> available across warehouses</p>
            <a href="admin/sales_orders.php?product=<?php echo htmlspecialchars($product['product_id']); ?>">Order this product <span aria-hidden="true">→</span></a>
        </article>
    <?php } ?>
    </div>
    <?php if ($rows->num_rows == 0) { ?><p>No matching products.</p><?php } ?>
</main>
</body>
</html>

<?php
// One product card. Expects $product (a vw_storefront_products row plus avg_rating and review_count) and $currency.
$p_img = $product['product_image'];
if ($p_img == '') {
    $p_img = 'uploads/products/default.png';
}
$stock_status = $product['stock_status'];
include __DIR__ . '/stock_badge.php';
$rounded_rating = (int) round((float) $product['avg_rating']);
$buy_disabled = '';
if ($product['available_to_sell'] <= 0) {
    $buy_disabled = 'disabled';
}
?>
<div class="product-card d-flex flex-column">
    <a class="product-thumb" href="product.php?id=<?php echo $product['product_id']; ?>"><img src="<?php echo htmlspecialchars($p_img); ?>" onerror="this.src='uploads/products/default.png'" alt="<?php echo htmlspecialchars($product['product_name']); ?>"></a>
    <div class="p-2 d-flex flex-column flex-grow-1">
        <div class="product-brand text-truncate"><?php echo htmlspecialchars((string) $product['brand']); ?><?php if ($product['category_name'] != '') { echo ' &middot; ' . htmlspecialchars((string) $product['category_name']); } ?></div>
        <h3 class="product-title"><a href="product.php?id=<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a></h3>
        <div class="price fs-5"><?php echo $currency . number_format((float) $product['selling_price'], 2); ?></div>
        <div class="small mb-1">
            <span class="stars"><?php $star = 1; while ($star <= 5) { if ($star <= $rounded_rating) { echo '&#9733;'; } else { echo '<span class="off">&#9733;</span>'; } $star = $star + 1; } ?></span>
            <span class="text-muted">(<?php echo (int) $product['review_count']; ?>)</span>
        </div>
        <div class="mb-2"><span class="badge <?php echo $stock_class; ?> stock-text"><?php echo $stock_label; ?></span></div>
        <div class="d-flex gap-1 mt-auto">
            <form method="post" class="flex-fill"><input type="hidden" name="action" value="buy_now"><input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>"><button class="btn btn-cyan btn-sm w-100 px-1" <?php echo $buy_disabled; ?>>Buy Now</button></form>
            <form method="post" class="flex-fill"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>"><button class="btn btn-orange btn-sm w-100 px-1" <?php echo $buy_disabled; ?>>Add to Cart</button></form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$shipping_fee = 0;
$action = '';
$product_id = 0;
$quantity = 1;
$removed_note = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if (isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
}
if (isset($_POST['quantity'])) {
    $quantity = (int) $_POST['quantity'];
}
if (isset($_SESSION['cart']) == false) {
    $_SESSION['cart'] = array();
}

// Update, remove or clear
if ($action == 'update') {
    if ($quantity < 1) {
        $quantity = 1;
    }
    $stock_result = mysqli_query($conn, "SELECT available_to_sell FROM vw_storefront_products WHERE product_id=$product_id");
    $stock_row = mysqli_fetch_assoc($stock_result);
    if ($stock_row) {
        $available = (int) $stock_row['available_to_sell'];
        if ($quantity > $available) {
            $quantity = $available;
            $removed_note = 'Only ' . $available . ' in stock, so the quantity was lowered.';
        }
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
} elseif ($action == 'remove') {
    unset($_SESSION['cart'][$product_id]);
} elseif ($action == 'clear') {
    $_SESSION['cart'] = array();
}

// Build the cart lines from live stock and prices
$cart_items = array();
$subtotal = 0;
$estimated_tax = 0;
$cart_keys = array_keys($_SESSION['cart']);
$key_total = count($cart_keys);
$i = 0;
while ($i < $key_total) {
    $pid = (int) $cart_keys[$i];
    $qty = (int) $_SESSION['cart'][$pid];
    $result = mysqli_query($conn, "SELECT * FROM vw_storefront_products WHERE product_id=$pid");
    $line = mysqli_fetch_assoc($result);
    if ($line == null) {
        unset($_SESSION['cart'][$pid]);
        $removed_note = 'Some items are no longer sold and were removed from your cart.';
    } else {
        $available = (int) $line['available_to_sell'];
        if ($available <= 0) {
            unset($_SESSION['cart'][$pid]);
            $removed_note = $line['product_name'] . ' is out of stock and was removed from your cart.';
        } else {
            if ($qty > $available) {
                $qty = $available;
                $_SESSION['cart'][$pid] = $qty;
                $removed_note = 'Some quantities were lowered to match live stock.';
            }
            $line['cart_quantity'] = $qty;
            $cart_items[] = $line;
            $line_total = $qty * (float) $line['selling_price'];
            $subtotal = $subtotal + $line_total;
            $estimated_tax = $estimated_tax + ($line_total * (float) $line['tax_rate'] / 100);
        }
    }
    $i = $i + 1;
}
$item_total = count($cart_items);
$grand_total = $subtotal + $estimated_tax + $shipping_fee;
?>
<!doctype html>
<html lang="en">
<head>
    <title>Shopping Cart | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <h1 class="h3 mb-3">Shopping Cart</h1>
        <?php if ($removed_note != '') { ?><div class="alert alert-warning"><?php echo htmlspecialchars($removed_note); ?></div><?php } ?>

        <?php if ($item_total == 0) { ?>
            <div class="card"><div class="card-body text-center py-5">
                <i class="bx bx-cart display-4 text-orange"></i>
                <h2 class="h4">Your cart is empty</h2>
                <p class="text-muted">Pick something from the live warehouse catalog.</p>
                <a class="btn btn-orange" href="shop.php">Continue Shopping</a>
            </div></div>
        <?php } else { ?>
        <div class="row g-2">
            <section class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><?php echo $item_total; ?> item(s) in your cart</strong>
                        <form method="post"><input type="hidden" name="action" value="clear"><button class="btn btn-sm btn-outline-secondary" type="submit">Clear Cart</button></form>
                    </div>
                    <div class="card-body">
                        <?php $n = 0; while ($n < $item_total) { ?>
                            <?php
                            $item = $cart_items[$n];
                            $p_img = product_image_url($item['product_image']);
                            $stock_status = $item['stock_status'];
                            include __DIR__ . '/includes/stock_badge.php';
                            ?>
                            <div class="row g-3 align-items-center py-3 border-bottom">
                                <div class="col-auto"><img class="cart-thumb" src="<?php echo htmlspecialchars($p_img); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></div>
                                <div class="col">
                                    <a class="fw-semibold text-decoration-none" href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                    <div class="small text-muted"><?php echo htmlspecialchars((string) $item['brand']); ?></div>
                                    <span class="badge <?php echo $stock_class; ?>"><?php echo $stock_label; ?></span>
                                    <span class="small text-muted"><?php echo (int) $item['available_to_sell']; ?> available</span>
                                    <div class="price"><?php echo $currency . number_format((float) $item['selling_price'], 2); ?> each</div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <form class="mb-2" method="post" data-autosubmit>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <div class="qty-stepper" data-quantity-stepper data-max="<?php echo (int) $item['available_to_sell']; ?>">
                                            <button type="button" data-minus aria-label="Decrease quantity">-</button>
                                            <input type="text" name="quantity" value="<?php echo $item['cart_quantity']; ?>" readonly aria-label="Quantity">
                                            <button type="button" data-plus aria-label="Increase quantity">+</button>
                                        </div>
                                    </form>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="price"><?php echo $currency . number_format($item['cart_quantity'] * (float) $item['selling_price'], 2); ?></strong>
                                        <form method="post"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>"><button class="btn btn-sm btn-outline-secondary" type="submit" aria-label="Remove item" title="Remove"><i class="bx bx-trash"></i></button></form>
                                    </div>
                                </div>
                            </div>
                        <?php $n = $n + 1; } ?>
                    </div>
                </div>
            </section>

            <aside class="col-lg-4">
                <div class="card sticky-summary"><div class="card-body">
                    <h2 class="h5">Order Summary</h2>
                    <div class="d-flex justify-content-between my-2"><span>Subtotal</span><strong><?php echo $currency . number_format($subtotal, 2); ?></strong></div>
                    <div class="d-flex justify-content-between my-2"><span>Estimated Tax</span><strong><?php echo $currency . number_format($estimated_tax, 2); ?></strong></div>
                    <div class="d-flex justify-content-between my-2"><span>Shipping Fee</span><strong><?php echo $currency . number_format($shipping_fee, 2); ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><span class="fw-bold">Grand Total</span><strong class="price fs-4"><?php echo $currency . number_format($grand_total, 2); ?></strong></div>
                    <a class="btn btn-orange w-100 btn-lg" href="checkout.php">Proceed to Checkout</a>
                    <a class="btn btn-link w-100 mt-1" href="shop.php">Continue Shopping</a>
                </div></div>
            </aside>
        </div>
        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

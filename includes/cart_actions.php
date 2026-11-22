<?php
// Add to Cart and Buy Now posts. The page must start the session and load $conn first.
// The wishlist heart on the product cards is handled here too.
include __DIR__ . '/wishlist_actions.php';
$cart_action = '';
$cart_product_id = 0;
$cart_quantity = 1;
$cart_return = 'home.php';
if (isset($_POST['action'])) {
    $cart_action = $_POST['action'];
}
if (isset($_POST['product_id'])) {
    $cart_product_id = (int) $_POST['product_id'];
}
if (isset($_POST['quantity'])) {
    $cart_quantity = (int) $_POST['quantity'];
}
if (isset($_SERVER['REQUEST_URI'])) {
    $cart_return = $_SERVER['REQUEST_URI'];
}
if (isset($_SESSION['cart']) == false) {
    $_SESSION['cart'] = array();
}
if ($cart_action == 'add_cart' || $cart_action == 'buy_now') {
    if ($cart_quantity < 1) {
        $cart_quantity = 1;
    }
    $stock_result = mysqli_query($conn, "SELECT product_name, available_to_sell FROM vw_storefront_products WHERE product_id=$cart_product_id");
    $stock_row = null;
    if ($stock_result) {
        $stock_row = mysqli_fetch_assoc($stock_result);
    }
    $go_to = $cart_return;
    if ($stock_row == null) {
        $_SESSION['cart_error'] = 'That product is not available.';
    } else {
        $available = (int) $stock_row['available_to_sell'];
        $in_cart = 0;
        if (isset($_SESSION['cart'][$cart_product_id])) {
            $in_cart = (int) $_SESSION['cart'][$cart_product_id];
        }
        $new_quantity = $in_cart + $cart_quantity;
        if ($available <= 0) {
            $_SESSION['cart_error'] = $stock_row['product_name'] . ' is out of stock.';
        } else {
            if ($new_quantity > $available) {
                $new_quantity = $available;
                $_SESSION['cart_error'] = 'Only ' . $available . ' of ' . $stock_row['product_name'] . ' are in stock, so your cart holds that many.';
            }
            $_SESSION['cart'][$cart_product_id] = $new_quantity;
            $_SESSION['cart_notice'] = $stock_row['product_name'] . ' was added to your cart.';
            if ($cart_action == 'buy_now') {
                $go_to = 'checkout.php';
            }
        }
    }
    header('Location: ' . $go_to);
    exit;
}

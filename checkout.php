<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$shipping_fee = 0;
$user_id = 0;
$posted = false;
$customer_name = '';
$shipping_address = '';
$phone = '';
$warehouse_id = 0;
$payment_method = 'COD';
$transaction_id = '';
$error = '';
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = true;
}
if (isset($_POST['customer_name'])) {
    $customer_name = trim($_POST['customer_name']);
}
if (isset($_POST['shipping_address'])) {
    $shipping_address = trim($_POST['shipping_address']);
}
if (isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);
}
if (isset($_POST['warehouse_id'])) {
    $warehouse_id = (int) $_POST['warehouse_id'];
}
if (isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
}
if (isset($_POST['transaction_id'])) {
    $transaction_id = trim($_POST['transaction_id']);
}

// Guests sign in first and come back here
if ($user_id == 0) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}
if (isset($_SESSION['cart']) == false) {
    $_SESSION['cart'] = array();
}

// Only customer accounts can order
include __DIR__ . '/includes/current_customer.php';
$can_order = false;
if ($customer_id > 0) {
    $can_order = true;
}
if ($can_order && $posted == false) {
    $customer_name = (string) $customer['customer_name'];
    $shipping_address = (string) $customer['shipping_address'];
    $phone = (string) $customer['phone'];
}

// Cart lines with live prices
$line_ids = array();
$line_qtys = array();
$line_names = array();
$line_prices = array();
$line_images = array();
$subtotal = 0;
$estimated_tax = 0;
$cart_keys = array_keys($_SESSION['cart']);
$key_total = count($cart_keys);
$i = 0;
while ($i < $key_total) {
    $pid = (int) $cart_keys[$i];
    $qty = (int) $_SESSION['cart'][$pid];
    $result = mysqli_query($conn, "SELECT product_id, product_name, product_image, selling_price, tax_rate FROM vw_storefront_products WHERE product_id=$pid");
    $row = mysqli_fetch_assoc($result);
    if ($row == null) {
        unset($_SESSION['cart'][$pid]);
    } else {
        $line_ids[] = $pid;
        $line_qtys[] = $qty;
        $line_names[] = $row['product_name'];
        $line_prices[] = (float) $row['selling_price'];
        $p_img = $row['product_image'];
        if ($p_img == '') {
            $p_img = 'uploads/products/default.png';
        }
        $line_images[] = $p_img;
        $line_total = $qty * (float) $row['selling_price'];
        $subtotal = $subtotal + $line_total;
        $estimated_tax = $estimated_tax + ($line_total * (float) $row['tax_rate'] / 100);
    }
    $i = $i + 1;
}
$line_count = count($line_ids);
$grand_total = $subtotal + $estimated_tax + $shipping_fee;

// Which warehouses can ship the whole cart
$wh_ids = array();
$wh_labels = array();
$wh_can = array();
$any_warehouse = false;
$wh_result = mysqli_query($conn, 'SELECT warehouse_id, warehouse_name, location_address FROM warehouses WHERE is_active=1 ORDER BY warehouse_name');
while ($wh = mysqli_fetch_assoc($wh_result)) {
    $wid = (int) $wh['warehouse_id'];
    $fits = 1;
    $k = 0;
    while ($k < $line_count) {
        $free_result = mysqli_query($conn, "SELECT quantity_on_hand - quantity_reserved AS free_qty FROM warehouse_stock WHERE warehouse_id=$wid AND product_id=" . $line_ids[$k]);
        $free_row = mysqli_fetch_assoc($free_result);
        $free_qty = 0;
        if ($free_row) {
            $free_qty = (int) $free_row['free_qty'];
        }
        if ($free_qty < $line_qtys[$k]) {
            $fits = 0;
        }
        $k = $k + 1;
    }
    $wh_ids[] = $wid;
    $wh_labels[] = $wh['warehouse_name'] . ' - ' . $wh['location_address'];
    $wh_can[] = $fits;
    if ($fits == 1) {
        $any_warehouse = true;
    }
}
$wh_count = count($wh_ids);
if ($warehouse_id == 0) {
    $j = 0;
    while ($j < $wh_count) {
        if ($wh_can[$j] == 1 && $warehouse_id == 0) {
            $warehouse_id = $wh_ids[$j];
        }
        $j = $j + 1;
    }
}

// Place the order
if ($posted && $can_order && $line_count > 0) {
    $warehouse_ok = false;
    $j = 0;
    while ($j < $wh_count) {
        if ($wh_ids[$j] == $warehouse_id && $wh_can[$j] == 1) {
            $warehouse_ok = true;
        }
        $j = $j + 1;
    }
    $valid_methods = array('COD', 'BKASH', 'NAGAD', 'CARD', 'BANK');
    if ($customer_name == '') {
        $error = 'Enter your name.';
    } elseif (strlen($customer_name) > 100) {
        $error = 'Use a shorter name.';
    } elseif ($shipping_address == '') {
        $error = 'Enter your shipping address.';
    } elseif ($warehouse_ok == false) {
        $error = 'Choose a warehouse that has all of your items in stock.';
    } elseif (in_array($payment_method, $valid_methods) == false) {
        $error = 'Choose a payment method.';
    } elseif (strlen($transaction_id) > 100) {
        $error = 'The transaction ID is too long.';
    }

    if ($error == '') {
        $sql_text = array('customer_name' => $customer_name, 'shipping_address' => $shipping_address, 'phone' => $phone, 'transaction_id' => $transaction_id);
        include __DIR__ . '/includes/quote_fields.php';
        $transaction_sql = 'NULL';
        if ($payment_method != 'COD' && $sql_text['transaction_id'] != '') {
            $transaction_sql = "'" . $sql_text['transaction_id'] . "'";
        }
        $total_text = number_format($grand_total, 2, '.', '');
        $fee_text = number_format($shipping_fee, 2, '.', '');
        $temp_number = 'TMP-' . uniqid();
        $order_ok = true;
        $failed_name = '';
        $so_id = 0;

        mysqli_query($conn, 'START TRANSACTION');
        $sql = "UPDATE customers SET customer_name='$sql_text[customer_name]', phone='$sql_text[phone]', shipping_address='$sql_text[shipping_address]' WHERE customer_id=$customer_id";
        if (mysqli_query($conn, $sql) == false) {
            $order_ok = false;
        }
        if ($order_ok) {
            $sql = "INSERT INTO sales_orders (so_number, customer_id, warehouse_id, order_source, status, payment_method, payment_status, transaction_id, shipping_fee, shipping_address, order_date, total_amount, created_by)
                    VALUES ('$temp_number', $customer_id, $warehouse_id, 'ECOMMERCE', 'PENDING', '$payment_method', 'UNPAID', $transaction_sql, '$fee_text', '$sql_text[shipping_address]', CURDATE(), '$total_text', $user_id)";
            if (mysqli_query($conn, $sql)) {
                $so_id = mysqli_insert_id($conn);
                $so_number = 'SO-WEB-' . (1000 + $so_id);
                if (mysqli_query($conn, "UPDATE sales_orders SET so_number='$so_number' WHERE so_id=$so_id") == false) {
                    $order_ok = false;
                }
            } else {
                $order_ok = false;
            }
        }
        // Each item insert fires trg_reserve_stock_on_sale
        $k = 0;
        while ($k < $line_count && $order_ok) {
            $sql = "INSERT INTO sales_order_items (so_id, product_id, quantity, unit_price) VALUES ($so_id, " . $line_ids[$k] . ', ' . $line_qtys[$k] . ", '" . number_format($line_prices[$k], 2, '.', '') . "')";
            if (mysqli_query($conn, $sql) == false) {
                $order_ok = false;
                $failed_name = $line_names[$k];
            }
            $k = $k + 1;
        }

        if ($order_ok) {
            mysqli_query($conn, 'COMMIT');
            $_SESSION['cart'] = array();
            header('Location: order_success.php?so_id=' . $so_id);
            exit;
        }
        mysqli_query($conn, 'ROLLBACK');
        if ($failed_name != '') {
            $error = 'Not enough stock for ' . $failed_name . ' in that warehouse. Someone may have just bought it. Please review your cart.';
        } else {
            $error = 'We could not place your order. Please try again.';
        }
    }
}

$pay_ids = array('COD', 'BKASH', 'NAGAD', 'CARD', 'BANK');
$pay_names = array('Cash on Delivery', 'bKash', 'Nagad', 'Card', 'Bank');
$pay_icons = array('bx-money', 'bx-wallet', 'bx-wallet', 'bx-credit-card', 'bx-building');
?>
<!doctype html>
<html lang="en">
<head>
    <title>Checkout | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <h1 class="h3 mb-3">Checkout</h1>

        <?php if ($can_order == false) { ?>
            <div class="alert alert-warning">
                You are signed in with a team account. Orders can only be placed from a customer account.
                <a href="logout.php">Log out</a> and sign in or sign up as a customer to continue.
            </div>
        <?php } elseif ($line_count == 0) { ?>
            <div class="card"><div class="card-body text-center py-5">
                <h2 class="h4">Your cart is empty</h2>
                <a class="btn btn-orange" href="shop.php">Continue Shopping</a>
            </div></div>
        <?php } else { ?>
        <?php if ($error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <?php if ($any_warehouse == false) { ?>
            <div class="alert alert-warning">No single warehouse has every item in your cart in the quantities you chose. <a href="cart.php">Lower the quantities in your cart</a> or order the items separately.</div>
        <?php } ?>
        <form method="post" class="row g-2">
            <div class="col-lg-8">

                <div class="card mb-3"><div class="card-body">
                    <h2 class="h5">Shipping &amp; Billing</h2>
                    <div class="mb-3">
                        <label class="form-label" for="customer_name">Full name</label>
                        <input class="form-control" type="text" id="customer_name" name="customer_name" maxlength="100" value="<?php echo htmlspecialchars($customer_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="shipping_address">Delivery address</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label" for="phone">Phone</label>
                            <input class="form-control" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="warehouse_id">Ships from</label>
                            <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                <?php $j = 0; while ($j < $wh_count) { ?>
                                    <option value="<?php echo $wh_ids[$j]; ?>" <?php if ($warehouse_id == $wh_ids[$j]) { echo 'selected'; } ?> <?php if ($wh_can[$j] == 0) { echo 'disabled'; } ?>><?php echo htmlspecialchars($wh_labels[$j]); ?><?php if ($wh_can[$j] == 0) { echo ' (not enough stock)'; } ?></option>
                                <?php $j = $j + 1; } ?>
                            </select>
                        </div>
                    </div>
                </div></div>

                <div class="card mb-3"><div class="card-body">
                    <h2 class="h5">Delivery Package</h2>
                    <?php $k = 0; while ($k < $line_count) { ?>
                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                            <img class="cart-thumb" src="<?php echo htmlspecialchars($line_images[$k]); ?>" onerror="this.src='uploads/products/default.png'" alt="">
                            <div class="flex-grow-1"><strong><?php echo htmlspecialchars($line_names[$k]); ?></strong><div class="small text-muted">Quantity: <?php echo $line_qtys[$k]; ?></div></div>
                            <strong class="price"><?php echo $currency . number_format($line_qtys[$k] * $line_prices[$k], 2); ?></strong>
                        </div>
                    <?php $k = $k + 1; } ?>
                </div></div>

                <div class="card"><div class="card-body">
                    <h2 class="h5">Select Payment Method</h2>
                    <div class="row row-cols-2 row-cols-md-5 g-2 mb-3">
                        <?php $m = 0; while ($m < 5) { ?>
                            <div class="col">
                                <input class="btn-check" type="radio" name="payment_method" id="pay_<?php echo $pay_ids[$m]; ?>" value="<?php echo $pay_ids[$m]; ?>" <?php if ($payment_method == $pay_ids[$m]) { echo 'checked'; } ?>>
                                <label class="btn btn-outline-orange pay-card w-100" for="pay_<?php echo $pay_ids[$m]; ?>"><i class="bx <?php echo $pay_icons[$m]; ?>"></i><span><?php echo $pay_names[$m]; ?></span></label>
                            </div>
                        <?php $m = $m + 1; } ?>
                    </div>
                    <div data-transaction-field <?php if ($payment_method == 'COD') { echo 'hidden'; } ?>>
                        <label class="form-label" for="transaction_id">Transaction ID (optional)</label>
                        <input class="form-control" type="text" id="transaction_id" name="transaction_id" maxlength="100" value="<?php echo htmlspecialchars($transaction_id); ?>" placeholder="Paste the ID from your payment receipt">
                        <div class="form-text">Pay first, then enter the ID so the team can mark your order as paid.</div>
                    </div>
                </div></div>
            </div>

            <aside class="col-lg-4">
                <div class="card sticky-summary"><div class="card-body">
                    <h2 class="h5">Order Summary</h2>
                    <div class="d-flex justify-content-between my-2"><span>Subtotal</span><strong><?php echo $currency . number_format($subtotal, 2); ?></strong></div>
                    <div class="d-flex justify-content-between my-2"><span>Estimated Tax</span><strong><?php echo $currency . number_format($estimated_tax, 2); ?></strong></div>
                    <div class="d-flex justify-content-between my-2"><span>Shipping Fee</span><strong><?php echo $currency . number_format($shipping_fee, 2); ?></strong></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><span class="fw-bold">Grand Total</span><strong class="price fs-4"><?php echo $currency . number_format($grand_total, 2); ?></strong></div>
                    <button class="btn btn-orange btn-lg w-100" type="submit" <?php if ($any_warehouse == false) { echo 'disabled'; } ?>>Place Order</button>
                    <a class="btn btn-link w-100 mt-1" href="cart.php">Back to cart</a>
                </div></div>
            </aside>
        </form>
        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

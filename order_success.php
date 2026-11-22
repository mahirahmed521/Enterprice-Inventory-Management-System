<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$so_id = 0;
if (isset($_GET['so_id'])) {
    $so_id = (int) $_GET['so_id'];
}
if (isset($_SESSION['user_id']) == false) {
    header('Location: login.php');
    exit;
}

// Only the customer who placed the order can see it
include __DIR__ . '/includes/current_customer.php';
if ($customer_id == 0) {
    header('Location: my_orders.php');
    exit;
}
$order_result = mysqli_query($conn, "SELECT o.*, w.warehouse_name, w.location_address FROM sales_orders o JOIN warehouses w ON w.warehouse_id=o.warehouse_id WHERE o.so_id=$so_id AND o.customer_id=$customer_id");
$order = mysqli_fetch_assoc($order_result);
if ($order == null) {
    header('Location: my_orders.php');
    exit;
}
$items = mysqli_query($conn, "SELECT i.quantity, i.unit_price, p.product_name, p.product_image, p.brand FROM sales_order_items i JOIN products p ON p.product_id=i.product_id WHERE i.so_id=$so_id");
$subtotal = 0;
$item_rows = array();
while ($item = mysqli_fetch_assoc($items)) {
    $item_rows[] = $item;
    $subtotal = $subtotal + ($item['quantity'] * (float) $item['unit_price']);
}
$item_total = count($item_rows);
$shipping_fee = (float) $order['shipping_fee'];
$tax_amount = (float) $order['total_amount'] - $subtotal - $shipping_fee;
if ($tax_amount < 0) {
    $tax_amount = 0;
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Order Confirmed | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <div class="alert alert-success d-flex align-items-center gap-3" role="status">
            <i class="bx bx-check-circle display-6"></i>
            <div>
                <strong class="fs-5">Thank you! Your order is placed.</strong><br>
                Order <strong><?php echo htmlspecialchars($order['so_number']); ?></strong> is pending. We have reserved your items and will confirm it shortly.
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card"><div class="card-body">
                    <h2 class="h5">Order Receipt</h2>
                    <?php $n = 0; while ($n < $item_total) { ?>
                        <?php
                        $item = $item_rows[$n];
                        $p_img = product_image_url($item['product_image']);
                        ?>
                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                            <img class="cart-thumb" src="<?php echo htmlspecialchars($p_img); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="">
                            <div class="flex-grow-1"><strong><?php echo htmlspecialchars($item['product_name']); ?></strong><div class="small text-muted"><?php echo htmlspecialchars((string) $item['brand']); ?> &middot; Quantity <?php echo $item['quantity']; ?></div></div>
                            <strong class="price"><?php echo $currency . number_format($item['quantity'] * (float) $item['unit_price'], 2); ?></strong>
                        </div>
                    <?php $n = $n + 1; } ?>
                    <div class="ms-auto mt-3" style="max-width:320px">
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span><?php echo $currency . number_format($subtotal, 2); ?></span></div>
                        <div class="d-flex justify-content-between"><span>Tax</span><span><?php echo $currency . number_format($tax_amount, 2); ?></span></div>
                        <div class="d-flex justify-content-between"><span>Shipping Fee</span><span><?php echo $currency . number_format($shipping_fee, 2); ?></span></div>
                        <hr>
                        <div class="d-flex justify-content-between"><strong>Total</strong><strong class="price fs-5"><?php echo $currency . number_format((float) $order['total_amount'], 2); ?></strong></div>
                    </div>
                </div></div>
            </div>
            <aside class="col-lg-4">
                <div class="card"><div class="card-body">
                    <h2 class="h5">Order Details</h2>
                    <p class="mb-2"><strong>Order number</strong><br><?php echo htmlspecialchars($order['so_number']); ?></p>
                    <p class="mb-2"><strong>Date</strong><br><?php echo htmlspecialchars($order['order_date']); ?></p>
                    <p class="mb-2"><strong>Shipping address</strong><br><?php echo nl2br(htmlspecialchars((string) $order['shipping_address'])); ?></p>
                    <p class="mb-2"><strong>Ships from</strong><br><?php echo htmlspecialchars($order['warehouse_name']); ?>, <?php echo htmlspecialchars((string) $order['location_address']); ?></p>
                    <p class="mb-3"><strong>Payment</strong><br><?php echo htmlspecialchars($order['payment_method']); ?> &middot; <?php echo htmlspecialchars($order['payment_status']); ?><?php if ($order['transaction_id'] != '') { echo '<br>Transaction ID: ' . htmlspecialchars($order['transaction_id']); } ?></p>
                    <div class="d-grid gap-2">
                        <a class="btn btn-orange" href="my_orders.php">Track My Order</a>
                        <a class="btn btn-outline-orange" href="order_invoice.php?so_id=<?php echo $so_id; ?>">Print Invoice</a>
                        <a class="btn btn-orange" href="home.php">Continue Shopping</a>
                    </div>
                </div></div>
            </aside>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

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

// Only the customer who placed the order can print it
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
$items = mysqli_query($conn, "SELECT i.quantity, i.unit_price, p.product_name, p.sku FROM sales_order_items i JOIN products p ON p.product_id=i.product_id WHERE i.so_id=$so_id");
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
    <title>Invoice <?php echo htmlspecialchars($order['so_number']); ?> | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<div class="container py-4" style="max-width:900px">
    <div class="d-flex justify-content-between mb-3 d-print-none">
        <a class="btn btn-outline-secondary" href="my_orders.php">Back to my orders</a>
        <button class="btn btn-orange" type="button" onclick="window.print()"><i class="bx bx-printer"></i> Print Invoice</button>
    </div>
    <div class="card invoice-sheet"><div class="card-body p-4">
        <div class="d-flex justify-content-between border-bottom border-2 pb-3 mb-3">
            <div><h1 class="h3 mb-0">Stockroom Electronics</h1><div class="text-muted">Customer Invoice</div></div>
            <div class="text-end"><div class="h5 mb-0"><?php echo htmlspecialchars($order['so_number']); ?></div><div class="text-muted">Date: <?php echo htmlspecialchars($order['order_date']); ?></div></div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-4"><strong>Billed to</strong><br><?php echo htmlspecialchars($customer['customer_name']); ?><br><?php echo htmlspecialchars((string) $customer['email']); ?><br><?php echo htmlspecialchars((string) $customer['phone']); ?></div>
            <div class="col-sm-4"><strong>Ship to</strong><br><?php echo nl2br(htmlspecialchars((string) $order['shipping_address'])); ?></div>
            <div class="col-sm-4"><strong>Shipped from</strong><br><?php echo htmlspecialchars($order['warehouse_name']); ?><br><?php echo htmlspecialchars((string) $order['location_address']); ?></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Item</th><th>SKU</th><th class="text-end">Qty</th><th class="text-end">Unit price</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    <?php $n = 0; while ($n < $item_total) { ?>
                        <?php $item = $item_rows[$n]; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['sku']); ?></td>
                            <td class="text-end"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo $currency . number_format((float) $item['unit_price'], 2); ?></td>
                            <td class="text-end"><?php echo $currency . number_format($item['quantity'] * (float) $item['unit_price'], 2); ?></td>
                        </tr>
                    <?php $n = $n + 1; } ?>
                </tbody>
            </table>
        </div>
        <div class="ms-auto" style="max-width:320px">
            <div class="d-flex justify-content-between"><span>Subtotal</span><span><?php echo $currency . number_format($subtotal, 2); ?></span></div>
            <div class="d-flex justify-content-between"><span>Tax</span><span><?php echo $currency . number_format($tax_amount, 2); ?></span></div>
            <div class="d-flex justify-content-between"><span>Shipping Fee</span><span><?php echo $currency . number_format($shipping_fee, 2); ?></span></div>
            <hr>
            <div class="d-flex justify-content-between"><strong>Grand Total</strong><strong class="fs-5"><?php echo $currency . number_format((float) $order['total_amount'], 2); ?></strong></div>
        </div>
        <hr>
        <p class="mb-1"><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?> &middot; <?php echo htmlspecialchars($order['payment_status']); ?><?php if ($order['transaction_id'] != '') { echo ' &middot; Transaction ID ' . htmlspecialchars($order['transaction_id']); } ?></p>
        <p class="mb-0"><strong>Order status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
    </div></div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$currency = '৳';
$filter = 'ALL';
$action = '';
$order_id = 0;
$cancel_error = '';
if (isset($_GET['status'])) {
    $filter = $_GET['status'];
}
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if (isset($_POST['order_id'])) {
    $order_id = (int) $_POST['order_id'];
}
if (isset($_SESSION['user_id']) == false) {
    $_SESSION['redirect_after_login'] = 'my_orders.php';
    header('Location: login.php');
    exit;
}
$tabs = array('ALL', 'PENDING', 'CONFIRMED', 'SHIPPED', 'CANCELLED');
if (in_array($filter, $tabs) == false) {
    $filter = 'ALL';
}

// Only customer accounts have orders
include __DIR__ . '/includes/current_customer.php';

// Cancel a pending order (trg_release_stock_on_cancel gives the stock back)
if ($customer_id > 0 && $action == 'cancel') {
    mysqli_query($conn, 'START TRANSACTION');
    $check = mysqli_query($conn, "SELECT status FROM sales_orders WHERE so_id=$order_id AND customer_id=$customer_id FOR UPDATE");
    $check_row = mysqli_fetch_assoc($check);
    if ($check_row && $check_row['status'] == 'PENDING') {
        if (mysqli_query($conn, "UPDATE sales_orders SET status='CANCELLED' WHERE so_id=$order_id AND customer_id=$customer_id")) {
            mysqli_query($conn, 'COMMIT');
            $_SESSION['store_notice'] = 'Your order was cancelled and the items went back on the shelf.';
            header('Location: my_orders.php?status=' . $filter);
            exit;
        }
        mysqli_query($conn, 'ROLLBACK');
        $cancel_error = 'We could not cancel that order. Please try again.';
    } else {
        mysqli_query($conn, 'ROLLBACK');
        $cancel_error = 'Only pending orders can be cancelled. Contact us if you need help with this one.';
    }
}

$orders = null;
$order_total = 0;
if ($customer_id > 0) {
    $sql = "SELECT * FROM sales_orders WHERE customer_id=$customer_id";
    if ($filter != 'ALL') {
        $sql = $sql . " AND status='$filter'";
    }
    $orders = mysqli_query($conn, $sql . ' ORDER BY so_id DESC');
    $order_total = mysqli_num_rows($orders);
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>My Orders | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <h1 class="h3 mb-3">My Orders</h1>

        <?php if ($customer_id == 0) { ?>
            <div class="alert alert-warning">Order tracking is for customer accounts. Team members can manage online orders from the <a href="admin/sales_orders.php">admin sales orders</a> page.</div>
        <?php } else { ?>

        <?php if ($cancel_error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($cancel_error); ?></div><?php } ?>

        <ul class="nav nav-tabs mb-3">
            <?php $t = 0; while ($t < 5) { ?>
                <li class="nav-item"><a class="nav-link <?php if ($filter == $tabs[$t]) { echo 'active'; } ?>" href="my_orders.php?status=<?php echo $tabs[$t]; ?>"><?php echo ucfirst(strtolower($tabs[$t])); ?></a></li>
            <?php $t = $t + 1; } ?>
        </ul>

        <?php if ($order_total == 0) { ?>
            <div class="card"><div class="card-body text-center py-5">
                <h2 class="h5">No orders here yet</h2>
                <p class="text-muted">When you place an order it will show up with live status tracking.</p>
                <a class="btn btn-orange" href="shop.php">Start Shopping</a>
            </div></div>
        <?php } ?>

        <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
            <?php
            $status = $order['status'];
            $status_class = 'text-bg-secondary';
            if ($status == 'PENDING') {
                $status_class = 'text-bg-warning';
            } elseif ($status == 'CONFIRMED') {
                $status_class = 'text-bg-info';
            } elseif ($status == 'SHIPPED') {
                $status_class = 'text-bg-success';
            } elseif ($status == 'CANCELLED') {
                $status_class = 'text-bg-danger';
            }
            $step_two = '';
            $step_three = '';
            if ($status == 'CONFIRMED' || $status == 'SHIPPED') {
                $step_two = 'done';
            }
            if ($status == 'SHIPPED') {
                $step_three = 'done';
            }
            $this_order = (int) $order['so_id'];
            $items = mysqli_query($conn, "SELECT i.quantity, i.unit_price, p.product_id, p.product_name, p.product_image FROM sales_order_items i JOIN products p ON p.product_id=i.product_id WHERE i.so_id=$this_order");
            ?>
            <div class="card mb-3"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><strong><?php echo htmlspecialchars($order['so_number']); ?></strong> <span class="text-muted small">placed <?php echo htmlspecialchars($order['order_date']); ?></span></div>
                    <div><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span> <span class="badge text-bg-light border"><?php echo htmlspecialchars($order['payment_method']); ?> &middot; <?php echo htmlspecialchars($order['payment_status']); ?></span></div>
                </div>

                <?php if ($status == 'CANCELLED') { ?>
                    <div class="alert alert-danger my-3 py-2">This order was cancelled. Its reserved items have been released.</div>
                <?php } else { ?>
                    <div class="order-steps">
                        <div class="order-step done"><b>1</b><br>Order Placed</div>
                        <div class="order-step <?php echo $step_two; ?>"><b>2</b><br>Confirmed &amp; Packed</div>
                        <div class="order-step <?php echo $step_three; ?>"><b>3</b><br>Shipped / Delivered</div>
                    </div>
                <?php } ?>

                <?php while ($item = mysqli_fetch_assoc($items)) { ?>
                    <?php
                    $p_img = $item['product_image'];
                    if ($p_img == '') {
                        $p_img = 'uploads/products/default.png';
                    }
                    ?>
                    <div class="d-flex align-items-center gap-3 py-2 border-top">
                        <img class="cart-thumb" src="<?php echo htmlspecialchars($p_img); ?>" onerror="this.src='uploads/products/default.png'" alt="">
                        <div class="flex-grow-1">
                            <a class="fw-semibold text-decoration-none" href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                            <div class="small text-muted">Quantity <?php echo $item['quantity']; ?> &middot; <?php echo $currency . number_format((float) $item['unit_price'], 2); ?> each</div>
                        </div>
                        <?php if ($status == 'SHIPPED') { ?>
                            <a class="btn btn-sm btn-outline-orange" href="product.php?id=<?php echo $item['product_id']; ?>#reviews">Write a Review</a>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-3">
                    <div>Total <strong class="price fs-5"><?php echo $currency . number_format((float) $order['total_amount'], 2); ?></strong></div>
                    <div class="d-flex gap-2">
                        <?php if ($status == 'PENDING') { ?>
                            <form method="post" onsubmit="return confirm('Cancel this order?');">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="order_id" value="<?php echo $this_order; ?>">
                                <button class="btn btn-outline-danger" type="submit">Cancel Order</button>
                            </form>
                        <?php } ?>
                        <a class="btn btn-orange" href="order_invoice.php?so_id=<?php echo $this_order; ?>">Print Invoice</a>
                    </div>
                </div>
            </div></div>
        <?php } ?>

        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

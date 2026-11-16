<?php
if ($action == 'confirm') {
    if ($can_approve == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $items = mysqli_query($conn, "SELECT so_item_id FROM sales_order_items WHERE so_id=$id LIMIT 1");
    $item = null;
    if ($items) {
        $item = mysqli_fetch_assoc($items);
    }
    if ($item == null) {
        $error = 'Add a product before confirming.';
        return;
    }
    $next_status = 'CONFIRMED';
} elseif ($action == 'ship') {
    if ($can_ship == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $next_status = 'SHIPPED';
} elseif ($action == 'cancel') {
    if ($can_cancel == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $next_status = 'CANCELLED';
} else {
    $error = 'This action is unavailable.';
    return;
}
if ($action == 'ship') {
    include __DIR__ . '/release_sales_stock.php';
} elseif ($action == 'cancel') {
    include __DIR__ . '/release_sales_stock.php';
}
if ($error == '') {
    if (mysqli_query($conn, "UPDATE sales_orders SET status='$next_status' WHERE so_id=$id") == false) {
        $error = 'Could not update the order status.';
    }
}

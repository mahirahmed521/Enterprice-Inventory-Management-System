<?php
include __DIR__ . '/so_items.php';
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/release_sales_stock.php';
$places = mysqli_query($conn, "SELECT warehouse_id FROM warehouses WHERE warehouse_id=$warehouse_id AND is_active=1");
$partners = mysqli_query($conn, "SELECT customer_id FROM customers WHERE customer_id=$party_id");
if ($places == false) {
    $error = 'Could not check the warehouse.';
    return;
} elseif ($partners == false) {
    $error = 'Could not check the customer.';
    return;
} elseif ($places->num_rows == 0) {
    $error = 'Choose an active warehouse.';
    return;
} elseif ($partners->num_rows == 0) {
    $error = 'Choose a valid customer.';
    return;
}

if ($action == 'create') {
    $number = uniqid('SO-');
    $sql = "INSERT INTO sales_orders (customer_id, warehouse_id, so_number, order_date, created_by)
                VALUES ($party_id, $warehouse_id, '$number', CURRENT_DATE, $user_id)";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not create the order.';
        return;
    }
    $id = mysqli_insert_id($conn);
    if (isset($product_id)) {
        if ($product_id > 0) {

        }
    }
} else {
    $current_order = mysqli_query($conn, "SELECT warehouse_id FROM sales_orders WHERE so_id=$id");
    if ($current_order == false) {
        $error = 'Could not load the order details.';
        return;
    }
    if ($current_order->num_rows == 0) {
        $error = 'Could not load the order details.';
        return;
    }
    $current_order_row = mysqli_fetch_assoc($current_order);
    $old_warehouse = $current_order_row['warehouse_id'];
    $same_warehouse = $old_warehouse == $warehouse_id;
    if ($same_warehouse == false) {

        if ($error) {
            return;
        }
    }
    if (mysqli_query($conn, "UPDATE sales_orders SET customer_id=$party_id, warehouse_id=$warehouse_id
                    WHERE so_id=$id") == false) {
        $error = 'Could not update order details.';
    }
}

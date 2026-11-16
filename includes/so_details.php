<?php
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
    if ($product_id > 0) {
        include __DIR__ . '/so_items.php';
    }
} else {
    $same_warehouse = $old_warehouse == $warehouse_id;
    if ($same_warehouse == false) {
        include __DIR__ . '/release_sales_stock.php';
        if ($error) {
            return;
        }
    }
    if (mysqli_query($conn, "UPDATE sales_orders SET customer_id=$party_id, warehouse_id=$warehouse_id
                    WHERE so_id=$id") == false) {
        $error = 'Could not update order details.';
    }
}

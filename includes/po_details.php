<?php
$places = mysqli_query($conn, "SELECT warehouse_id FROM warehouses WHERE warehouse_id=$warehouse_id AND is_active=1");
$partners = mysqli_query($conn, "SELECT supplier_id FROM suppliers WHERE supplier_id=$party_id AND is_active=1");
if ($places == false) {
    $error = 'Could not check the warehouse.';
    return;
} elseif ($partners == false) {
    $error = 'Could not check the supplier.';
    return;
} elseif ($places->num_rows == 0) {
    $error = 'Choose an active warehouse.';
    return;
} elseif ($partners->num_rows == 0) {
    $error = 'Choose a valid supplier.';
    return;
}
$sql_text = [
    'expected_date' => $expected_date
];
include __DIR__ . '/quote_fields.php';
$date = $sql_text['expected_date'];
$delivery_date = 'NULL';
if (isset($date[0])) {
    $delivery_date = "'$date'";
}
if ($action == 'create') {
    $number = uniqid('PO-');
    $sql = "INSERT INTO purchase_orders (supplier_id, warehouse_id, expected_date, po_number, order_date, created_by)
                VALUES ($party_id, $warehouse_id, $delivery_date, '$number', CURRENT_DATE, $user_id)";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not create the order.';
        return;
    }
    $id = mysqli_insert_id($conn);

} else {

    if (mysqli_query($conn, "UPDATE purchase_orders SET supplier_id=$party_id, warehouse_id=$warehouse_id, expected_date=$delivery_date
                    WHERE po_id=$id") == false) {
        $error = 'Could not update order details.';
    }
}

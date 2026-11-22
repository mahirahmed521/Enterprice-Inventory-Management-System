<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($user_id)) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        $user_id = 0;
    }
}
$user_id = (int) $user_id;
if ($user_id <= 0) {
    $error = 'Could not identify the user performing this movement.';
    return;
}
$sql = "SELECT product_id FROM products WHERE product_id=$product_id AND is_active=1";
$products = mysqli_query($conn, $sql);
$warehouses = mysqli_query($conn, "SELECT warehouse_id FROM warehouses WHERE warehouse_id=$warehouse_id AND is_active=1");
if ($products == false) {
    $error = 'Could not check the product.';
    return;
} elseif ($warehouses == false) {
    $error = 'Could not check the warehouse.';
    return;
} elseif ($products->num_rows == 0) {
    $error = 'Choose an active product.';
    return;
} elseif ($warehouses->num_rows == 0) {
    $error = 'Choose an active warehouse.';
    return;
}
$sql_text = [
    'notes' => $notes
];
include __DIR__ . '/quote_fields.php';
$reason = $sql_text['notes'];
$units = $quantity;
$change = $units;
$type = 'ADJUSTMENT';
$source = 'MANUAL';
if ($action == 'transfer') {
    $target = mysqli_query($conn, "SELECT warehouse_id FROM warehouses WHERE warehouse_id=$destination AND is_active=1");
    $destination_row = null;
    if ($target) {
        $destination_row = mysqli_fetch_assoc($target);
    }
    if ($destination_row == null) {
        $error = 'Choose an active destination.';
        return;
    }
    $type = 'TRANSFER_OUT';
    $source = 'TRANSFER';
}
$take_from_stock = false;
if ($action == 'transfer') {
    $take_from_stock = true;
} elseif ($action == 'decrease') {
    $take_from_stock = true;
}
if ($take_from_stock) {
    $sql = "SELECT quantity_on_hand-quantity_reserved AS available FROM warehouse_stock
            WHERE product_id=$product_id AND warehouse_id=$warehouse_id FOR UPDATE";
    $bins = mysqli_query($conn, $sql);
    $bin = null;
    if ($bins) {
        $bin = mysqli_fetch_assoc($bins);
    }
    if ($bin == null) {
        $error = 'No stock is available in that warehouse.';
        return;
    }
    if ($bin['available'] < $units) {
        $error = 'Insufficient available stock.';
        return;
    }
    $change = 0 - $units;
}
$sql = "INSERT INTO stock_movements
    (product_id, notes, performed_by, reference_type, warehouse_id, movement_type, quantity_change)
    VALUES
    ($product_id, '$reason', $user_id, '$source', $warehouse_id, '$type', $change)";
if (mysqli_query($conn, $sql) == false) {
    $error = 'Could not post the movement.';
    return;
}
if ($action == 'transfer') {
    $outgoing_id = mysqli_insert_id($conn);
    $sql = "INSERT INTO stock_movements (product_id, notes, performed_by, reference_type, warehouse_id, movement_type, quantity_change, reference_id)
                VALUES ($product_id, '$reason', $user_id, '$source', $destination, 'TRANSFER_IN', $units, $outgoing_id)";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not complete the transfer.';
    }
}

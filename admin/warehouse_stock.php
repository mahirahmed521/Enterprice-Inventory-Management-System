<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Warehouse stock';
$section = 'Warehouse operations';
$error = '';
$action = '';
$warehouse_id = '';
$product_id = '';
$bin = '';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['warehouse_id'])) {
    $warehouse_id = trim($_POST['warehouse_id']);
}
if (isset($_POST['product_id'])) {
    $product_id = trim($_POST['product_id']);
}
if (isset($_POST['bin'])) {
    $bin = trim($_POST['bin']);
}
$valid_digits = isset($warehouse_id[0]);
$digit_position = 0;
while (isset($warehouse_id[$digit_position])) {
    if ($warehouse_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($warehouse_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $warehouse_id = 0;
}
$valid_digits = isset($product_id[0]);
$digit_position = 0;
while (isset($product_id[$digit_position])) {
    if ($product_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($product_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $product_id = 0;
}
require '../includes/auth.php';
if ($warehouse_access == false) {
    exit('Access denied.');
}

$manager = true;
if ($account['role'] == 'STAFF') {
    $manager = false;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql_text = [
        'bin' => $bin
    ];
    include __DIR__ . '/../includes/quote_fields.php';
    $location = $sql_text['bin'];
    $stock_key = "warehouse_id=$warehouse_id AND product_id=$product_id";
    if ($action == 'update') {
        $sql = "UPDATE warehouse_stock SET aisle_bin_location='$location' WHERE $stock_key";
    } elseif ($action == 'create') {
        if ($supervisor == false) {
            exit('This action is unavailable.');
        }
        $sql = "INSERT INTO warehouse_stock (warehouse_id, product_id, aisle_bin_location)
                VALUES ($warehouse_id, $product_id, '$location')";
    } elseif ($action == 'delete') {
        if ($supervisor == false) {
            exit('This action is unavailable.');
        }
        $sql = "DELETE FROM warehouse_stock WHERE $stock_key AND quantity_on_hand=0 AND quantity_reserved=0";
    } else {
        exit('This action is unavailable.');
    }
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Check the warehouse, product and bin.';
    } elseif ($action == 'delete') {
        if (mysqli_affected_rows($conn) == 0) {
            $error = 'Only an empty bin can be removed.';
        }
    }
    if ($error == '') {
        $_SESSION['notice'] = 'Bin saved.';
        header('Location: warehouse_stock.php');
        exit;
    }
}
$sql = "SELECT warehouse_stock.*, warehouses.warehouse_name, products.product_name,
               warehouse_stock.quantity_on_hand - warehouse_stock.quantity_reserved AS available
        FROM warehouse_stock
        JOIN warehouses USING(warehouse_id)
        JOIN products USING(product_id)
        ORDER BY warehouses.warehouse_name, products.product_name";
$rows = mysqli_query($conn, $sql);
include '../includes/workspace.php';
include '../templates/warehouse_stock.php';
include '../includes/end.php';

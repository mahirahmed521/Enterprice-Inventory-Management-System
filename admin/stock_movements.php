<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Stock movements';
$section = 'Warehouse operations';
$error = '';
$action = '';
$warehouse_id = '';
$product_id = '';
$destination = '';
$quantity = '';
$notes = '';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['warehouse_id'])) {
    $warehouse_id = trim($_POST['warehouse_id']);
}
if (isset($_POST['product_id'])) {
    $product_id = trim($_POST['product_id']);
}
if (isset($_POST['destination'])) {
    $destination = trim($_POST['destination']);
}
if (isset($_POST['quantity'])) {
    $quantity = trim($_POST['quantity']);
}
if (isset($_POST['notes'])) {
    $notes = trim($_POST['notes']);
}
$valid_digits = isset($warehouse_id[0]);
$digit_position = 0;
while (isset($warehouse_id[$digit_position])) {
    if ($warehouse_id[$digit_position] < '0' || $warehouse_id[$digit_position] > '9') {
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
    if ($product_id[$digit_position] < '0' || $product_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $product_id = 0;
}
$valid_digits = isset($destination[0]);
$digit_position = 0;
while (isset($destination[$digit_position])) {
    if ($destination[$digit_position] < '0' || $destination[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $destination = 0;
}
require '../includes/auth.php';
if ($warehouse_access == false) {
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valid_movement = false;
    $same_warehouse = false;
    if ($action == 'transfer') {
        $valid_movement = true;
        if ($warehouse_id == $destination) {
            $same_warehouse = true;
        }
    } elseif ($action == 'increase') {
        $valid_movement = true;
    } elseif ($action == 'decrease') {
        $valid_movement = true;
    }
    $valid_digits = isset($quantity[0]);
    $digit_position = 0;
    while (isset($quantity[$digit_position])) {
        if ($quantity[$digit_position] < '0' || $quantity[$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        $error = 'Enter a positive whole quantity.';
    } elseif ($quantity < 1) {
        $error = 'Enter a positive whole quantity.';
    } elseif ($notes == '') {
        $error = 'Enter the reason for this movement.';
    } elseif ($valid_movement == false) {
        $error = 'Choose a movement type.';
    } elseif ($same_warehouse) {
        $error = 'Choose a different destination warehouse.';
    } else {
        mysqli_query($conn, 'START TRANSACTION');
        include '../includes/post_movement.php';
        if ($error == '') {
            if (mysqli_query($conn, 'COMMIT')) {
                $_SESSION['notice'] = 'Movement posted.';
                header('Location: stock_movements.php');
                exit;
            }
        }
        mysqli_query($conn, 'ROLLBACK');
        if ($error == '') {
            $error = 'Could not save the movement.';
        }
    }
}
$sql = "SELECT stock_movements.*, warehouses.warehouse_name, products.sku, users.username
        FROM stock_movements
        JOIN warehouses USING(warehouse_id)
        JOIN products USING(product_id)
        LEFT JOIN users ON users.user_id=stock_movements.performed_by
        ORDER BY stock_movements.movement_id DESC";
$rows = mysqli_query($conn, $sql);
include '../includes/workspace.php';
include '../templates/stock_movements.php';
include '../includes/end.php';

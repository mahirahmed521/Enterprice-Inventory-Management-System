<?php
require_once __DIR__ . '/../includes/check_fields.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
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
    $action = $_POST['action'];
}
if (isset($_POST['warehouse_id'])) {
    $warehouse_id = $_POST['warehouse_id'];
}
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
}
if (isset($_POST['destination'])) {
    $destination = $_POST['destination'];
}
if (isset($_POST['quantity'])) {
    $quantity = $_POST['quantity'];
}
if (isset($_POST['notes'])) {
    $notes = $_POST['notes'];
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
$valid_digits = isset($destination[0]);
$digit_position = 0;
while (isset($destination[$digit_position])) {
    if ($destination[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($destination[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $destination = 0;
}

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
        if ($quantity[$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($quantity[$digit_position] > '9') {
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
$sql = "SELECT m.*,w.warehouse_name,p.sku,u.username FROM stock_movements m
        JOIN warehouses w USING(warehouse_id) JOIN products p USING(product_id)
        LEFT JOIN users u ON u.user_id=m.performed_by ORDER BY m.movement_id DESC";
$rows = mysqli_query($conn, $sql);
include '../includes/workspace.php';
include '../templates/stock_movements.php';
include '../includes/end.php';

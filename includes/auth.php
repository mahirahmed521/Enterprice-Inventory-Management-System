<?php
require_once __DIR__ . '/check_fields.php';
require_once __DIR__ . '/../config/db.php';
session_start();
if (isset($_SESSION['user_id']) == false) {
    if (basename($_SERVER['SCRIPT_NAME']) == 'sales_orders.php') {
        $_SESSION['open_sales'] = 1;
        if (isset($_GET['product'])) {
            $valid_digits = isset($_GET['product'][0]);
            $digit_position = 0;
            while (isset($_GET['product'][$digit_position])) {
                if ($_GET['product'][$digit_position] < '0') {
                    $valid_digits = false;
                }
                if ($_GET['product'][$digit_position] > '9') {
                    $valid_digits = false;
                }
                $digit_position = $digit_position + 1;
            }
            if ($valid_digits) {
                $_SESSION['product'] = $_GET['product'];
            }
        }
    }
    header('Location: index.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT user_id, username, role, customer_id FROM users WHERE user_id=$user_id AND is_active=1";
$users = mysqli_query($conn, $sql);
if ($users == false) {
    exit('Could not load the signed-in account.');
}
if (mysqli_num_rows($users) == 0) {
    session_destroy();
    header('Location: index.php');
    exit;
}
$account = mysqli_fetch_assoc($users);
$supervisor = false;
$warehouse_access = false;
if ($account['role'] == 'ADMIN') {
    $supervisor = true;
    $warehouse_access = true;
} elseif ($account['role'] == 'MANAGER') {
    $supervisor = true;
    $warehouse_access = true;
} elseif ($account['role'] == 'STAFF') {
    $warehouse_access = true;
}
if (isset($required_role)) {
    $role_matches = $account['role'] == $required_role;
    if ($role_matches == false) {
        exit('Access denied.');
    }
}
$_SESSION['role'] = $account['role'];

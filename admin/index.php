<?php
require_once __DIR__ . '/../includes/check_fields.php';
session_start();
$pageTitle = 'Sign in';
$error = '';
$email = '';
$password = '';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
}
if (isset($_POST['password'])) {
    $password = trim($_POST['password']);
}
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../config/db.php';
    $sql_text = [
        'email' => $email
    ];
    include __DIR__ . '/../includes/quote_fields.php';
    $address = $sql_text['email'];
    $users = mysqli_query($conn, "SELECT user_id,role,is_active,password_hash FROM users WHERE email='$address'");
    if ($users == false) {
        $error = 'Could not read the account.';
    } elseif ($users->num_rows == 0) {
        $error = 'Email or password is incorrect.';
    } else {
        $user = mysqli_fetch_assoc($users);
        if (password_verify($password, $user['password_hash']) == false) {
            $error = 'Email or password is incorrect.';
        } elseif ($user['is_active'] == 0) {
            $error = 'Your account is inactive.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $home = 'dashboard.php';
            if (isset($_SESSION['open_sales'])) {
                $home = 'sales_orders.php';
                if (isset($_SESSION['product'])) {
                    $home = $home . '?product=' . $_SESSION['product'];
                }
                unset($_SESSION['product'], $_SESSION['open_sales']);
            }
            header('Location: ' . $home);
            exit;
        }
    }
}
include '../templates/account.php';

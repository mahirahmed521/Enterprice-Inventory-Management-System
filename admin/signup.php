<?php
require_once __DIR__ . '/../includes/check_fields.php';
require_once __DIR__ . '/../config/db.php';
session_start();
$pageTitle = 'Create an account';
$error = '';
$username = '';
$email = '';
$password = '';
$role = '';
$address = '';

if (isset($_POST['username'])) {
    $username = $_POST['username'];
}
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
}
if (isset($_POST['role'])) {
    $role = $_POST['role'];
}
if (isset($_POST['address'])) {
    $address = $_POST['address'];
}
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valid_role = false;
    if ($role == 'ADMIN') {
        $valid_role = true;
    } elseif ($role == 'MANAGER') {
        $valid_role = true;
    } elseif ($role == 'STAFF') {
        $valid_role = true;
    } elseif ($role == 'CUSTOMER') {
        $valid_role = true;
    }
    if ($username == '') {
        $error = 'Enter a name, email and password.';
    } elseif ($email == '') {
        $error = 'Enter a name, email and password.';
    } elseif ($password == '') {
        $error = 'Enter a name, email and password.';
    } elseif (strpos($email, '@') == false) {
        $error = 'Enter a name, email and password.';
    } elseif ($valid_role == false) {
        $error = 'Choose a listed role.';
    } else {
        
        $sql_text = [
            'username' => $username,
            'email' => $email,
            'address' => $address
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $customer = 'NULL';
        mysqli_query($conn, 'START TRANSACTION');
        if ($role == 'CUSTOMER') {
            $sql = "INSERT INTO customers (customer_name, email, shipping_address)
                VALUES ('$sql_text[username]', '$sql_text[email]', '$sql_text[address]')";
            if (mysqli_query($conn, $sql)) {
                $customer = mysqli_insert_id($conn);
            } else {
                $error = 'Could not create the delivery record.';
            }
        }
        if ($error == '') {
            $sql = "INSERT INTO users (username, email, password_hash, role, is_active, customer_id)
                VALUES ('$sql_text[username]', '$sql_text[email]', '$hash', '$role', 1, $customer)";
            if (mysqli_query($conn, $sql) == false) {
                $error = 'Could not register. Check the email and username.';
            }
        }
        if ($error == '') {
            mysqli_query($conn, 'COMMIT');
            $_SESSION['notice'] = 'Account created. Sign in to continue.';
            header('Location: index.php');
            exit;
        }
        mysqli_query($conn, 'ROLLBACK');
    }
}
include '../templates/account.php';

<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Customers';
$section = 'Directory';
$error = '';
include '../includes/edit_request.php';
$action = '';
$customer_name = '';
$email = '';
$phone = '';
$shipping_address = '';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['customer_name'])) {
    $customer_name = trim($_POST['customer_name']);
}
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
}
if (isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);
}
if (isset($_POST['shipping_address'])) {
    $shipping_address = trim($_POST['shipping_address']);
}

require '../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

if ($id > 0) {
    $records = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id=$id");
    $record = null;
    if ($records) {
        $record = mysqli_fetch_assoc($records);
    }
    if ($record == null) {
        exit('Record not found.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $customer_name = $record['customer_name'];
        $email = $record['email'];
        $phone = $record['phone'];
        $shipping_address = $record['shipping_address'];
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    $deleted = mysqli_query($conn, "DELETE FROM customers WHERE customer_id=$id");
    if ($deleted == false) {
        $error = 'This record is still in use.';
    } elseif (mysqli_affected_rows($conn) == 0) {
        $error = 'This record is still in use.';
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    if ($customer_name === '') {
        $error = 'Complete the required fields.';
    }

    if ($error == '') {
        $sql_text = [
            'customer_name' => $customer_name,
            'email' => $email,
            'phone' => $phone,
            'shipping_address' => $shipping_address
        ];
        include __DIR__ . '/../includes/quote_fields.php';

        if ($id == 0) {
            $sql = "INSERT INTO customers (customer_name, email, phone, shipping_address)
                VALUES ('$sql_text[customer_name]', '$sql_text[email]', '$sql_text[phone]', '$sql_text[shipping_address]')";
        } else {
            $sql = "UPDATE customers SET customer_name='$sql_text[customer_name]',
                    email='$sql_text[email]',
                    phone='$sql_text[phone]',
                    shipping_address='$sql_text[shipping_address]'
                    WHERE customer_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the customer name and contact details.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: customers.php');
        exit;
    }
}

$rows = mysqli_query($conn, 'SELECT customer_id,customer_name,email,phone,shipping_address FROM customers ORDER BY customer_id DESC');
include '../includes/workspace.php';
include '../templates/customers.php';
include '../includes/end.php';

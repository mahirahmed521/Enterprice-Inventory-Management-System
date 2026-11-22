<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Delivery details';
$section = 'Customer account';
$error = '';
$customer_name = '';
$phone = '';
$shipping_address = '';

if (isset($_POST['customer_name'])) {
    $customer_name = $_POST['customer_name'];
}
if (isset($_POST['phone'])) {
    $phone = $_POST['phone'];
}
if (isset($_POST['shipping_address'])) {
    $shipping_address = $_POST['shipping_address'];
}
$required_role = 'CUSTOMER';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$customer = 0;
if (isset($account['customer_id'])) {
    $customer = $account['customer_id'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($customer_name === '') {
        $error = 'Enter your delivery name.';
    } else {
        $sql_text = [
            'customer_name' => $customer_name,
            'phone' => $phone,
            'shipping_address' => $shipping_address
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $name = $sql_text['customer_name'];
        $number = $sql_text['phone'];
        $address = $sql_text['shipping_address'];
        $sql = "UPDATE customers SET customer_name='$name',phone='$number',shipping_address='$address'
                WHERE customer_id=$customer";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['notice'] = 'Delivery details saved.';
            header('Location: customer_profile.php');
            exit;
        }
        $error = 'Could not save your delivery details.';
    }
} else {
    $contacts = mysqli_query($conn, "SELECT customer_name,phone,shipping_address FROM customers WHERE customer_id=$customer");
    $contact = null;
    if ($contacts) {
        $contact = mysqli_fetch_assoc($contacts);
    }
    if ($contact == null) {
        exit('Delivery record not found.');
    }
    $customer_name = $contact['customer_name'];
    $phone = $contact['phone'];
    $shipping_address = $contact['shipping_address'];
}
include '../includes/workspace.php';
include '../templates/customer_profile.php';
include '../includes/end.php';

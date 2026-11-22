<?php
$pageTitle = 'Customer dashboard';
$section = 'Overview';
$required_role = 'CUSTOMER';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$customer = 0;
if (isset($account['customer_id'])) {
    $customer = $account['customer_id'];
}
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_orders WHERE customer_id=$customer AND status IN ('PENDING','CONFIRMED')");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['open_orders'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_orders WHERE customer_id=$customer AND status='SHIPPED'");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['shipments'] = $row['total'];
$rows = mysqli_query($conn, "SELECT so_number,order_date,status,total_amount FROM sales_orders WHERE customer_id=$customer ORDER BY so_id DESC LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_customer.php';
include '../includes/end.php';

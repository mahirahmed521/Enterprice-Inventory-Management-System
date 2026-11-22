<?php
$pageTitle = 'Staff dashboard';
$section = 'Overview';
$required_role = 'STAFF';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_orders WHERE status='CONFIRMED'");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['orders_to_ship'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM purchase_orders WHERE status IN ('ORDERED','PARTIAL')");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['deliveries_due'] = $row['total'];
$rows = mysqli_query($conn, "SELECT o.so_number,c.customer_name,w.warehouse_name,o.order_date FROM sales_orders o JOIN customers c USING(customer_id) JOIN warehouses w USING(warehouse_id) WHERE o.status='CONFIRMED' ORDER BY o.order_date,o.so_id LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_staff.php';
include '../includes/end.php';

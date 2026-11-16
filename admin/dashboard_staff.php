<?php
$pageTitle = 'Staff dashboard';
$section = 'Overview';
$required_role = 'STAFF';
require '../includes/auth.php';

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
$rows = mysqli_query($conn, "SELECT sales_orders.so_number, customers.customer_name,
                                    warehouses.warehouse_name, sales_orders.order_date
                             FROM sales_orders
                             JOIN customers USING(customer_id)
                             JOIN warehouses USING(warehouse_id)
                             WHERE sales_orders.status='CONFIRMED'
                             ORDER BY sales_orders.order_date, sales_orders.so_id LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_staff.php';
include '../includes/end.php';

<?php
$pageTitle = 'Manager dashboard';
$section = 'Overview';
$required_role = 'MANAGER';
require '../includes/auth.php';

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM purchase_orders WHERE status IN ('DRAFT','ORDERED','PARTIAL')");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['open_purchases'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_orders WHERE status='PENDING'");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['pending_sales'] = $row['total'];
$rows = mysqli_query($conn, "SELECT v.sku,v.product_name,v.available_to_sell,v.reorder_point FROM vw_reorder_alerts v JOIN products p USING(product_id) WHERE p.is_active=1 ORDER BY v.available_to_sell LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_manager.php';
include '../includes/end.php';

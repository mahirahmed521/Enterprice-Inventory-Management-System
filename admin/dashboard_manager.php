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
$rows = mysqli_query($conn, "SELECT vw_reorder_alerts.sku, vw_reorder_alerts.product_name,
                                    vw_reorder_alerts.available_to_sell, vw_reorder_alerts.reorder_point
                             FROM vw_reorder_alerts
                             JOIN products USING(product_id)
                             WHERE products.is_active=1
                             ORDER BY vw_reorder_alerts.available_to_sell LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_manager.php';
include '../includes/end.php';

<?php
$pageTitle = 'Reorder alerts';
$section = 'Reports';
require '../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

$sql = "SELECT vw_reorder_alerts.sku, vw_reorder_alerts.product_name, vw_reorder_alerts.available_to_sell,
               vw_reorder_alerts.reorder_point, products.reorder_quantity, suppliers.company_name
        FROM vw_reorder_alerts
        JOIN products USING(product_id)
        LEFT JOIN suppliers ON suppliers.supplier_id=products.preferred_supplier_id
        WHERE products.is_active=1
        ORDER BY vw_reorder_alerts.available_to_sell, vw_reorder_alerts.sku";
$rows = mysqli_query($conn, $sql);
if ($rows == false) {
    exit('Could not load this report.');
}
include '../includes/workspace.php';
include '../templates/reorder_alerts.php';
include '../includes/end.php';

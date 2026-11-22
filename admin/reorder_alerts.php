<?php
$pageTitle = 'Reorder alerts';
$section = 'Reports';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if ($supervisor == false) {
    exit('Access denied.');
}

$sql = "SELECT a.sku,a.product_name,a.available_to_sell,a.reorder_point,p.reorder_quantity,s.company_name FROM vw_reorder_alerts a JOIN products p USING(product_id) LEFT JOIN suppliers s ON s.supplier_id=p.preferred_supplier_id WHERE p.is_active=1 ORDER BY a.available_to_sell,a.sku";
$rows = mysqli_query($conn, $sql);
if ($rows == false) {
    exit('Could not load this report.');
}
include '../includes/workspace.php';
include '../templates/reorder_alerts.php';
include '../includes/end.php';

<?php
$pageTitle = 'Inventory valuation';
$section = 'Reports';
$required_role = 'ADMIN';
require '../includes/auth.php';

$sql = "SELECT * FROM vw_inventory_valuation ORDER BY product_name,sku";
$rows = mysqli_query($conn, $sql);
if ($rows == false) {
    exit('Could not load this report.');
}
$total = mysqli_query($conn, 'SELECT SUM(total_inventory_cost) AS total FROM vw_inventory_valuation');
if ($total == false) {
    exit('Could not calculate inventory value.');
}
$value = mysqli_fetch_assoc($total);
if ($value['total'] === null) {
    $value['total'] = 0;
}
include '../includes/workspace.php';
include '../templates/inventory_valuation.php';
include '../includes/end.php';

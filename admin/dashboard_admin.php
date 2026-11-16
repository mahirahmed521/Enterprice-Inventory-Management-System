<?php
$pageTitle = 'Admin dashboard';
$section = 'Overview';
$required_role = 'ADMIN';
require '../includes/auth.php';

$result = mysqli_query($conn, "SELECT SUM(total_inventory_cost) AS total FROM vw_inventory_valuation");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['inventory_value'] = $row['total'];
if ($row['total'] === null) {
    $summary['inventory_value'] = 0;
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE is_active=1");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['active_products'] = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE is_active=1");
if ($result == false) {
    exit('Could not load the dashboard.');
}
$row = mysqli_fetch_assoc($result);
$summary['active_accounts'] = $row['total'];
$rows = mysqli_query($conn, "SELECT stock_movements.movement_date, products.sku, warehouses.warehouse_name,
                                    stock_movements.movement_type, stock_movements.quantity_change
                             FROM stock_movements
                             JOIN products USING(product_id)
                             JOIN warehouses USING(warehouse_id)
                             ORDER BY stock_movements.movement_id DESC LIMIT 8");
if ($rows == false) {
    exit('Could not load the dashboard.');
}
include '../includes/workspace.php';
include '../templates/dashboard_admin.php';
include '../includes/end.php';

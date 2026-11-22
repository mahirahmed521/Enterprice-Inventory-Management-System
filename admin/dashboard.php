<?php
// One dashboard for the whole team. What each person sees depends on their role:
//   ADMIN   everything, including stock value and margin
//   MANAGER everything except stock value and margin
//   STAFF   the warehouse view (shipping, deliveries, stock alerts, movements)
$pageTitle = 'Dashboard';
$section = 'Overview';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$overview_level = 'staff';
$overview_show_money = false;
if ($account['role'] == 'ADMIN') {
    $overview_level = 'admin';
    $overview_show_money = true;
} elseif ($account['role'] == 'MANAGER') {
    $overview_level = 'manager';
}

include '../includes/overview_data.php';
include '../includes/workspace.php';
include '../templates/dashboard.php';
include '../includes/end.php';

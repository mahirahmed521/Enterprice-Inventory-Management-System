<?php
require '../includes/auth.php';

$home = 'dashboard_customer.php';
if ($account['role'] == 'ADMIN') {
    $home = 'dashboard_admin.php';
} elseif ($account['role'] == 'MANAGER') {
    $home = 'dashboard_manager.php';
} elseif ($account['role'] == 'STAFF') {
    $home = 'dashboard_staff.php';
}
header('Location: ' . $home);
exit;

<?php
// "My profile" link of the staff portal: sends each team member to the profile page of their own role.
require_once __DIR__ . '/../includes/auth.php';
$profile_target = 'staff_profile.php';
if ($account['role'] == 'ADMIN') {
    $profile_target = 'admin_profile.php';
} elseif ($account['role'] == 'MANAGER') {
    $profile_target = 'manager_profile.php';
}
header('Location: ' . $profile_target);
exit;

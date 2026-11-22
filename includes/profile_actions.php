<?php
// Handles the forms of every profile page and loads what the page needs to draw itself.
//
// The page sets these before including this file:
//   $profile_role         ADMIN, MANAGER, STAFF or CUSTOMER
//   $user_id              the signed-in user's id
//   $customer_id and $customer   customers only (from includes/current_customer.php)
//   $profile_notice_key   session key the page layout shows its success message from
//                         ('notice' in the staff portal, 'store_notice' in the store)
//
// It leaves these for the page: $error, $profile_user, $profile, $profile_values, $profile_keep,
// $profile_warehouses and $managed_warehouses.
require_once __DIR__ . '/profile_data.php';

if (isset($profile_notice_key) == false) {
    $profile_notice_key = 'notice';
}
if (isset($error) == false) {
    $error = '';
}
if (isset($customer_id) == false) {
    $customer_id = 0;
}
if (isset($customer) == false) {
    $customer = null;
}
$user_id = (int) $user_id;
$profile_page = basename($_SERVER['SCRIPT_NAME']);

if (profile_ensure_table($conn) == false) {
    $error = 'The profile table could not be created. Import dabatases/update_user_profiles.sql and reload this page.';
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_action = '';
    if (isset($_POST['profile_action'])) {
        $profile_action = $_POST['profile_action'];
    }
    $profile_done = '';
    if (profile_csrf_ok() == false) {
        $error = 'This page expired. Reload it and try again.';
    } elseif ($profile_action == 'details') {
        $error = profile_save_details($conn, $profile_role, $user_id, $customer_id);
        $profile_done = 'Your details were saved.';
    } elseif ($profile_action == 'account') {
        $error = profile_save_account($conn, $profile_role, $user_id, $customer_id);
        $profile_done = 'Your sign-in details were saved.';
    } elseif ($profile_action == 'password') {
        $error = profile_change_password($conn, $user_id);
        $profile_done = 'Your password was changed.';
    } elseif ($profile_action == 'notifications') {
        $error = profile_save_notifications($conn, $profile_role, $user_id);
        $profile_done = 'Your notification choices were saved.';
    } elseif ($profile_action == 'avatar') {
        $error = profile_upload_avatar($conn, $user_id);
        $profile_done = 'Your profile picture was updated.';
    } elseif ($profile_action == 'remove_avatar') {
        $error = profile_remove_avatar($conn, $user_id);
        $profile_done = 'Your profile picture was removed.';
    } else {
        $error = 'Choose a valid action.';
    }
    if ($error == '') {
        $_SESSION[$profile_notice_key] = $profile_done;
        header('Location: ' . $profile_page);
        exit;
    }
}

// Everything the page shows
$profile_user = array('user_id' => $user_id, 'username' => '', 'email' => '', 'role' => $profile_role, 'created_at' => '');
$found_users = db_rows($conn, "SELECT user_id, username, email, role, created_at FROM users WHERE user_id=$user_id");
if (count($found_users) > 0) {
    $profile_user = $found_users[0];
}
$profile = profile_load($conn, $user_id);
$profile_values = profile_values($profile_role, $profile, $customer);
$profile_keep = false;
if ($error != '' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_keep = true;
}
$profile_warehouses = db_rows($conn, 'SELECT warehouse_id, warehouse_name, is_active FROM warehouses ORDER BY warehouse_name');
$managed_warehouses = array();
if ($profile_role == 'MANAGER') {
    $managed_warehouses = db_rows($conn, "SELECT warehouse_code, warehouse_name, location_address, is_active FROM warehouses WHERE manager_id=$user_id ORDER BY warehouse_name");
}

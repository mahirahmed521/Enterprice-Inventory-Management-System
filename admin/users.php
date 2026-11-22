<?php
require_once __DIR__ . '/../includes/check_fields.php';
$required_role = 'ADMIN';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Users';
$section = 'Team';
$error = '';
include '../includes/edit_request.php';
$action = '';
$username = '';
$email = '';
$password = '';
$role = 'STAFF';
$customer_id = '';
$is_active = '1';
$hash = '';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if (isset($_POST['username'])) {
    $username = $_POST['username'];
}
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
}
if (isset($_POST['role'])) {
    $role = $_POST['role'];
}
if (isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];
}
if (isset($_POST['is_active'])) {
    $is_active = $_POST['is_active'];
}

if ($id > 0) {
    $records = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id");
    $record = null;
    if ($records) {
        $record = mysqli_fetch_assoc($records);
    }
    if ($record == null) {
        exit('Record not found.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $username = $record['username'];
        $email = $record['email'];
        $role = $record['role'];
        $customer_id = $record['customer_id'];
        $is_active = $record['is_active'];
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    if ($id == $user_id) {
        $error = 'You are using this account.';
    } else {
        $deleted = mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
        if ($deleted == false) {
            $error = 'This account is still in use.';
        }
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    $valid_status = false;
    if ($is_active === '1') {
        $valid_status = true;
    } elseif ($is_active === '0') {
        $valid_status = true;
    }
    $valid_role = false;
    if ($role == 'ADMIN') {
        $valid_role = true;
    } elseif ($role == 'MANAGER') {
        $valid_role = true;
    } elseif ($role == 'STAFF') {
        $valid_role = true;
    } elseif ($role == 'CUSTOMER') {
        $valid_role = true;
    }
    if ($username === '') {
        $error = 'Complete the required fields.';
    } elseif ($email === '') {
        $error = 'Complete the required fields.';
    } elseif ($valid_status == false) {
        $error = 'Choose a valid status.';
    } elseif ($valid_role == false) {
        $error = 'Choose a valid role.';
    } elseif ($role == 'CUSTOMER') {
        if ($customer_id == '') {
            $error = 'Select the linked customer.';
        }
    } elseif (isset($customer_id[0])) {
        $error = 'Only customer accounts can link to customers.';
    }
    if ($error == '') {
        if ($id == $user_id) {
            $keep_admin_role = $role == 'ADMIN';
            if ($keep_admin_role == false) {
                $error = 'Keep the signed-in administrator active.';
            } elseif ($is_active == '0') {
                $error = 'Keep the signed-in administrator active.';
            }
        } elseif ($id == 0) {
            if ($password == '') {
                $error = 'Enter a password.';
            }
        }
    }

    if ($error == '') {
        $sql_text = [
            'username' => $username,
            'email' => $email
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $customer_id_sql = 'NULL';
        if (isset($customer_id[0])) {
            $valid_digits = isset($customer_id[0]);
            $digit_position = 0;
            while (isset($customer_id[$digit_position])) {
                if ($customer_id[$digit_position] < '0') {
                    $valid_digits = false;
                }
                if ($customer_id[$digit_position] > '9') {
                    $valid_digits = false;
                }
                $digit_position = $digit_position + 1;
            }
            if ($valid_digits == false) {
                exit('Choose a valid record.');
            }
            $customer_id_sql = $customer_id;
        }
        $details = "username='$sql_text[username]',
                    email='$sql_text[email]',
                    role='$role',
                    customer_id=$customer_id_sql,
                    is_active='$is_active'";
        
        if (isset($password[0])) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $details = $details . ",password_hash='$hash'";
        }
        if ($id == 0) {
            $sql = "INSERT INTO users (username, email, role, customer_id, is_active, password_hash)
                VALUES ('$sql_text[username]', '$sql_text[email]', '$role', $customer_id_sql, '$is_active', '$hash')";
        } else {
            $sql = "UPDATE users SET $details WHERE user_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the username, email and role.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: users.php');
        exit;
    }
}

$rows = mysqli_query($conn, 'SELECT user_id,username,email,role,is_active FROM users ORDER BY user_id DESC');
include '../includes/workspace.php';
include '../templates/users.php';
include '../includes/end.php';

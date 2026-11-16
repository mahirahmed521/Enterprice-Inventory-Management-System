<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Warehouses';
$section = 'Directory';
$error = '';
include '../includes/edit_request.php';
$action = '';
$warehouse_code = '';
$warehouse_name = '';
$location_address = '';
$manager_id = '';
$is_active = '1';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['warehouse_code'])) {
    $warehouse_code = trim($_POST['warehouse_code']);
}
if (isset($_POST['warehouse_name'])) {
    $warehouse_name = trim($_POST['warehouse_name']);
}
if (isset($_POST['location_address'])) {
    $location_address = trim($_POST['location_address']);
}
if (isset($_POST['manager_id'])) {
    $manager_id = trim($_POST['manager_id']);
}
if (isset($_POST['is_active'])) {
    $is_active = trim($_POST['is_active']);
}

require '../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

if ($id > 0) {
    $records = mysqli_query($conn, "SELECT * FROM warehouses WHERE warehouse_id=$id");
    $record = null;
    if ($records) {
        $record = mysqli_fetch_assoc($records);
    }
    if ($record == null) {
        exit('Record not found.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $warehouse_code = $record['warehouse_code'];
        $warehouse_name = $record['warehouse_name'];
        $location_address = $record['location_address'];
        $manager_id = $record['manager_id'];
        $is_active = $record['is_active'];
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    mysqli_query($conn, 'START TRANSACTION');
    $parent = mysqli_query($conn, "SELECT warehouse_id FROM warehouses WHERE warehouse_id=$id FOR UPDATE");
    if ($parent == false) {
        $error = 'Could not check this record.';
    } else {
        $bins = mysqli_query($conn, "SELECT quantity_on_hand, quantity_reserved FROM warehouse_stock WHERE warehouse_id=$id FOR UPDATE");
        if ($bins == false) {
            $error = 'Could not check warehouse stock.';
        } else {
            while ($bin = mysqli_fetch_assoc($bins)) {
                if ($bin['quantity_on_hand']) {
                    $error = 'This record is still in use.';
                    break;
                }
                if ($bin['quantity_reserved']) {
                    $error = 'This record is still in use.';
                    break;
                }
            }
        }
    }
    if ($error == '') {
        if (mysqli_query($conn, "DELETE FROM warehouses WHERE warehouse_id=$id") == false) {
            $error = 'This record is still in use.';
        } elseif (mysqli_affected_rows($conn) == 0) {
            $error = 'Record not found.';
        }
    }
    if ($error == '') {
        if (mysqli_query($conn, 'COMMIT') == false) {
            $error = 'Could not save the change.';
        }
    }
    if ($error) {
        mysqli_query($conn, 'ROLLBACK');
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    $valid_status = false;
    if ($is_active === '1') {
        $valid_status = true;
    } elseif ($is_active === '0') {
        $valid_status = true;
    }
    if ($warehouse_code === '') {
        $error = 'Complete the required fields.';
    } elseif ($warehouse_name === '') {
        $error = 'Complete the required fields.';
    } elseif ($valid_status == false) {
        $error = 'Choose a valid status.';
    }

    if ($error == '') {
        $sql_text = [
            'warehouse_code' => $warehouse_code,
            'warehouse_name' => $warehouse_name,
            'location_address' => $location_address
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $manager_id_sql = 'NULL';
        if (isset($manager_id[0])) {
            if (ctype_digit($manager_id) == false) {
                exit('Choose a valid record.');
            }
            $manager_id_sql = $manager_id;
        }

        if ($id == 0) {
            $sql = "INSERT INTO warehouses (warehouse_code, warehouse_name, location_address, manager_id, is_active)
                VALUES ('$sql_text[warehouse_code]', '$sql_text[warehouse_name]', '$sql_text[location_address]', $manager_id_sql, '$is_active')";
        } else {
            $sql = "UPDATE warehouses SET warehouse_code='$sql_text[warehouse_code]',
                    warehouse_name='$sql_text[warehouse_name]',
                    location_address='$sql_text[location_address]',
                    manager_id=$manager_id_sql,
                    is_active='$is_active'
                    WHERE warehouse_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the warehouse code and manager.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: warehouses.php');
        exit;
    }
}

$rows = mysqli_query($conn, 'SELECT warehouse_id,warehouse_code,warehouse_name,location_address,is_active FROM warehouses ORDER BY warehouse_id DESC');
include '../includes/workspace.php';
include '../templates/warehouses.php';
include '../includes/end.php';

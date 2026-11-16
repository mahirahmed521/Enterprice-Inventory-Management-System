<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Sales orders';
$section = 'Order management';
$error = '';
$id = 0;
$product = 0;
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id']) == false) {
        exit('Order not found.');
    }
    $id = $_GET['id'];
}
if (isset($_GET['product'])) {
    if (ctype_digit($_GET['product'])) {
        $product = $_GET['product'];
    }
}
$action = '';
$warehouse_id = '';
$party_id = '';
$product_id = '';
$line_id = '';
$quantity = '';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['warehouse_id'])) {
    $warehouse_id = trim($_POST['warehouse_id']);
}
if (isset($_POST['party_id'])) {
    $party_id = trim($_POST['party_id']);
}
if (isset($_POST['product_id'])) {
    $product_id = trim($_POST['product_id']);
}
if (isset($_POST['line_id'])) {
    $line_id = trim($_POST['line_id']);
}
if (isset($_POST['quantity'])) {
    $quantity = trim($_POST['quantity']);
}
if (ctype_digit($warehouse_id) == false) {
    $warehouse_id = 0;
}
if (ctype_digit($party_id) == false) {
    $party_id = 0;
}
if (ctype_digit($product_id) == false) {
    $product_id = 0;
}
if (ctype_digit($line_id) == false) {
    $line_id = 0;
}
require '../includes/auth.php';
$can_prepare = $supervisor;
$is_customer = false;
if ($account['role'] == 'CUSTOMER') {
    $is_customer = true;
    $can_prepare = true;
    $party_id = 0;
    if (isset($account['customer_id'])) {
        $party_id = $account['customer_id'];
    }
} elseif ($warehouse_access == false) {
    exit('Access denied.');
}

$order = null;
$status = '';
$list_sql = "SELECT o.*, partner.customer_name AS party, w.warehouse_name
             FROM sales_orders o JOIN customers partner ON partner.customer_id=o.customer_id
             JOIN warehouses w ON w.warehouse_id=o.warehouse_id";
if ($id < 0) {
    exit('Order not found.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_query($conn, 'START TRANSACTION');
}
if ($id > 0) {
    $sql = "$list_sql WHERE o.so_id=$id";
    if ($is_customer) {
        $sql = $sql . " AND o.customer_id=$party_id";
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = $sql . ' FOR UPDATE';
    }
    $result = mysqli_query($conn, $sql);
    $order = null;
    if ($result) {
        $order = mysqli_fetch_assoc($result);
    }
    if ($order == null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            mysqli_query($conn, 'ROLLBACK');
        }
        exit('Order not found.');
    }
    $status = $order['status'];
    $pageTitle = $order['so_number'];
    $old_warehouse = $order['warehouse_id'];
}

$editable = false;
$can_cancel = false;
$can_approve = false;
if ($can_prepare) {
    if ($order == null) {
        $editable = true;
    } elseif ($status == 'PENDING') {
        $editable = true;
        $can_cancel = true;
    }
}
if ($supervisor) {
    if ($status == 'CONFIRMED') {
        $can_cancel = true;
    } elseif ($status == 'PENDING') {
        $can_approve = true;
    }
}
$can_ship = false;
if ($warehouse_access) {
    if ($status == 'CONFIRMED') {
        $can_ship = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $original_id = $id;
    $item_action = false;
    if ($action == 'add') {
        $item_action = true;
    } elseif ($action == 'edit') {
        $item_action = true;
    } elseif ($action == 'remove') {
        $item_action = true;
    } elseif ($action == 'delete') {
        $item_action = true;
    }
    if ($order == null) {
        $error = 'Choose an order first.';
        if ($action == 'create') {
            if ($can_prepare) {
                $error = '';
                include '../includes/so_details.php';
            }
        }
    } elseif ($action == 'header') {
        if ($editable) {
            include '../includes/so_details.php';
        } else {
            $error = 'Only a draft order can be edited.';
        }
    } elseif ($item_action) {
        if ($editable) {
            include '../includes/so_items.php';
        } else {
            $error = 'Only a draft order can be edited.';
        }
    } else {
        include '../includes/so_status.php';
    }
    if ($error == '') {
        if (mysqli_query($conn, 'COMMIT')) {
            $destination = 'sales_orders.php';
            if ($id > 0) {
                $destination = $destination . '?id=' . $id;
            }
            $_SESSION['notice'] = 'Order saved.';
            header('Location: ' . $destination);
            exit;
        }
    }
    mysqli_query($conn, 'ROLLBACK');
    $id = $original_id;
    if ($error == '') {
        $error = 'The order could not be saved.';
    }
}

if ($is_customer) {
    $list_sql = $list_sql . " WHERE o.customer_id=$party_id";
}
$rows = mysqli_query($conn, "$list_sql ORDER BY o.so_id DESC");
if ($order) {
    $lines = mysqli_query($conn, "SELECT i.*,p.product_name FROM sales_order_items i
        JOIN products p ON i.product_id=p.product_id WHERE i.so_id=$id ORDER BY i.so_item_id");
}
include '../includes/workspace.php';
include '../templates/sales_orders.php';
include '../includes/end.php';

<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Purchase orders';
$section = 'Order management';
$error = '';
$id = 0;
$product = 0;
if (isset($_GET['id'])) {
    $valid_digits = isset($_GET['id'][0]);
    $digit_position = 0;
    while (isset($_GET['id'][$digit_position])) {
        if ($_GET['id'][$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($_GET['id'][$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        exit('Order not found.');
    }
    $id = $_GET['id'];
}
if (isset($_GET['product'])) {
    $valid_digits = isset($_GET['product'][0]);
    $digit_position = 0;
    while (isset($_GET['product'][$digit_position])) {
        if ($_GET['product'][$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($_GET['product'][$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits) {
        $product = $_GET['product'];
    }
}
$action = '';
$warehouse_id = '';
$party_id = '';
$product_id = '';
$line_id = '';
$quantity = '';
$previous_received = '';
$expected_date = '';

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
if (isset($_POST['previous_received'])) {
    $previous_received = trim($_POST['previous_received']);
}
if (isset($_POST['expected_date'])) {
    $expected_date = trim($_POST['expected_date']);
}
$valid_digits = isset($warehouse_id[0]);
$digit_position = 0;
while (isset($warehouse_id[$digit_position])) {
    if ($warehouse_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($warehouse_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $warehouse_id = 0;
}
$valid_digits = isset($party_id[0]);
$digit_position = 0;
while (isset($party_id[$digit_position])) {
    if ($party_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($party_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $party_id = 0;
}
$valid_digits = isset($product_id[0]);
$digit_position = 0;
while (isset($product_id[$digit_position])) {
    if ($product_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($product_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $product_id = 0;
}
$valid_digits = isset($line_id[0]);
$digit_position = 0;
while (isset($line_id[$digit_position])) {
    if ($line_id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($line_id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $line_id = 0;
}
require '../includes/auth.php';
if ($warehouse_access == false) {
    exit('Access denied.');
}

$can_prepare = $supervisor;
$is_customer = false;
$order = null;
$status = '';
$list_sql = "SELECT o.*, partner.company_name AS party, w.warehouse_name
             FROM purchase_orders o JOIN suppliers partner ON partner.supplier_id=o.supplier_id
             JOIN warehouses w ON w.warehouse_id=o.warehouse_id";
if ($id < 0) {
    exit('Order not found.');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_query($conn, 'START TRANSACTION');
}
if ($id > 0) {
    $sql = "$list_sql WHERE o.po_id=$id";
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
    $pageTitle = $order['po_number'];
    $old_warehouse = $order['warehouse_id'];
}

$editable = false;
$can_cancel = false;
$can_approve = false;
if ($can_prepare) {
    if ($order == null) {
        $editable = true;
    } elseif ($status == 'DRAFT') {
        $editable = true;
        $can_cancel = true;
    }
}
if ($supervisor) {
    if ($status == 'ORDERED') {
        $can_cancel = true;
    } elseif ($status == 'DRAFT') {
        $can_approve = true;
    }
}
$can_receive = false;
if ($status == 'ORDERED') {
    $can_receive = true;
} elseif ($status == 'PARTIAL') {
    $can_receive = true;
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
                include '../includes/po_details.php';
            }
        }
    } elseif ($action == 'header') {
        if ($editable) {
            include '../includes/po_details.php';
        } else {
            $error = 'Only a draft order can be edited.';
        }
    } elseif ($item_action) {
        if ($editable) {
            include '../includes/po_items.php';
        } else {
            $error = 'Only a draft order can be edited.';
        }
    } else {
        include '../includes/po_status.php';
    }
    if ($error == '') {
        if (mysqli_query($conn, 'COMMIT')) {
            $destination = 'purchase_orders.php';
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

$rows = mysqli_query($conn, "$list_sql ORDER BY o.po_id DESC");
if ($order) {
    $lines = mysqli_query($conn, "SELECT i.*,p.product_name FROM purchase_order_items i
        JOIN products p ON i.product_id=p.product_id WHERE i.po_id=$id ORDER BY i.po_item_id");
}
include '../includes/workspace.php';
include '../templates/purchase_orders.php';
include '../includes/end.php';

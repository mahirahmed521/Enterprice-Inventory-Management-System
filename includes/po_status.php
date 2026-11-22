<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($id)) {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    } else {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } else {
            $id = '';
        }
    }
}
if (is_array($id)) {
    $error = 'Could not find the order.';
    return;
}
$id = (string) $id;
$valid_digits = isset($id[0]);
$digit_position = 0;
while (isset($id[$digit_position])) {
    if ($id[$digit_position] < '0') {
        $valid_digits = false;
    }
    if ($id[$digit_position] > '9') {
        $valid_digits = false;
    }
    $digit_position = $digit_position + 1;
}
if ($valid_digits == false) {
    $error = 'Could not find the order.';
    return;
}
$id = (int) $id;
if ($action == 'order') {
    if ($can_approve == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $items = mysqli_query($conn, "SELECT po_item_id FROM purchase_order_items WHERE po_id=$id LIMIT 1");
    $item = null;
    if ($items) {
        $item = mysqli_fetch_assoc($items);
    }
    if ($item == null) {
        $error = 'Add a product before placing the order.';
        return;
    }
    $next_status = 'ORDERED';
} elseif ($action == 'cancel') {
    if ($can_cancel == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $next_status = 'CANCELLED';
} elseif ($action == 'receive') {
    if ($can_receive == false) {
        $error = 'This action is unavailable.';
        return;
    }
    $items = mysqli_query($conn, "SELECT * FROM purchase_order_items WHERE po_id=$id AND po_item_id=$line_id");
    $item = null;
    if ($items) {
        $item = mysqli_fetch_assoc($items);
    }
    if ($item == null) {
        $error = 'Select an item on this order.';
        return;
    }
    $outstanding = $item['quantity_ordered'] - $item['quantity_received'];
    $valid_digits = isset($quantity[0]);
    $digit_position = 0;
    while (isset($quantity[$digit_position])) {
        if ($quantity[$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($quantity[$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        $error = 'Check the outstanding quantity.';
        return;
    } elseif ($quantity < 1) {
        $error = 'Check the outstanding quantity.';
        return;
    } elseif ($quantity > $outstanding) {
        $error = 'Check the outstanding quantity.';
        return;
    }
    $receipt_matches = $previous_received == $item['quantity_received'];
    $valid_digits = isset($previous_received[0]);
    $digit_position = 0;
    while (isset($previous_received[$digit_position])) {
        if ($previous_received[$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($previous_received[$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        $error = 'The received quantity has changed. Reload the order.';
        return;
    } elseif ($receipt_matches == false) {
        $error = 'The received quantity has changed. Reload the order.';
        return;
    }
    $warehouse_result = mysqli_query($conn, "SELECT warehouse_id FROM purchase_orders WHERE po_id=$id");
    if ($warehouse_result == false) {
        $error = 'Could not find the order warehouse.';
        return;
    }
    $warehouse = mysqli_fetch_assoc($warehouse_result);
    if ($warehouse == null) {
        $error = 'Could not find the order warehouse.';
        return;
    }
    $old_warehouse = $warehouse['warehouse_id'];
    $received_product = $item['product_id'];
    if (isset($_SESSION['user_id'])) {
        $performed_by = (int) $_SESSION['user_id'];
    } else {
        $error = 'Could not identify the user recording the delivery.';
        return;
    }
    $sql = "INSERT INTO stock_movements (product_id, warehouse_id, quantity_change, movement_type, reference_type, reference_id, performed_by)
                VALUES ($received_product, $old_warehouse, $quantity, 'PURCHASE_RECEIPT', 'PO', $id, $performed_by)";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not record the delivery.';
        return;
    }
    $sql = "UPDATE purchase_order_items SET quantity_received=quantity_received+$quantity WHERE po_item_id=$line_id";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not update the received quantity.';
        return;
    }
    $open = mysqli_query($conn, "SELECT po_item_id FROM purchase_order_items WHERE po_id=$id AND quantity_received<quantity_ordered LIMIT 1");
    if ($open == false) {
        $error = 'Could not check the remaining items.';
        return;
    }
    if ($open->num_rows > 0) {
        $next_status = 'PARTIAL';
    } else {
        $next_status = 'RECEIVED';
    }
} else {
    $error = 'This action is unavailable.';
    return;
}
if (mysqli_query($conn, "UPDATE purchase_orders SET status='$next_status' WHERE po_id=$id") == false) {
    $error = 'Could not update the order status.';
}

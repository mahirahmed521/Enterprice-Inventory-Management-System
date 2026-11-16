<?php
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
    if (ctype_digit($quantity) == false) {
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
    if (ctype_digit($previous_received) == false) {
        $error = 'The received quantity has changed. Reload the order.';
        return;
    } elseif ($receipt_matches == false) {
        $error = 'The received quantity has changed. Reload the order.';
        return;
    }
    $received_product = $item['product_id'];
    $sql = "INSERT INTO stock_movements (product_id, warehouse_id, quantity_change, movement_type, reference_type, reference_id, performed_by)
                VALUES ($received_product, $old_warehouse, $quantity, 'PURCHASE_RECEIPT', 'PO', $id, $user_id)";
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

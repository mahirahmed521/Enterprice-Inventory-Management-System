<?php
if ($action == 'delete') {

    if (mysqli_query($conn, "DELETE FROM purchase_orders WHERE po_id=$id")) {
        $id = 0;
    } else {
        $error = 'Could not delete the draft.';
    }
    return;
}
$needs_quantity = true;
if ($action == 'remove') {
    $needs_quantity = false;
}
if ($needs_quantity) {
    $valid_digits = isset($quantity[0]);
    $digit_position = 0;
    while (isset($quantity[$digit_position])) {
        if ($quantity[$digit_position] < '0' || $quantity[$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        $error = 'Enter a positive whole quantity.';
        return;
    } elseif ($quantity < 1) {
        $error = 'Enter a positive whole quantity.';
        return;
    }
}
if ($action == 'add') {
    $result = mysqli_query($conn, "SELECT cost_price FROM products WHERE product_id=$product_id AND is_active=1 FOR UPDATE");
    if ($result == false) {
        $error = 'Could not read the product price.';
        return;
    }
    $product_row = mysqli_fetch_assoc($result);
    if ($product_row == null) {
        $error = 'Choose an active product.';
        return;
    }
    $price = $product_row['cost_price'];
    $sql = "INSERT INTO purchase_order_items (po_id, product_id, quantity_ordered, unit_cost)
            VALUES ($id, $product_id, $quantity, '$price')";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Choose an active product.';
        return;
    }
    return;
} else {
    $result = mysqli_query($conn, "SELECT product_id,quantity_ordered FROM purchase_order_items WHERE po_item_id=$line_id AND po_id=$id");
    $item = null;
    if ($result) {
        $item = mysqli_fetch_assoc($result);
    }
    if ($item == null) {
        $error = 'Item not found on this order.';
        return;
    }

    if ($action == 'edit') {
        $sql = "UPDATE purchase_order_items SET quantity_ordered=$quantity WHERE po_item_id=$line_id AND po_id=$id";
    } else {
        $sql = "DELETE FROM purchase_order_items WHERE po_item_id=$line_id AND po_id=$id";
    }
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not change the item.';
        return;
    }
}
$result = mysqli_query($conn, "SELECT SUM(quantity_ordered * unit_cost) AS total FROM purchase_order_items WHERE po_id=$id");
if ($result == false) {
    $error = 'Could not calculate the order total.';
    return;
}
$row = mysqli_fetch_assoc($result);
$total = $row['total'];
if ($total === null) {
    $total = 0;
}
$sql = "UPDATE purchase_orders SET total_amount='$total' WHERE po_id=$id";
if (mysqli_query($conn, $sql) == false) {
    $error = 'Could not update the order total.';
}

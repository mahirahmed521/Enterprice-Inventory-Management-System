<?php
global $conn;
include __DIR__ . '/release_sales_stock.php';
if (!isset($id)) {
    $error = 'Invalid sales order.';
    return;
}
if (!is_numeric($id)) {
    $error = 'Invalid sales order.';
    return;
}
$id = (int) $id;
if ($action == 'delete') {
    include __DIR__ . '/release_sales_stock.php';
    
    if ($error) {
        return;
    }
    if (mysqli_query($conn, "DELETE FROM sales_orders WHERE so_id=$id")) {
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
        if ($quantity[$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($quantity[$digit_position] > '9') {
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
$new_item = false;
if ($action == 'add') {
    $new_item = true;
} elseif ($action == 'create') {
    $new_item = true;
}
if ($new_item) {
    $result = mysqli_query($conn, "SELECT selling_price FROM products WHERE product_id=$product_id AND is_active=1 FOR UPDATE");
    if ($result == false) {
        $error = 'Could not read the product price.';
        return;
    }
    $product_row = mysqli_fetch_assoc($result);
    if ($product_row == null) {
        $error = 'Choose an active product.';
        return;
    }
    $price = $product_row['selling_price'];
    $sql = "INSERT INTO sales_order_items (so_id, product_id, quantity, unit_price)
            VALUES ($id, $product_id, $quantity, '$price')";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Choose an active product with enough available stock.';
        return;
    }

} else {
    $result = mysqli_query($conn, "SELECT product_id,quantity FROM sales_order_items WHERE so_item_id=$line_id AND so_id=$id");
    $item = null;
    if ($result) {
        $item = mysqli_fetch_assoc($result);
    }
    if ($item == null) {
        $error = 'Item not found on this order.';
        return;
    }
    $product_id = $item['product_id'];
    $new_quantity = 0;
    if ($action == 'edit') {
        $new_quantity = $quantity;
    }
    $difference = $new_quantity - $item['quantity'];
    if ($difference) {
        $sql = "UPDATE warehouse_stock SET quantity_reserved=quantity_reserved+$difference
                WHERE product_id=$product_id AND warehouse_id=$old_warehouse
                  AND quantity_reserved+$difference >= 0
                  AND quantity_reserved+$difference <= quantity_on_hand";
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Insufficient available stock.';
            return;
        } elseif (mysqli_affected_rows($conn) == 0) {
            $error = 'Insufficient available stock.';
            return;
        }
    }
    if ($action == 'edit') {
        $sql = "UPDATE sales_order_items SET quantity=$quantity WHERE so_item_id=$line_id AND so_id=$id";
    } else {
        $sql = "DELETE FROM sales_order_items WHERE so_item_id=$line_id AND so_id=$id";
    }
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not change the item.';
        return;
    }
}
$result = mysqli_query($conn, "SELECT SUM(quantity * unit_price) AS total FROM sales_order_items WHERE so_id=$id");
if ($result == false) {
    $error = 'Could not calculate the order total.';
    return;
}
$row = mysqli_fetch_assoc($result);
$total = $row['total'];
if ($total === null) {
    $total = 0;
}
$sql = "UPDATE sales_orders SET total_amount='$total' WHERE so_id=$id";
if (mysqli_query($conn, $sql) == false) {
    $error = 'Could not update the order total.';
}

<?php
$sql = "SELECT product_id, SUM(quantity) AS units FROM sales_order_items
        WHERE so_id=$id GROUP BY product_id ORDER BY product_id";
$items_to_release = mysqli_query($conn, $sql);
if ($items_to_release == false) {
    $error = 'Could not read reservations.';
    return;
}
while ($item = mysqli_fetch_assoc($items_to_release)) {
    $sku_id = $item['product_id'];
    $units = $item['units'];
    $sql = "UPDATE warehouse_stock SET quantity_reserved=quantity_reserved-$units
            WHERE product_id=$sku_id AND warehouse_id=$old_warehouse AND quantity_reserved>=$units";
    if (mysqli_query($conn, $sql) == false) {
        $error = 'Could not release the reserved units.';
        return;
    } elseif (mysqli_affected_rows($conn) == 0) {
        $error = 'Could not release the reserved units.';
        return;
    }
    if ($action == 'ship') {
        $shipped = 0 - $units;
        $sql = "INSERT INTO stock_movements (product_id, warehouse_id, quantity_change, movement_type, reference_type, reference_id, performed_by)
                VALUES ($sku_id, $old_warehouse, $shipped, 'SALE_SHIPMENT', 'SO', $id, $user_id)";
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not record the shipment.';
            return;
        }
    } elseif ($action == 'header') {
        $sql = "UPDATE warehouse_stock SET quantity_reserved=quantity_reserved+$units
                WHERE product_id=$sku_id AND warehouse_id=$warehouse_id AND quantity_on_hand-quantity_reserved>=$units";
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Insufficient stock at the new warehouse.';
            return;
        } elseif (mysqli_affected_rows($conn) == 0) {
            $error = 'Insufficient stock at the new warehouse.';
            return;
        }
    }
}

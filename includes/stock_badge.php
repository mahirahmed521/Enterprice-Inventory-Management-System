<?php
// Turns $stock_status (IN_STOCK, LOW_STOCK, OUT_OF_STOCK) into a Bootstrap badge class and label.
$stock_class = 'text-bg-success';
$stock_label = 'In Stock';
if ($stock_status == 'LOW_STOCK') {
    $stock_class = 'text-bg-warning';
    $stock_label = 'Low Stock';
} elseif ($stock_status == 'OUT_OF_STOCK') {
    $stock_class = 'text-bg-danger';
    $stock_label = 'Out of Stock';
}

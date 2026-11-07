<?php
// Dashboard queries are read-only. Dates are shown in Bangladesh time.
date_default_timezone_set('Asia/Dhaka');
mysqli_query($conn, "SET time_zone = '+06:00'");

function dashboard_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$warehouses = mysqli_fetch_all(mysqli_query($conn, 'SELECT warehouse_id, warehouse_name FROM warehouses ORDER BY warehouse_name'), MYSQLI_ASSOC);
$warehouseId = (int) ($_GET['warehouse'] ?? 0);
if (!in_array($warehouseId, array_map('intval', array_column($warehouses, 'warehouse_id')), true)) {
    $warehouseId = 0;
}
$days = (int) ($_GET['days'] ?? 7);
if (!in_array($days, [7, 30], true)) {
    $days = 7;
}
$warehouseName = 'All warehouses';
foreach ($warehouses as $warehouse) {
    if ((int) $warehouse['warehouse_id'] === $warehouseId) {
        $warehouseName = $warehouse['warehouse_name'];
    }
}

// Only validated integer IDs are used in the optional SQL conditions.
$stockCondition = $warehouseId ? 'WHERE warehouse_id = ' . $warehouseId : '';
$movementCondition = $warehouseId ? ' AND m.warehouse_id = ' . $warehouseId : '';
$productRows = mysqli_fetch_all(mysqli_query($conn, "SELECT p.product_id, p.product_name, p.sku,
    p.cost_price, p.selling_price, p.reorder_point, p.unit_of_measure,
    COALESCE(c.name, 'Uncategorized') AS category_name,
    COALESCE(s.company_name, 'Not assigned') AS supplier_name,
    COALESCE(stock.on_hand, 0) AS on_hand, COALESCE(stock.reserved, 0) AS reserved
    FROM products p
    LEFT JOIN categories c ON c.category_id = p.category_id
    LEFT JOIN suppliers s ON s.supplier_id = p.preferred_supplier_id
    LEFT JOIN (SELECT product_id, SUM(quantity_on_hand) AS on_hand,
        SUM(quantity_reserved) AS reserved FROM warehouse_stock $stockCondition GROUP BY product_id) stock
        ON stock.product_id = p.product_id
    WHERE p.is_active = 1 ORDER BY p.product_name"), MYSQLI_ASSOC);

$totals = ['products' => count($productRows), 'on_hand' => 0, 'reserved' => 0, 'available' => 0, 'value' => 0];
$health = ['healthy' => 0, 'low' => 0, 'out' => 0];
$categories = [];
foreach ($productRows as &$product) {
    $product['on_hand'] = (int) $product['on_hand'];
    $product['reserved'] = (int) $product['reserved'];
    $product['available'] = $product['on_hand'] - $product['reserved'];
    $product['status'] = $product['available'] <= 0 ? 'out' : ($product['available'] <= (int) $product['reorder_point'] ? 'low' : 'healthy');
    $health[$product['status']]++;
    $totals['on_hand'] += $product['on_hand'];
    $totals['reserved'] += $product['reserved'];
    $totals['available'] += $product['available'];
    $totals['value'] += $product['on_hand'] * (float) $product['cost_price'];
    $categories[$product['category_name']] = $product['category_name'];
}
unset($product);
sort($categories);
$attention = $health['low'] + $health['out'];
$healthyPercent = $totals['products'] ? (int) round(100 * $health['healthy'] / $totals['products']) : 0;
$healthyAngle = $totals['products'] ? 360 * $health['healthy'] / $totals['products'] : 0;
$lowAngle = $healthyAngle + ($totals['products'] ? 360 * $health['low'] / $totals['products'] : 0);

$today = new DateTimeImmutable('today');
$startDate = $today->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
$endDate = $today->modify('+1 day')->format('Y-m-d');
$movementRows = mysqli_fetch_all(mysqli_query($conn, "SELECT DATE(m.movement_date) AS day,
    SUM(CASE WHEN m.quantity_change > 0 THEN m.quantity_change ELSE 0 END) AS incoming,
    SUM(CASE WHEN m.quantity_change < 0 THEN -m.quantity_change ELSE 0 END) AS outgoing
    FROM stock_movements m WHERE m.movement_date >= '$startDate' AND m.movement_date < '$endDate'
    $movementCondition GROUP BY DATE(m.movement_date) ORDER BY day"), MYSQLI_ASSOC);
$byDay = array_column($movementRows, null, 'day');
$chart = [];
$incoming = 0;
$outgoing = 0;
$chartMax = 1;
for ($day = 0; $day < $days; $day++) {
    $date = new DateTimeImmutable($startDate . ' +' . $day . ' days');
    $row = $byDay[$date->format('Y-m-d')] ?? [];
    $in = (int) ($row['incoming'] ?? 0);
    $out = (int) ($row['outgoing'] ?? 0);
    $chart[] = ['label' => $date->format('d M'), 'incoming' => $in, 'outgoing' => $out];
    $incoming += $in;
    $outgoing += $out;
    $chartMax = max($chartMax, $in, $out);
}
$recent = mysqli_fetch_all(mysqli_query($conn, "SELECT m.movement_type, m.quantity_change, m.movement_date,
    p.product_name, w.warehouse_name FROM stock_movements m
    JOIN products p ON p.product_id = m.product_id
    JOIN warehouses w ON w.warehouse_id = m.warehouse_id
    WHERE m.movement_date >= '$startDate' AND m.movement_date < '$endDate' $movementCondition
    ORDER BY m.movement_date DESC, m.movement_id DESC LIMIT 5"), MYSQLI_ASSOC);
$movementLabels = ['PURCHASE_RECEIPT' => 'Stock received', 'SALE_SHIPMENT' => 'Order shipped',
    'TRANSFER_IN' => 'Transfer in', 'TRANSFER_OUT' => 'Transfer out', 'ADJUSTMENT' => 'Stock adjustment'];
$statusLabels = ['healthy' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock'];
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

<?php
// Everything the dashboard overview shows, read from the database in one place.
// Expects $conn. Fills $ov (numbers) and the $ov_* lists. Every query is safe to fail: a missing table just shows zero.
$ov = array();
$ov_currency = '৳';

$ov['today'] = db_scalar($conn, 'SELECT CURDATE()', date('Y-m-d'));

// ---- Sales (cancelled orders never count)
$ov['sales_total'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM sales_orders WHERE status<>'CANCELLED'");
$ov['sales_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status<>'CANCELLED'");
$ov['avg_order'] = 0;
if ($ov['sales_orders'] > 0) {
    $ov['avg_order'] = $ov['sales_total'] / $ov['sales_orders'];
}
$ov['month_sales'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM sales_orders WHERE status<>'CANCELLED' AND order_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$ov['month_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status<>'CANCELLED' AND order_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$ov['today_sales'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM sales_orders WHERE status<>'CANCELLED' AND order_date = CURDATE()");
$ov['today_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status<>'CANCELLED' AND order_date = CURDATE()");
$ov['unpaid_amount'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM sales_orders WHERE status<>'CANCELLED' AND payment_status='UNPAID'");
$ov['unpaid_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status<>'CANCELLED' AND payment_status='UNPAID'");
$ov['to_ship'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status='CONFIRMED'");
$ov['pending_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE status='PENDING'");

// ---- Stock
$ov['cost_value'] = (float) db_scalar($conn, 'SELECT SUM(total_inventory_cost) FROM vw_inventory_valuation');
$ov['retail_value'] = (float) db_scalar($conn, 'SELECT SUM(total_on_hand * selling_price) FROM vw_inventory_valuation');
$ov['units_on_hand'] = (int) db_scalar($conn, 'SELECT SUM(total_on_hand) FROM vw_inventory_valuation');
$ov['units_reserved'] = (int) db_scalar($conn, 'SELECT SUM(total_reserved) FROM vw_inventory_valuation');
$ov['low_stock'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM vw_reorder_alerts a JOIN products p ON p.product_id=a.product_id WHERE p.is_active=1 AND a.available_to_sell > 0');
$ov['out_stock'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM vw_reorder_alerts a JOIN products p ON p.product_id=a.product_id WHERE p.is_active=1 AND a.available_to_sell <= 0');
$ov['active_products'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM products WHERE is_active=1');
$ov['categories'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM categories');

// ---- People and places
$ov['customers'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM customers');
$ov['new_customers'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM customers WHERE created_at >= (NOW() - INTERVAL 30 DAY)');
$ov['suppliers'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM suppliers WHERE is_active=1');
$ov['warehouses'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM warehouses WHERE is_active=1');
$ov['open_pos'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM purchase_orders WHERE status IN ('DRAFT','ORDERED','PARTIAL')");
$ov['open_po_value'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM purchase_orders WHERE status IN ('DRAFT','ORDERED','PARTIAL')");
$ov['reviews'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM product_reviews');
$ov['avg_rating'] = (float) db_scalar($conn, 'SELECT AVG(rating) FROM product_reviews');
$ov['wishlist_items'] = (int) db_scalar($conn, 'SELECT COUNT(*) FROM wishlist_items');

// ---- Sales for the last 14 days (days with no orders show as zero)
$ov_chart = array();
$chart_rows = db_rows($conn, "SELECT order_date, COUNT(*) AS orders_count, SUM(total_amount) AS sales_amount FROM sales_orders WHERE status<>'CANCELLED' AND order_date >= (CURDATE() - INTERVAL 13 DAY) GROUP BY order_date");
$chart_by_date = array();
$c = 0;
while (isset($chart_rows[$c])) {
    $chart_by_date[$chart_rows[$c]['order_date']] = $chart_rows[$c];
    $c = $c + 1;
}
$ov['chart_max'] = 0;
$ov['chart_sales'] = 0;
$ov['chart_orders'] = 0;
$back = 13;
while ($back >= 0) {
    $day = date('Y-m-d', strtotime($ov['today'] . ' -' . $back . ' day'));
    $day_sales = 0;
    $day_orders = 0;
    if (isset($chart_by_date[$day])) {
        $day_sales = (float) $chart_by_date[$day]['sales_amount'];
        $day_orders = (int) $chart_by_date[$day]['orders_count'];
    }
    if ($day_sales > $ov['chart_max']) {
        $ov['chart_max'] = $day_sales;
    }
    $ov['chart_sales'] = $ov['chart_sales'] + $day_sales;
    $ov['chart_orders'] = $ov['chart_orders'] + $day_orders;
    $ov_chart[] = array('date' => $day, 'label' => date('j', strtotime($day)), 'long' => date('D, d M', strtotime($day)), 'sales' => $day_sales, 'orders' => $day_orders);
    $back = $back - 1;
}

// ---- Order pipeline by status, and how customers pay
$ov_status = array('PENDING' => 0, 'CONFIRMED' => 0, 'SHIPPED' => 0, 'CANCELLED' => 0);
$status_rows = db_rows($conn, 'SELECT status, COUNT(*) AS total FROM sales_orders GROUP BY status');
$s = 0;
while (isset($status_rows[$s])) {
    $ov_status[$status_rows[$s]['status']] = (int) $status_rows[$s]['total'];
    $s = $s + 1;
}
$ov['all_orders'] = $ov_status['PENDING'] + $ov_status['CONFIRMED'] + $ov_status['SHIPPED'] + $ov_status['CANCELLED'];
$ov_payments = db_rows($conn, "SELECT payment_method, COUNT(*) AS total, SUM(total_amount) AS amount FROM sales_orders WHERE status<>'CANCELLED' GROUP BY payment_method ORDER BY total DESC");

// ---- Lists
$ov_top_products = db_rows($conn, "SELECT p.product_id, p.product_name, p.sku, p.product_image, SUM(i.quantity) AS units, SUM(i.quantity * i.unit_price) AS revenue FROM sales_order_items i JOIN sales_orders o ON o.so_id=i.so_id JOIN products p ON p.product_id=i.product_id WHERE o.status<>'CANCELLED' GROUP BY p.product_id, p.product_name, p.sku, p.product_image ORDER BY units DESC, revenue DESC LIMIT 5");
$ov_alerts = db_rows($conn, 'SELECT p.product_id, p.product_name, p.sku, p.product_image, a.available_to_sell, a.reorder_point FROM vw_reorder_alerts a JOIN products p ON p.product_id=a.product_id WHERE p.is_active=1 ORDER BY a.available_to_sell ASC, p.product_name LIMIT 6');
$ov_orders = db_rows($conn, 'SELECT o.so_id, o.so_number, o.order_date, o.status, o.payment_status, o.payment_method, o.order_source, o.total_amount, c.customer_name FROM sales_orders o JOIN customers c ON c.customer_id=o.customer_id ORDER BY o.so_id DESC LIMIT 7');
$ov_purchases = db_rows($conn, 'SELECT o.po_id, o.po_number, o.order_date, o.expected_date, o.status, o.total_amount, s.company_name FROM purchase_orders o JOIN suppliers s ON s.supplier_id=o.supplier_id ORDER BY o.po_id DESC LIMIT 4');
$ov_warehouses = db_rows($conn, 'SELECT w.warehouse_id, w.warehouse_code, w.warehouse_name, w.is_active, u.username AS manager_name, COUNT(ws.product_id) AS product_lines, COALESCE(SUM(ws.quantity_on_hand),0) AS on_hand, COALESCE(SUM(ws.quantity_on_hand - ws.quantity_reserved),0) AS available, COALESCE(SUM(ws.quantity_reserved),0) AS reserved FROM warehouses w LEFT JOIN users u ON u.user_id=w.manager_id LEFT JOIN warehouse_stock ws ON ws.warehouse_id=w.warehouse_id GROUP BY w.warehouse_id, w.warehouse_code, w.warehouse_name, w.is_active, u.username ORDER BY w.warehouse_name');
$ov_movements = db_rows($conn, 'SELECT m.movement_date, p.sku, w.warehouse_name, m.movement_type, m.quantity_change FROM stock_movements m JOIN products p USING(product_id) JOIN warehouses w USING(warehouse_id) ORDER BY m.movement_id DESC LIMIT 6');
$ov_reviews = db_rows($conn, 'SELECT r.rating, r.comment, r.created_at, c.customer_name, p.product_id, p.product_name FROM product_reviews r JOIN customers c ON c.customer_id=r.customer_id JOIN products p ON p.product_id=r.product_id ORDER BY r.review_id DESC LIMIT 3');
$ov_customers = db_rows($conn, 'SELECT customer_name, email, phone FROM customers ORDER BY customer_id DESC LIMIT 3');
$ov_suppliers = db_rows($conn, 'SELECT company_name, contact_person, is_active FROM suppliers ORDER BY supplier_id DESC LIMIT 3');

// ---- Work queues for the dashboard (what needs doing next)
$ov['deliveries_due'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM purchase_orders WHERE status IN ('ORDERED','PARTIAL')");
$ov['pending_online'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE order_source='ECOMMERCE' AND status='PENDING'");
$ov_ship_queue = db_rows($conn, "SELECT o.so_id, o.so_number, o.order_date, c.customer_name, w.warehouse_name FROM sales_orders o JOIN customers c ON c.customer_id=o.customer_id JOIN warehouses w ON w.warehouse_id=o.warehouse_id WHERE o.status='CONFIRMED' ORDER BY o.order_date, o.so_id LIMIT 6");
$ov_confirm_queue = db_rows($conn, "SELECT o.so_id, o.so_number, o.order_date, o.order_source, o.total_amount, c.customer_name FROM sales_orders o JOIN customers c ON c.customer_id=o.customer_id WHERE o.status='PENDING' ORDER BY o.order_date, o.so_id LIMIT 6");
$ov_delivery_queue = db_rows($conn, "SELECT o.po_id, o.po_number, o.expected_date, o.status, s.company_name FROM purchase_orders o JOIN suppliers s ON s.supplier_id=o.supplier_id WHERE o.status IN ('ORDERED','PARTIAL') ORDER BY (o.expected_date IS NULL), o.expected_date, o.po_id LIMIT 6");

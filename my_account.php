<?php
// Customer account page: order summary, wishlist preview, reviews waiting, and delivery details.
// This replaces the old customer dashboard that lived inside the staff portal.
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

$currency = '৳';
$error = '';

if (isset($_SESSION['user_id']) == false) {
    $_SESSION['redirect_after_login'] = 'my_account.php';
    header('Location: login.php');
    exit;
}

// Team members have no customer record: send them to their own dashboard.
$session_role = '';
if (isset($_SESSION['role'])) {
    $session_role = $_SESSION['role'];
}
if ($session_role == 'ADMIN' || $session_role == 'MANAGER' || $session_role == 'STAFF') {
    header('Location: admin/dashboard.php');
    exit;
}

include __DIR__ . '/includes/current_customer.php';

$summary = array('open_orders' => 0, 'shipments' => 0, 'total_spent' => 0, 'all_orders' => 0, 'wishlist' => 0, 'reviews' => 0);
$latest_orders = array();
$wish_preview = array();
$to_review = array();
$form_name = '';
$form_phone = '';
$form_address = '';

if ($customer_id > 0) {
    $form_name = $customer['customer_name'];
    $form_phone = $customer['phone'];
    $form_address = $customer['shipping_address'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $form_name = '';
        $form_phone = '';
        $form_address = '';
        if (isset($_POST['customer_name'])) {
            $form_name = trim($_POST['customer_name']);
        }
        if (isset($_POST['phone'])) {
            $form_phone = trim($_POST['phone']);
        }
        if (isset($_POST['shipping_address'])) {
            $form_address = trim($_POST['shipping_address']);
        }
        if ($form_name === '') {
            $error = 'Enter your delivery name.';
        } else {
            $save = mysqli_prepare($conn, 'UPDATE customers SET customer_name=?, phone=?, shipping_address=? WHERE customer_id=?');
            mysqli_stmt_bind_param($save, 'sssi', $form_name, $form_phone, $form_address, $customer_id);
            if (mysqli_stmt_execute($save)) {
                $_SESSION['store_notice'] = 'Delivery details saved.';
                header('Location: my_account.php');
                exit;
            }
            $error = 'Could not save your delivery details.';
        }
    }

    $summary['open_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE customer_id=$customer_id AND status IN ('PENDING','CONFIRMED')");
    $summary['shipments'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE customer_id=$customer_id AND status='SHIPPED'");
    $summary['total_spent'] = (float) db_scalar($conn, "SELECT SUM(total_amount) FROM sales_orders WHERE customer_id=$customer_id AND status<>'CANCELLED'");
    $summary['all_orders'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM sales_orders WHERE customer_id=$customer_id AND status<>'CANCELLED'");
    $summary['wishlist'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM wishlist_items WHERE customer_id=$customer_id");
    $summary['reviews'] = (int) db_scalar($conn, "SELECT COUNT(*) FROM product_reviews WHERE customer_id=$customer_id");
    $latest_orders = db_rows($conn, "SELECT so_id,so_number,order_date,status,payment_status,total_amount FROM sales_orders WHERE customer_id=$customer_id ORDER BY so_id DESC LIMIT 6");
    $wish_preview = db_rows($conn, "SELECT p.product_id, p.product_name, p.product_image, p.selling_price FROM wishlist_items w JOIN products p ON p.product_id=w.product_id WHERE w.customer_id=$customer_id AND p.is_active=1 ORDER BY w.wishlist_id DESC LIMIT 4");
    $to_review = db_rows($conn, "SELECT DISTINCT p.product_id, p.product_name, p.product_image FROM sales_orders o JOIN sales_order_items i ON i.so_id=o.so_id JOIN products p ON p.product_id=i.product_id WHERE o.customer_id=$customer_id AND o.status='SHIPPED' AND NOT EXISTS (SELECT 1 FROM product_reviews r WHERE r.product_id=p.product_id AND r.customer_id=$customer_id) LIMIT 4");
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>My Account | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h1 class="h3 mb-0">My Account</h1>
            <a class="btn btn-outline-orange btn-sm" href="my_profile.php">Edit my profile</a>
        </div>

        <?php if ($customer_id == 0) { ?>
            <div class="alert alert-warning">We could not find the delivery record for this account. Please contact the store team.</div>
            <a class="btn btn-outline-orange" href="logout.php">Sign out</a>
        <?php } else { ?>

        <?php if ($error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Open orders</div><div class="fs-3 fw-bold"><?php echo $summary['open_orders']; ?></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Delivered</div><div class="fs-3 fw-bold"><?php echo $summary['shipments']; ?></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total spent</div><div class="fs-3 fw-bold"><?php echo $currency . number_format($summary['total_spent'], 0); ?></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><div class="text-muted small">Wishlist</div><div class="fs-3 fw-bold"><?php echo $summary['wishlist']; ?></div></div></div></div>
        </div>
        <p class="text-muted small mb-4"><?php echo $summary['all_orders']; ?> order(s) placed &middot; <?php echo $summary['reviews']; ?> review(s) written</p>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h5 mb-0">My latest orders</h2>
                        <a href="my_orders.php">Track &amp; manage &rarr;</a>
                    </div>
                    <?php if (count($latest_orders) == 0) { ?>
                        <p class="text-muted mb-0">No orders yet. <a href="shop.php">Start shopping</a>.</p>
                    <?php } else { ?>
                    <div class="table-responsive"><table class="table align-middle mb-0">
                        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Payment</th><th class="text-end">Total</th><th></th></tr></thead>
                        <tbody>
                        <?php $o = 0; while (isset($latest_orders[$o])) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($latest_orders[$o]['so_number']); ?></td>
                                <td><?php echo htmlspecialchars($latest_orders[$o]['order_date']); ?></td>
                                <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($latest_orders[$o]['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($latest_orders[$o]['payment_status']); ?></td>
                                <td class="text-end"><?php echo $currency . number_format((float) $latest_orders[$o]['total_amount'], 2); ?></td>
                                <td><a href="order_invoice.php?so_id=<?php echo (int) $latest_orders[$o]['so_id']; ?>">Invoice</a></td>
                            </tr>
                        <?php $o = $o + 1; } ?>
                        </tbody>
                    </table></div>
                    <?php } ?>
                </div></div>

                <?php if (count($wish_preview) > 0) { ?>
                <div class="card mb-4"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h5 mb-0">From my wishlist</h2>
                        <a href="wishlist.php">Open wishlist &rarr;</a>
                    </div>
                    <?php $w = 0; while (isset($wish_preview[$w])) { ?>
                        <a class="d-flex align-items-center gap-3 py-2 text-decoration-none text-reset border-top" href="product.php?id=<?php echo (int) $wish_preview[$w]['product_id']; ?>">
                            <img src="<?php echo htmlspecialchars(product_image_url($wish_preview[$w]['product_image'])); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="" width="48" height="48" style="object-fit:cover">
                            <span class="flex-grow-1"><?php echo htmlspecialchars($wish_preview[$w]['product_name']); ?></span>
                            <strong><?php echo $currency . number_format((float) $wish_preview[$w]['selling_price'], 2); ?></strong>
                        </a>
                    <?php $w = $w + 1; } ?>
                </div></div>
                <?php } ?>

                <?php if (count($to_review) > 0) { ?>
                <div class="card mb-4"><div class="card-body">
                    <h2 class="h5 mb-2">How did these work out?</h2>
                    <p class="text-muted small">Delivered items you have not reviewed yet.</p>
                    <?php $r = 0; while (isset($to_review[$r])) { ?>
                        <a class="d-flex align-items-center gap-3 py-2 text-decoration-none text-reset border-top" href="product.php?id=<?php echo (int) $to_review[$r]['product_id']; ?>#reviews">
                            <img src="<?php echo htmlspecialchars(product_image_url($to_review[$r]['product_image'])); ?>" onerror="this.onerror=null;this.src='pictures/default.png'" alt="" width="48" height="48" style="object-fit:cover">
                            <span class="flex-grow-1"><?php echo htmlspecialchars($to_review[$r]['product_name']); ?></span>
                            <span class="text-muted small">Write a review</span>
                        </a>
                    <?php $r = $r + 1; } ?>
                </div></div>
                <?php } ?>
            </div>

            <div class="col-lg-4">
                <div class="card"><div class="card-body">
                    <h2 class="h5 mb-3">Delivery details</h2>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="customer_name">Delivery name</label>
                            <input class="form-control" id="customer_name" name="customer_name" maxlength="150" value="<?php echo htmlspecialchars($form_name); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone</label>
                            <input class="form-control" id="phone" name="phone" maxlength="30" value="<?php echo htmlspecialchars((string) $form_phone); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="shipping_address">Delivery address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"><?php echo htmlspecialchars((string) $form_address); ?></textarea>
                        </div>
                        <button class="btn btn-orange w-100" type="submit">Save details</button>
                    </form>
                </div></div>
            </div>
        </div>
        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

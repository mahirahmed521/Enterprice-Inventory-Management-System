<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Customers';
$error = '';
$message = $_SESSION['customer_message'] ?? '';
unset($_SESSION['customer_message']);

if (!isset($_SESSION['customer_token'])) {
    $_SESSION['customer_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $id = (int) ($_POST['customer_id'] ?? 0);

    if (!is_string($token) || !hash_equals($_SESSION['customer_token'], $token)) {
        $error = 'Please reload the page and try again.';
    } elseif ($id < 1) {
        $error = 'Customer not found.';
    } else {
        try {
            mysqli_execute_query($conn, 'DELETE FROM customers WHERE customer_id = ?', [$id]);

            if (mysqli_affected_rows($conn) == 1) {
                $_SESSION['customer_message'] = 'Customer deleted.';
            } else {
                $_SESSION['customer_message'] = 'Customer not found.';
            }
            header('Location: customers.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1451) {
                $error = 'This customer has sales orders and cannot be deleted.';
            } else {
                error_log($exception->getMessage());
                $error = 'Could not delete the customer. Please try again.';
            }
        }
    }
}

$customers = mysqli_query($conn, 'SELECT customer_id, customer_name, email, phone, shipping_address FROM customers ORDER BY customer_id DESC');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/business-overview.css">
</head>
<body class="workspace-page">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <main class="page-wrapper">
        <div class="page-content">
            <div class="page-heading">
                <div>
                    <span class="eyebrow">YOUR PARTNERS</span>
                    <h1>Customers<span class="heading-dot">.</span></h1>
                    <p>Add, view, edit and delete your customers.</p>
                </div>
                <a href="customer_form.php" class="btn btn-success">Add customer</a>
            </div>

            <?php if ($message !== '') { ?>
                <div class="alert alert-success" role="status"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>
            <?php if ($error !== '') { ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <section class="surface records-surface">
                <div class="section-heading">
                    <div><h2>All customers</h2><p><?php echo mysqli_num_rows($customers); ?> customers</p></div>
                    <a href="dashboard.php" class="text-link">Back to overview →</a>
                </div>
                <div class="table-responsive">
                    <table class="table inventory-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Shipping address</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['shipping_address'] ?? ''); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="customer_form.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-outline-success">Edit</a>
                                            <form method="post" action="customers.php" onsubmit="return confirm('Delete this customer?');">
                                                <input type="hidden" name="token" value="<?php echo $_SESSION['customer_token']; ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (mysqli_num_rows($customers) === 0) { ?>
                                <tr><td colspan="5" class="empty-state">No customers yet. Click Add customer to get started.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    <?php include '../includes/admin_script.php'; ?>
</body>
</html>

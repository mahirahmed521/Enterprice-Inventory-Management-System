<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Add customer';
$buttonText = 'Add customer';
$formAction = 'customer_form.php';
$name = '';
$email = '';
$phone = '';
$address = '';
$error = '';
$notFound = false;

function form_text($field) {
    if (isset($_POST[$field]) && is_string($_POST[$field])) {
        return trim($_POST[$field]);
    }
    return '';
}

if ($id > 0) {
    $pageTitle = 'Edit customer';
    $buttonText = 'Save changes';
    $formAction = 'customer_form.php?id=' . $id;
}

if (!isset($_SESSION['customer_token'])) {
    $_SESSION['customer_token'] = bin2hex(random_bytes(16));
}

if (isset($_GET['id'])) {
    $result = mysqli_execute_query($conn, 'SELECT * FROM customers WHERE customer_id = ?', [$id]);
    $customer = mysqli_fetch_assoc($result);

    if (!$customer || $id < 1) {
        http_response_code(404);
        $notFound = true;
        $error = 'Customer not found. Return to the customer list.';
    } else {
        $name = $customer['customer_name'];
        $email = $customer['email'] ?? '';
        $phone = $customer['phone'] ?? '';
        $address = $customer['shipping_address'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$notFound) {
    $name = form_text('name');
    $email = form_text('email');
    $phone = form_text('phone');
    $address = form_text('address');
    $token = form_text('token');

    if (!hash_equals($_SESSION['customer_token'], $token)) {
        $error = 'Please reload the page and try again.';
    } elseif ($name === '') {
        $error = 'Please enter the customer name.';
    } elseif (mb_strlen($name) > 150) {
        $error = 'Customer name must be within 150 characters.';
    } elseif (mb_strlen($email) > 150) {
        $error = 'Email must be within 150 characters.';
    } elseif (mb_strlen($phone) > 30) {
        $error = 'Phone must be within 30 characters.';
    } elseif (mb_strlen($address) > 2000) {
        $error = 'Address must be within 2,000 characters.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address or leave it empty.';
    } else {
        try {
            if ($id > 0) {
                $sql = 'UPDATE customers SET customer_name = ?, email = ?, phone = ?, shipping_address = ? WHERE customer_id = ?';
                mysqli_execute_query($conn, $sql, [$name, $email, $phone, $address, $id]);
                $_SESSION['customer_message'] = 'Customer updated.';
            } else {
                $sql = 'INSERT INTO customers (customer_name, email, phone, shipping_address) VALUES (?, ?, ?, ?)';
                mysqli_execute_query($conn, $sql, [$name, $email, $phone, $address]);
                $_SESSION['customer_message'] = 'Customer added.';
            }

            header('Location: customers.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            $error = 'Could not save the customer. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle; ?> | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="workspace-page">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <main class="page-wrapper">
        <div class="page-content">
            <div class="page-heading">
                <div><span class="eyebrow">YOUR PARTNERS</span><h1><?php echo $pageTitle; ?><span class="heading-dot">.</span></h1><p>Only the customer name is required.</p></div>
                <a href="customers.php" class="text-link">Back to customers →</a>
            </div>
            <?php if ($error !== '') { ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>
            <?php if (!$notFound) { ?>
                <section class="surface p-4">
                    <form method="post" action="<?php echo $formAction; ?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['customer_token']; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer-name" class="form-label">Customer name *</label>
                                <input type="text" id="customer-name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" maxlength="150" required>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-email" class="form-label">Email</label>
                                <input type="email" id="customer-email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" maxlength="150">
                            </div>
                            <div class="col-md-6">
                                <label for="customer-phone" class="form-label">Phone</label>
                                <input type="text" id="customer-phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" maxlength="30">
                            </div>
                            <div class="col-12">
                                <label for="customer-address" class="form-label">Shipping address</label>
                                <textarea id="customer-address" name="address" class="form-control" rows="3" maxlength="2000"><?php echo htmlspecialchars($address); ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success"><?php echo $buttonText; ?></button>
                            <a href="customers.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </section>
            <?php } ?>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    <?php include '../includes/admin_script.php'; ?>
</body>
</html>

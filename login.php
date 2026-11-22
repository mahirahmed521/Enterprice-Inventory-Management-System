<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$email = '';
$password = '';
$error = '';
$posted = false;
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = true;
}

if ($posted) {
    $sql_text = array('email' => $email);
    include __DIR__ . '/includes/quote_fields.php';
    $users = mysqli_query($conn, "SELECT user_id, username, password_hash, role, customer_id, is_active FROM users WHERE email='$sql_text[email]'");
    $user = null;
    if ($users) {
        $user = mysqli_fetch_assoc($users);
    }
    if ($user == null) {
        $error = 'The email or password is not right.';
    } elseif (password_verify($password, $user['password_hash']) == false) {
        $error = 'The email or password is not right.';
    } elseif ($user['is_active'] == 0) {
        $error = 'This account is switched off. Contact the store team.';
    } else {
        $cart_before = array();
        if (isset($_SESSION['cart'])) {
            $cart_before = $_SESSION['cart'];
        }
        $go_to = '';
        if (isset($_SESSION['redirect_after_login'])) {
            $go_to = $_SESSION['redirect_after_login'];
        }
        session_regenerate_id(true);
        $_SESSION['cart'] = $cart_before;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        unset($_SESSION['redirect_after_login']);
        if ($user['role'] == 'CUSTOMER') {
            if ($go_to == 'checkout.php' || $go_to == 'my_orders.php') {
                header('Location: ' . $go_to);
            } elseif (count($_SESSION['cart']) > 0) {
                header('Location: checkout.php');
            } else {
                header('Location: my_orders.php');
            }
        } else {
            header('Location: admin/dashboard.php');
        }
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Sign In | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card"><div class="card-body p-4">
                    <h1 class="h3 mb-3">Sign In</h1>
                    <?php if ($error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                        </div>
                        <button class="btn btn-orange w-100 btn-lg" type="submit">Sign In</button>
                    </form>
                    <p class="text-center mt-3 mb-0">New here? <a href="register.php">Create a customer account</a></p>
                    <p class="text-center small text-muted mt-2 mb-0">Team members sign in here too and go to the admin dashboard.</p>
                </div></div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

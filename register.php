<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

$name = '';
$username = '';
$email = '';
$phone = '';
$address = '';
$password = '';
$password_confirm = '';
$error = '';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'CUSTOMER') {
    header('Location: my_account.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($name == '' || $username == '' || $email == '' || $password == '' || $password_confirm == '') {
        $error = 'Complete all required fields.';
    } elseif (strlen($name) > 150 || strlen($username) > 50 || strlen($email) > 100 || strlen($phone) > 30) {
        $error = 'One or more fields are too long.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Choose a password with at least 8 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'The passwords do not match.';
    }

    if ($error == '') {
        $check = mysqli_prepare($conn, 'SELECT user_id FROM users WHERE email=? OR username=? LIMIT 1');
        if ($check == false) {
            $error = 'Could not check account details. Please try again.';
        } else {
            mysqli_stmt_bind_param($check, 'ss', $email, $username);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);
            if (mysqli_stmt_num_rows($check) > 0) {
                $error = 'That email or username is already in use.';
            }
            mysqli_stmt_close($check);
        }
    }

    if ($error == '') {
        mysqli_begin_transaction($conn);
        $customer_insert = mysqli_prepare($conn, 'INSERT INTO customers (customer_name, email, phone, shipping_address) VALUES (?, ?, ?, ?)');
        $user_insert = mysqli_prepare($conn, 'INSERT INTO users (username, email, password_hash, role, customer_id, is_active) VALUES (?, ?, ?, \'CUSTOMER\', ?, 1)');
        if ($customer_insert == false || $user_insert == false) {
            $error = 'Could not create your account. Please try again.';
        } else {
            mysqli_stmt_bind_param($customer_insert, 'ssss', $name, $email, $phone, $address);
            $customer_saved = mysqli_stmt_execute($customer_insert);
            if ($customer_saved) {
                $new_customer_id = mysqli_insert_id($conn);
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($user_insert, 'sssi', $username, $email, $password_hash, $new_customer_id);
                $user_saved = mysqli_stmt_execute($user_insert);
            } else {
                $user_saved = false;
            }
            mysqli_stmt_close($customer_insert);
            mysqli_stmt_close($user_insert);
            if ($customer_saved && $user_saved) {
                $new_user_id = mysqli_insert_id($conn);
                mysqli_commit($conn);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['role'] = 'CUSTOMER';
                $_SESSION['username'] = $username;
                $next_page = 'home.php';
                if (isset($_SESSION['redirect_after_login'])) {
                    $requested_page = $_SESSION['redirect_after_login'];
                    if ($requested_page == 'checkout.php' || $requested_page == 'my_orders.php' || $requested_page == 'wishlist.php' || $requested_page == 'my_account.php' || $requested_page == 'my_profile.php') {
                        $next_page = $requested_page;
                    }
                    unset($_SESSION['redirect_after_login']);
                }
                header('Location: ' . $next_page);
                exit;
            }
            mysqli_rollback($conn);
            $error = 'Could not create your account. Check that the email and username are available.';
        }
        if ($customer_insert == false || $user_insert == false) {
            mysqli_rollback($conn);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Create Account | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6">
                <div class="card"><div class="card-body p-4">
                    <h1 class="h3 mb-3">Create a customer account</h1>
                    <p class="text-muted">Sign up to place orders and save products to your wishlist.</p>
                    <?php if ($error != '') { ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php } ?>
                    <form method="post">
                        <div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" type="text" id="name" name="name" maxlength="150" value="<?php echo htmlspecialchars($name); ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control" type="text" id="username" name="username" maxlength="50" value="<?php echo htmlspecialchars($username); ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($email); ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="phone">Phone <span class="text-muted">(optional)</span></label><input class="form-control" type="tel" id="phone" name="phone" maxlength="30" value="<?php echo htmlspecialchars($phone); ?>"></div>
                        <div class="mb-3"><label class="form-label" for="address">Delivery address <span class="text-muted">(optional)</span></label><textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea></div>
                        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" minlength="8" required></div>
                        <div class="mb-3"><label class="form-label" for="password_confirm">Confirm password</label><input class="form-control" type="password" id="password_confirm" name="password_confirm" minlength="8" required></div>
                        <button class="btn btn-orange w-100 btn-lg" type="submit">Create Account</button>
                    </form>
                    <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Sign in</a></p>
                </div></div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

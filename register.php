<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

// Page values first
$name = '';
$email = '';
$phone = '';
$address = '';
$password = '';
$confirm = '';
$error = '';
$posted = false;
if (isset($_POST['name'])) {
    $name = trim($_POST['name']);
}
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
}
if (isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);
}
if (isset($_POST['address'])) {
    $address = trim($_POST['address']);
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
}
if (isset($_POST['confirm'])) {
    $confirm = $_POST['confirm'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posted = true;
}

if ($posted) {
    if ($name == '' || $email == '' || $password == '') {
        $error = 'Enter your name, email and a password.';
    } elseif (strlen($name) > 100) {
        $error = 'Use a shorter name (100 characters at most).';
    } elseif (strlen($email) > 100 || strpos($email, '@') == false) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Use a password with at least 6 characters.';
    } elseif ($password != $confirm) {
        $error = 'The two passwords do not match.';
    }

    if ($error == '') {
        $sql_text = array('name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address);
        include __DIR__ . '/includes/quote_fields.php';
        $taken = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$sql_text[email]'");
        if (mysqli_num_rows($taken) > 0) {
            $error = 'That email already has an account. Try signing in instead.';
        }
    }

    if ($error == '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $username = substr($name, 0, 40);
        $sql_text = array('name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'username' => $username);
        include __DIR__ . '/includes/quote_fields.php';
        $created = false;
        mysqli_query($conn, 'START TRANSACTION');
        $sql = "INSERT INTO customers (customer_name, email, phone, shipping_address) VALUES ('$sql_text[name]', '$sql_text[email]', '$sql_text[phone]', '$sql_text[address]')";
        if (mysqli_query($conn, $sql)) {
            $new_customer_id = mysqli_insert_id($conn);
            // Two customers can share a name, so make the username unique
            $same_name = mysqli_query($conn, "SELECT user_id FROM users WHERE username='$sql_text[username]'");
            if (mysqli_num_rows($same_name) > 0) {
                $username = substr($name, 0, 40) . '-' . $new_customer_id;
            }
            $sql_text = array('username' => $username, 'email' => $email);
            include __DIR__ . '/includes/quote_fields.php';
            $sql = "INSERT INTO users (username, email, password_hash, role, customer_id, is_active) VALUES ('$sql_text[username]', '$sql_text[email]', '$hash', 'CUSTOMER', $new_customer_id, 1)";
            if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);
                $created = true;
            }
        }
        if ($created) {
            mysqli_query($conn, 'COMMIT');
            // Sign the new customer in and carry on
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
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['role'] = 'CUSTOMER';
            $_SESSION['username'] = $username;
            unset($_SESSION['redirect_after_login']);
            $_SESSION['store_notice'] = 'Welcome to Stockroom Electronics! Your account is ready.';
            if ($go_to == 'checkout.php') {
                header('Location: checkout.php');
            } elseif (count($_SESSION['cart']) > 0) {
                header('Location: cart.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
        mysqli_query($conn, 'ROLLBACK');
        $error = 'We could not create your account. Please try again.';
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
                    <h1 class="h3 mb-3">Create Account</h1>
                    <?php if ($error != '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="name">Full name</label>
                            <input class="form-control" type="text" id="name" name="name" maxlength="100" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" type="email" id="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="phone">Phone</label>
                                <input class="form-control" type="text" id="phone" name="phone" maxlength="30" value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address">Shipping address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" type="password" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="confirm">Confirm password</label>
                                <input class="form-control" type="password" id="confirm" name="confirm" required>
                            </div>
                        </div>
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

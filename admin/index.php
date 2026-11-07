<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        require_once '../config/db.php';
        $statement = mysqli_prepare($conn, 'SELECT user_id, username, email, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($statement, 's', $email);
        mysqli_stmt_execute($statement);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));

        // The imported database already contains bcrypt password hashes.
        if ($user && password_verify($password, $user['password_hash'])) {
            if ((int) $user['is_active'] !== 1) {
                $error = 'Your account is inactive. Please contact your administrator.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Email or password is incorrect.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="login-page">
    <main class="login-layout">
        <section class="login-aside">
            <a class="brand" href="index.php"><span class="brand-icon"><i class="bx bx-package" aria-hidden="true"></i></span> Stockroom<span class="brand-dot">.</span></a>
            <div class="login-story">
                <span class="eyebrow">INVENTORY MANAGEMENT</span>
                <h1>A little order.<br>A better workday.</h1>
                <p>Your products, people, and stock.<br>One place to keep it all together.</p>
                <div class="illustration-wrap"><img src="../assets/images/login-cover.svg" alt="" class="login-illustration"></div>
            </div>
            <div class="aside-footer"><span class="status-dot"></span> Your everyday inventory workspace</div>
        </section>
        <section class="login-form-panel">
            <div class="login-form-wrap">
                <div class="form-icon"><i class="bx bx-log-in-circle" aria-hidden="true"></i></div>
                <span class="eyebrow">LET'S GET STARTED</span>
                <h2>Welcome back</h2>
                <p class="form-intro">Sign in to your inventory workspace.</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif (isset($_GET['logged_out'])): ?>
                    <div class="alert alert-success" role="status">You have been logged out successfully.</div>
                <?php endif; ?>

                <form method="post" action="index.php">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>" autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                            <button class="btn password-toggle" type="button" id="toggle-password" aria-label="Show password" aria-pressed="false"><i class="bx bx-hide" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 sign-in-button">Sign in <i class="bx bx-right-arrow-alt" aria-hidden="true"></i></button>
                </form>
                <p class="account-help">Need an account? Contact your administrator.</p>
                <div class="login-note"><i class="bx bx-box" aria-hidden="true"></i><span>Less time keeping track.<br>More time moving forward.</span></div>
            </div>
            <footer class="login-footer">Stockroom &copy; <?php echo date('Y'); ?> <span>Inventory, made simple.</span></footer>
        </section>
    </main>
    <script src="../assets/js/login.js"></script>
</body>
</html>

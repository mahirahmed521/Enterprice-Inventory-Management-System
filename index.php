<?php
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

$product_count = (int) db_scalar($conn, 'SELECT COUNT(*) FROM vw_storefront_products', 0);
$warehouse_count = (int) db_scalar($conn, 'SELECT COUNT(*) FROM warehouses WHERE is_active=1', 0);
$welcome_name = '';
if (isset($_SESSION['username'])) {
    $welcome_name = trim((string) $_SESSION['username']);
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Welcome | Stockroom Electronics</title>
    <meta name="description" content="Discover dependable electronics and everyday essentials at Stockroom Electronics.">
    <?php include __DIR__ . '/includes/store_head.php'; ?>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body class="welcome-page">
<header class="welcome-header">
    <a class="welcome-brand" href="home.php" aria-label="Stockroom Electronics home"><span class="brand-mark"><i class="bx bx-package"></i></span><span>Stockroom<small>Electronics</small></span></a>
    <nav class="welcome-account" aria-label="Account links">
        <?php if ($welcome_name != '') { ?>
            <span class="welcome-user"><i class="bx bx-user-circle"></i> <?php echo htmlspecialchars($welcome_name); ?></span>
            <a href="my_account.php">My account</a>
        <?php } else { ?>
            <a href="login.php">Sign in</a>
            <a class="welcome-signup" href="register.php">Create account</a>
        <?php } ?>
    </nav>
</header>

<main class="welcome-main">
    <div class="welcome-glow glow-left"></div>
    <div class="welcome-glow glow-right"></div>
    <div class="welcome-grid" aria-hidden="true"></div>
    <div class="welcome-orbit orbit-large" aria-hidden="true"></div>
    <div class="welcome-orbit orbit-small" aria-hidden="true"></div>

    <div class="welcome-content">
        <div class="welcome-kicker"><span class="welcome-live"></span> Your next great find is closer</div>
        <h1>Good things.<br><span>In stock.</span></h1>
        <p>Thoughtful tech, dependable service, and a smoother way to shop. Come on in and find what fits your day.</p>
        <a class="welcome-enter" href="home.php"><span>Enter the store</span><i class="bx bx-right-arrow-alt"></i></a>
        <div class="welcome-stats" aria-label="Store information">
            <div><strong><?php echo number_format($product_count); ?>+</strong><span>products to explore</span></div>
            <i aria-hidden="true"></i>
            <div><strong><?php echo number_format($warehouse_count); ?></strong><span>active warehouses</span></div>
            <i aria-hidden="true"></i>
            <div><strong>Live</strong><span>stock availability</span></div>
        </div>
    </div>

    <div class="welcome-float float-stock" aria-hidden="true"><span class="float-icon"><i class="bx bx-check-shield"></i></span><span><strong>Ready when you are</strong><small>Live warehouse stock</small></span><span class="float-status"></span></div>
    <div class="welcome-float float-delivery" aria-hidden="true"><span class="float-icon"><i class="bx bx-package"></i></span><span><strong>Carefully delivered</strong><small>From our team to yours</small></span></div>
</main>

<footer class="welcome-footer"><span>&copy; <?php echo date('Y'); ?> Stockroom Electronics</span><span>Good tech for everyday life <i class="bx bx-sparkles"></i></span></footer>
</body>
</html>

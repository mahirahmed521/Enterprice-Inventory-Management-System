<?php
require_once '../includes/auth.php';
$pageTitle = 'Purchase orders';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase orders | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/business-overview.css">
</head>
<body class="workspace-page">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <main class="page-wrapper">
        <div class="page-content">
            <?php include '../templates/purchase_orders.html'; ?>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    <?php include '../includes/admin_script.php'; ?>
</body>
</html>

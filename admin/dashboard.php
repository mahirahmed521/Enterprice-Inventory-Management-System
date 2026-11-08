<?php
require_once '../includes/auth.php';
$pageTitle = 'Overview';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Overview | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/business-overview.css">
</head>
<body class="workspace-page">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <main class="page-wrapper">
        <div class="page-content">
            <?php include '../templates/dashboard.html'; ?>
            <?php include '../includes/business_overview.html'; ?>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    <?php include '../includes/admin_script.php'; ?>
</body>
</html>

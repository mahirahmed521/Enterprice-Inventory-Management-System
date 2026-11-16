<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($pageTitle); ?> | Stockroom</title>
<?php include __DIR__ . '/admin_css.php'; ?>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/business-overview.css">
<link rel="stylesheet" href="../assets/css/modules.css">
</head><body class="workspace-page">
<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/topbar.php'; ?>
<main class="page-wrapper"><div class="page-content">
<div class="page-heading"><div><span class="eyebrow"><?php echo htmlspecialchars($section); ?></span>
<h1><?php echo htmlspecialchars($pageTitle); ?><span class="heading-dot">.</span></h1></div></div>
<?php if (isset($_SESSION['notice'])) { ?><p class="alert alert-success" role="status"><?php echo htmlspecialchars($_SESSION['notice']); ?></p><?php unset($_SESSION['notice']);
} ?>
<?php
if (isset($error) == false) {
    $error = '';
}
if ($error) { ?><p class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></p><?php } ?>

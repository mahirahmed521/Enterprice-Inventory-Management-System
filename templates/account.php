<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $pageTitle; ?> | Stockroom</title><link rel="stylesheet" href="../assets/css/icons.css">
    <link rel="stylesheet" href="../assets/css/stockroom.css"></head>
<body><header class="site-header"><div class="bar"><a class="brand" href="../home.php"><i class="bx bx-package" aria-hidden="true"></i>Stockroom<span>.</span></a></div></header>
<main class="account-layout"><div class="account-photo" aria-hidden="true"></div><section class="panel account-panel"><h1><?php echo $pageTitle; ?></h1>
<?php if (isset($_SESSION['notice'])) { ?><p class="notice"><?php echo htmlspecialchars($_SESSION['notice']); ?></p><?php unset($_SESSION['notice']);
} ?>
<?php if ($error) { ?><p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p><?php } ?>
<form class="account-form" method="post">
<label class="field">Email<input type="email" name="email" value="<?php if (isset($email)) { echo htmlspecialchars($email); } ?>" required></label>
<label class="field">Password<input type="password" name="password" required></label>
<button><?php echo $pageTitle; ?></button>
</form>
</section></main></body></html>

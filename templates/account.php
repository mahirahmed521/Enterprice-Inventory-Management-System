<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $pageTitle; ?> | Stockroom</title><link rel="stylesheet" href="../assets/css/icons.css">
    <link rel="stylesheet" href="../assets/css/stockroom.css"></head>
<body><header class="site-header"><div class="bar"><a class="brand" href="../index.php"><i class="bx bx-package" aria-hidden="true"></i>Stockroom<span>.</span></a><nav aria-label="Main navigation"><a href="../shop.php">Shop</a></nav></div></header>
<main class="account-layout"><div class="account-photo" aria-hidden="true"></div><section class="panel account-panel"><h1><?php echo $pageTitle; ?></h1>
<?php if (isset($_SESSION['notice'])) { ?><p class="notice"><?php echo htmlspecialchars($_SESSION['notice']); ?></p><?php unset($_SESSION['notice']);
} ?>
<?php if ($error) { ?><p class="error" role="alert"><?php echo htmlspecialchars($error); ?></p><?php } ?>
<form class="account-form" method="post">
<?php if ($pageTitle == 'Create an account') { ?><label class="field">Name<input name="username" maxlength="50" value="<?php if (isset($username)) { echo htmlspecialchars($username); } ?>" required></label><?php } ?>
<label class="field">Email<input type="email" name="email" value="<?php if (isset($email)) { echo htmlspecialchars($email); } ?>" required></label>
<label class="field">Password<input type="password" name="password" required></label>
<?php if ($pageTitle == 'Create an account') { ?><label class="field">Account type<select name="role"><option>CUSTOMER</option><option>STAFF</option><option>MANAGER</option><option>ADMIN</option></select></label>
<label class="field">Customer delivery address<textarea name="address"><?php if (isset($address)) { echo htmlspecialchars($address); } ?></textarea></label><?php } ?>
<button><?php echo $pageTitle; ?></button>
</form><?php if ($pageTitle == 'Sign in') { ?><p><a href="signup.php">Create an account</a></p><?php } else { ?><p><a href="index.php">Sign in</a></p><?php } ?>
</section></main></body></html>

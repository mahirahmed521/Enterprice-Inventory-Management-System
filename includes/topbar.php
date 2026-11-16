<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn menu-toggle" type="button" id="menu-toggle" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false"><i class="bx bx-menu" aria-hidden="true"></i></button>
        <span class="breadcrumb-label">Workspace / <strong><?php echo htmlspecialchars($pageTitle); ?></strong></span>
    </div>
    <div class="topbar-account">
        <span class="topbar-date"><?php echo date('D, d M Y'); ?></span>
        <a class="account-link" href="dashboard.php">
            <span class="avatar"><?php echo htmlspecialchars(substr($account['username'], 0, 1)); ?></span>
            <div><strong><?php echo htmlspecialchars($account['username']); ?></strong><small><?php echo htmlspecialchars($account['role']); ?></small></div>
        </a>
        <a href="logout.php" class="topbar-logout" aria-label="Log out"><i class="bx bx-log-out" aria-hidden="true"></i></a>
    </div>
</header>

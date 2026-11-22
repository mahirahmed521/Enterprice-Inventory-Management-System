<?php
require_once __DIR__ . '/profile_data.php';
$topbar_profile = profile_header_info($conn, (int) $account['user_id']);
$topbar_name = $account['username'];
if (trim($topbar_profile['name']) != '') {
    $topbar_name = $topbar_profile['name'];
}
$topbar_avatar = profile_avatar_url($topbar_profile['avatar'], '../');
?>
<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn menu-toggle" type="button" id="menu-toggle" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false"><i class="bx bx-menu" aria-hidden="true"></i></button>
        <span class="breadcrumb-label">Workspace / <strong><?php echo htmlspecialchars($pageTitle); ?></strong></span>
    </div>
    <div class="topbar-account">
        <span class="topbar-date"><?php echo date('D, d M Y'); ?></span>
        <a class="account-link" href="profile.php" title="My profile">
            <?php if ($topbar_avatar != '') { ?>
            <img class="avatar" style="object-fit:cover" src="<?php echo htmlspecialchars($topbar_avatar); ?>" alt="">
            <?php } else { ?>
            <span class="avatar"><?php echo htmlspecialchars(substr($topbar_name, 0, 1)); ?></span>
            <?php } ?>
            <div><strong><?php echo htmlspecialchars($topbar_name); ?></strong><small><?php echo htmlspecialchars($account['role']); ?></small></div>
        </a>
        <a href="logout.php" class="topbar-logout" aria-label="Log out"><i class="bx bx-log-out" aria-hidden="true"></i></a>
    </div>
</header>

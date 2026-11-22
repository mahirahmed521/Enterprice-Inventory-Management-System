<?php
// Profile layout for the staff portal. The three role pages (admin_profile.php, manager_profile.php,
// staff_profile.php) all use it; the fields shown come from profile_fields($profile_role).
$role_titles = array('ADMIN' => 'Administrator', 'MANAGER' => 'Manager', 'STAFF' => 'Warehouse staff');
$role_blurbs = array(
    'ADMIN' => 'You maintain user accounts, activate or disable them and review inventory valuation.',
    'MANAGER' => 'You manage products, categories, customers and suppliers, place purchase orders, confirm sales and review reorder alerts.',
    'STAFF' => 'You receive deliveries, ship confirmed sales, and adjust, transfer or relocate stock.'
);
$role_title = $profile_role;
if (isset($role_titles[$profile_role])) {
    $role_title = $role_titles[$profile_role];
}
$role_blurb = '';
if (isset($role_blurbs[$profile_role])) {
    $role_blurb = $role_blurbs[$profile_role];
}
$display_name = $profile_user['username'];
if (isset($profile['full_name'])) {
    if (trim((string) $profile['full_name']) != '') {
        $display_name = $profile['full_name'];
    }
}
$avatar_file = '';
if (isset($profile['avatar'])) {
    $avatar_file = profile_avatar_url($profile['avatar'], '../');
}
$member_since = '';
if ($profile_user['created_at'] != '' && $profile_user['created_at'] !== null) {
    $member_since = date('d M Y', strtotime($profile_user['created_at']));
}
$detail_fields = profile_fields($profile_role);
$notification_options = profile_notification_options($profile_role);
?>
<section class="surface records-surface">
    <div class="profile-hero">
        <?php if ($avatar_file != '') { ?>
            <img class="profile-avatar" src="<?php echo htmlspecialchars($avatar_file); ?>" alt="">
        <?php } else { ?>
            <span class="profile-avatar" aria-hidden="true"><?php echo htmlspecialchars(substr($display_name, 0, 1)); ?></span>
        <?php } ?>
        <div>
            <h2 class="profile-name"><?php echo htmlspecialchars($display_name); ?></h2>
            <span class="role-chip"><?php echo htmlspecialchars($role_title); ?></span>
            <div class="profile-meta">
                <span><i class="bx bx-envelope" aria-hidden="true"></i> <?php echo htmlspecialchars($profile_user['email']); ?></span>
                <span><i class="bx bx-user" aria-hidden="true"></i> @<?php echo htmlspecialchars($profile_user['username']); ?></span>
                <?php if ($member_since != '') { ?><span><i class="bx bx-calendar" aria-hidden="true"></i> Member since <?php echo htmlspecialchars($member_since); ?></span><?php } ?>
            </div>
            <p class="profile-blurb"><?php echo htmlspecialchars($role_blurb); ?></p>
        </div>
    </div>
</section>

<div class="profile-layout">
    <div class="profile-column">
        <section class="surface records-surface">
            <div class="section-heading"><h2>Profile picture</h2></div>
            <form method="post" enctype="multipart/form-data" class="avatar-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="avatar">
                <input type="file" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Choose a profile picture" required>
                <button class="btn btn-success" type="submit">Upload</button>
            </form>
            <p class="profile-help">JPG, PNG, WEBP or GIF, up to 2 MB.</p>
            <?php if ($avatar_file != '') { ?>
            <form method="post" class="avatar-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="remove_avatar">
                <button class="btn btn-sm btn-link text-danger px-0" type="submit">Remove current picture</button>
            </form>
            <?php } ?>
        </section>

        <section class="surface records-surface">
            <div class="section-heading"><h2>Sign-in details</h2></div>
            <form method="post" class="module-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="account">
                <div class="fields">
                    <label class="field">Username
                        <input name="username" type="text" maxlength="50" value="<?php echo htmlspecialchars(profile_field_value('username', array('username' => $profile_user['username']), $profile_keep)); ?>" required>
                    </label>
                    <label class="field">Email
                        <input name="email" type="email" maxlength="100" value="<?php echo htmlspecialchars(profile_field_value('email', array('email' => $profile_user['email']), $profile_keep)); ?>" required>
                    </label>
                </div>
                <div class="actions"><button class="btn btn-success" type="submit">Save sign-in details</button></div>
            </form>
        </section>

        <section class="surface records-surface">
            <div class="section-heading"><h2>Change password</h2></div>
            <form method="post" class="module-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="password">
                <div class="fields">
                    <label class="field wide">Current password
                        <input name="current_password" type="password" autocomplete="current-password" required>
                    </label>
                    <label class="field">New password
                        <input name="new_password" type="password" autocomplete="new-password" minlength="6" required>
                    </label>
                    <label class="field">Confirm new password
                        <input name="confirm_password" type="password" autocomplete="new-password" minlength="6" required>
                    </label>
                </div>
                <div class="actions"><button class="btn btn-success" type="submit">Change password</button></div>
            </form>
        </section>
    </div>

    <div class="profile-column">
        <section class="surface records-surface">
            <div class="section-heading"><h2><?php echo htmlspecialchars($role_title); ?> details</h2></div>
            <form method="post" class="module-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="details">
                <div class="fields">
                    <?php
                    $f = 0;
                    while (isset($detail_fields[$f])) {
                        $field_key = $detail_fields[$f]['key'];
                        profile_render_field($detail_fields[$f], profile_field_value($field_key, $profile_values, $profile_keep), 'portal', $profile_warehouses);
                        $f = $f + 1;
                    }
                    ?>
                </div>
                <div class="actions"><button class="btn btn-success" type="submit">Save details</button></div>
            </form>
        </section>

        <?php if ($profile_role == 'MANAGER') { ?>
        <section class="surface records-surface">
            <div class="section-heading"><h2>Warehouses I manage</h2></div>
            <?php if (count($managed_warehouses) == 0) { ?>
                <p class="empty-state">No warehouse lists you as its manager yet. An administrator can assign one on the Warehouses page.</p>
            <?php } else { ?>
                <ul class="profile-list">
                <?php $w = 0; while (isset($managed_warehouses[$w])) { ?>
                    <li><strong><?php echo htmlspecialchars($managed_warehouses[$w]['warehouse_name']); ?></strong>
                        <span class="profile-help"><?php echo htmlspecialchars($managed_warehouses[$w]['warehouse_code']); ?> &middot; <?php echo htmlspecialchars((string) $managed_warehouses[$w]['location_address']); ?></span></li>
                <?php $w = $w + 1; } ?>
                </ul>
            <?php } ?>
        </section>
        <?php } ?>

        <section class="surface records-surface">
            <div class="section-heading"><h2>Notifications</h2></div>
            <form method="post" class="module-form">
                <?php profile_csrf_field(); ?>
                <input type="hidden" name="profile_action" value="notifications">
                <?php profile_render_notifications($profile_role, $profile, 'portal'); ?>
                <div class="actions"><button class="btn btn-success" type="submit">Save notification choices</button></div>
            </form>
        </section>
    </div>
</div>

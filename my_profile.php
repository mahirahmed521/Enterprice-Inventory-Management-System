<?php
// Customer profile page: picture, personal and delivery details, sign-in details, password and
// notification choices. Team accounts have their own profile pages in admin/.
require_once __DIR__ . '/includes/check_fields.php';
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';

if (isset($_SESSION['user_id']) == false) {
    $_SESSION['redirect_after_login'] = 'my_profile.php';
    header('Location: login.php');
    exit;
}

// Team members have their own profile pages in the portal.
$session_role = '';
if (isset($_SESSION['role'])) {
    $session_role = $_SESSION['role'];
}
if ($session_role == 'ADMIN' || $session_role == 'MANAGER' || $session_role == 'STAFF') {
    header('Location: admin/profile.php');
    exit;
}

include __DIR__ . '/includes/current_customer.php';

$profile_role = 'CUSTOMER';
$user_id = (int) $_SESSION['user_id'];
$profile_notice_key = 'store_notice';
if ($customer_id > 0) {
    include __DIR__ . '/includes/profile_actions.php';
}

if ($customer_id > 0) {
    $detail_fields = profile_fields('CUSTOMER');
    $display_name = $profile_user['username'];
    if (isset($customer['customer_name'])) {
        if (trim((string) $customer['customer_name']) != '') {
            $display_name = $customer['customer_name'];
        }
    }
    $avatar_file = '';
    if (isset($profile['avatar'])) {
        $avatar_file = profile_avatar_url($profile['avatar']);
    }
    $member_since = '';
    if ($profile_user['created_at'] != '' && $profile_user['created_at'] !== null) {
        $member_since = date('d M Y', strtotime($profile_user['created_at']));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>My Profile | Stockroom Electronics</title>
    <?php include __DIR__ . '/includes/store_head.php'; ?>
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body class="storefront">
<?php include __DIR__ . '/includes/store_header.php'; ?>
<main class="store-main">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h1 class="h3 mb-0">My Profile</h1>
            <a class="btn btn-outline-orange btn-sm" href="my_account.php">&larr; Back to My Account</a>
        </div>

        <?php if ($customer_id == 0) { ?>
            <div class="alert alert-warning">We could not find the delivery record for this account. Please contact the store team.</div>
            <a class="btn btn-outline-orange" href="logout.php">Sign out</a>
        <?php } else { ?>

        <?php if ($error != '') { ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card mb-4"><div class="card-body text-center">
                    <?php if ($avatar_file != '') { ?>
                        <img class="profile-avatar mb-3" src="<?php echo htmlspecialchars($avatar_file); ?>" alt="">
                    <?php } else { ?>
                        <span class="profile-avatar mb-3" aria-hidden="true"><?php echo htmlspecialchars(substr($display_name, 0, 1)); ?></span>
                    <?php } ?>
                    <h2 class="h5 mb-1"><?php echo htmlspecialchars($display_name); ?></h2>
                    <div class="text-muted small">@<?php echo htmlspecialchars($profile_user['username']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($profile_user['email']); ?></div>
                    <?php if ($member_since != '') { ?><div class="text-muted small mt-1">Customer since <?php echo htmlspecialchars($member_since); ?></div><?php } ?>

                    <form method="post" enctype="multipart/form-data" class="mt-3 text-start">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="avatar">
                        <label class="form-label small" for="avatar_file">Change profile picture</label>
                        <input class="form-control form-control-sm mb-2" type="file" id="avatar_file" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                        <button class="btn btn-orange btn-sm w-100" type="submit">Upload picture</button>
                        <div class="form-text">JPG, PNG, WEBP or GIF, up to 2 MB.</div>
                    </form>
                    <?php if ($avatar_file != '') { ?>
                    <form method="post" class="mt-2">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="remove_avatar">
                        <button class="btn btn-link btn-sm text-danger" type="submit">Remove picture</button>
                    </form>
                    <?php } ?>
                </div></div>

                <div class="card mb-4"><div class="card-body">
                    <h2 class="h5 mb-3">Sign-in details</h2>
                    <form method="post">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="account">
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" id="username" name="username" maxlength="50" value="<?php echo htmlspecialchars(profile_field_value('username', array('username' => $profile_user['username']), $profile_keep)); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" maxlength="100" value="<?php echo htmlspecialchars(profile_field_value('email', array('email' => $profile_user['email']), $profile_keep)); ?>" required>
                        </div>
                        <button class="btn btn-orange w-100" type="submit">Save sign-in details</button>
                    </form>
                </div></div>

                <div class="card"><div class="card-body">
                    <h2 class="h5 mb-3">Change password</h2>
                    <form method="post">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="password">
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="new_password">New password</label>
                            <input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="confirm_password">Confirm new password</label>
                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="6" required>
                        </div>
                        <button class="btn btn-orange w-100" type="submit">Change password</button>
                    </form>
                </div></div>
            </div>

            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <h2 class="h5 mb-3">Personal and delivery details</h2>
                    <form method="post">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="details">
                        <div class="row">
                            <?php
                            $f = 0;
                            while (isset($detail_fields[$f])) {
                                $field_key = $detail_fields[$f]['key'];
                                profile_render_field($detail_fields[$f], profile_field_value($field_key, $profile_values, $profile_keep), 'store', $profile_warehouses);
                                $f = $f + 1;
                            }
                            ?>
                        </div>
                        <button class="btn btn-orange" type="submit">Save details</button>
                    </form>
                </div></div>

                <div class="card"><div class="card-body">
                    <h2 class="h5 mb-3">Notifications</h2>
                    <form method="post">
                        <?php profile_csrf_field(); ?>
                        <input type="hidden" name="profile_action" value="notifications">
                        <?php profile_render_notifications('CUSTOMER', $profile, 'store'); ?>
                        <button class="btn btn-orange mt-2" type="submit">Save notification choices</button>
                    </form>
                </div></div>
            </div>
        </div>
        <?php } ?>
    </div>
</main>
<?php include __DIR__ . '/includes/store_footer.php'; ?>
</body>
</html>

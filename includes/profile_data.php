<?php
// Everything the four profile pages share: the user_profiles table, the fields each role can
// edit, the save routines, picture upload, password change and the form safety token.
//
//   Customer  my_profile.php
//   Staff     admin/staff_profile.php
//   Manager   admin/manager_profile.php
//   Admin     admin/admin_profile.php
//
// Sign-in details (username, email, password) stay in the users table. Delivery name, phone and
// delivery address of a customer stay in the customers table, so the store keeps working as before.
// Everything else a person customizes is kept in user_profiles (one row per user).

// Creates the user_profiles table the first time a profile page is opened.
// dabatases/update_user_profiles.sql holds the same statement if you prefer to import it.
function profile_ensure_table($conn)
{
    if (isset($GLOBALS['profile_table_ready'])) {
        return $GLOBALS['profile_table_ready'];
    }
    $ready = false;
    $found = mysqli_query($conn, "SHOW TABLES LIKE 'user_profiles'");
    if ($found) {
        if (mysqli_num_rows($found) > 0) {
            $ready = true;
        }
    }
    if ($ready == false) {
        $sql = "CREATE TABLE IF NOT EXISTS user_profiles (
            user_id int NOT NULL,
            full_name varchar(150) DEFAULT NULL,
            phone varchar(30) DEFAULT NULL,
            alt_phone varchar(30) DEFAULT NULL,
            bio varchar(500) DEFAULT NULL,
            avatar varchar(255) DEFAULT NULL,
            job_title varchar(100) DEFAULT NULL,
            department varchar(100) DEFAULT NULL,
            office_location varchar(150) DEFAULT NULL,
            employee_code varchar(50) DEFAULT NULL,
            shift varchar(20) DEFAULT NULL,
            warehouse_id int DEFAULT NULL,
            emergency_name varchar(150) DEFAULT NULL,
            emergency_phone varchar(30) DEFAULT NULL,
            billing_address varchar(500) DEFAULT NULL,
            preferred_payment varchar(30) DEFAULT NULL,
            date_of_birth date DEFAULT NULL,
            notify_orders tinyint(1) NOT NULL DEFAULT 1,
            notify_stock tinyint(1) NOT NULL DEFAULT 1,
            notify_promos tinyint(1) NOT NULL DEFAULT 0,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
            CONSTRAINT fk_profile_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (mysqli_query($conn, $sql)) {
            $ready = true;
        }
    }
    $GLOBALS['profile_table_ready'] = $ready;
    return $ready;
}

// The profile row of one user. A blank row is created the first time.
function profile_load($conn, $user_id)
{
    $user_id = (int) $user_id;
    if (profile_ensure_table($conn) == false) {
        return array();
    }
    $rows = db_rows($conn, "SELECT * FROM user_profiles WHERE user_id=$user_id");
    if (count($rows) == 0) {
        mysqli_query($conn, "INSERT IGNORE INTO user_profiles (user_id) VALUES ($user_id)");
        $rows = db_rows($conn, "SELECT * FROM user_profiles WHERE user_id=$user_id");
    }
    if (count($rows) == 0) {
        return array();
    }
    return $rows[0];
}

// Name and picture for the top bar. Does not create a row.
function profile_header_info($conn, $user_id)
{
    $info = array('name' => '', 'avatar' => '');
    if (profile_ensure_table($conn) == false) {
        return $info;
    }
    $rows = db_rows($conn, 'SELECT full_name, avatar FROM user_profiles WHERE user_id=' . (int) $user_id);
    if (count($rows) > 0) {
        if ($rows[0]['full_name'] !== null) {
            $info['name'] = $rows[0]['full_name'];
        }
        if ($rows[0]['avatar'] !== null) {
            $info['avatar'] = $rows[0]['avatar'];
        }
    }
    return $info;
}

// Web path of an uploaded profile picture, or '' when there is none (the page then shows the initial).
// $prefix is '' for storefront pages and '../' for admin pages.
function profile_avatar_url($stored, $prefix = '')
{
    $stored = trim((string) $stored);
    if ($stored == '') {
        return '';
    }
    if (strpos($stored, '/') !== false || strpos($stored, '\\') !== false || strpos($stored, '..') !== false) {
        return '';
    }
    if (file_exists(__DIR__ . '/../uploads/avatars/' . $stored) == false) {
        return '';
    }
    return $prefix . 'uploads/avatars/' . rawurlencode($stored);
}

// ---------------------------------------------------------------------------------------------
// What each role can customize
// ---------------------------------------------------------------------------------------------

// Fields of the "details" form for a role. 'table' is customers for the three fields that belong to the
// customer record; everything else is stored in user_profiles. 'wide' fields use the full row.
function profile_fields($role)
{
    $fields = array();
    if ($role == 'CUSTOMER') {
        $fields[] = array('key' => 'customer_name', 'label' => 'Delivery name', 'type' => 'text', 'max' => 150, 'required' => true, 'table' => 'customers');
        $fields[] = array('key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'max' => 30, 'table' => 'customers');
        $fields[] = array('key' => 'alt_phone', 'label' => 'Alternate phone', 'type' => 'tel', 'max' => 30);
        $fields[] = array('key' => 'date_of_birth', 'label' => 'Date of birth', 'type' => 'date', 'max' => 10);
        $fields[] = array('key' => 'preferred_payment', 'label' => 'Preferred payment method', 'type' => 'payment', 'max' => 30);
        $fields[] = array('key' => 'shipping_address', 'label' => 'Delivery address', 'type' => 'textarea', 'max' => 500, 'table' => 'customers', 'wide' => true);
        $fields[] = array('key' => 'billing_address', 'label' => 'Billing address (if different)', 'type' => 'textarea', 'max' => 500, 'wide' => true);
    } elseif ($role == 'STAFF') {
        $fields[] = array('key' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'max' => 30);
        $fields[] = array('key' => 'employee_code', 'label' => 'Employee ID', 'type' => 'text', 'max' => 50);
        $fields[] = array('key' => 'shift', 'label' => 'Shift', 'type' => 'shift', 'max' => 20);
        $fields[] = array('key' => 'warehouse_id', 'label' => 'Usual warehouse', 'type' => 'warehouse', 'max' => 10);
        $fields[] = array('key' => 'emergency_name', 'label' => 'Emergency contact name', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'emergency_phone', 'label' => 'Emergency contact phone', 'type' => 'tel', 'max' => 30);
        $fields[] = array('key' => 'bio', 'label' => 'About me', 'type' => 'textarea', 'max' => 500, 'wide' => true);
    } elseif ($role == 'MANAGER') {
        $fields[] = array('key' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'max' => 30);
        $fields[] = array('key' => 'job_title', 'label' => 'Job title', 'type' => 'text', 'max' => 100);
        $fields[] = array('key' => 'department', 'label' => 'Department', 'type' => 'text', 'max' => 100);
        $fields[] = array('key' => 'office_location', 'label' => 'Office location', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'bio', 'label' => 'About me', 'type' => 'textarea', 'max' => 500, 'wide' => true);
    } elseif ($role == 'ADMIN') {
        $fields[] = array('key' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'max' => 30);
        $fields[] = array('key' => 'job_title', 'label' => 'Job title', 'type' => 'text', 'max' => 100);
        $fields[] = array('key' => 'office_location', 'label' => 'Office location', 'type' => 'text', 'max' => 150);
        $fields[] = array('key' => 'bio', 'label' => 'About me', 'type' => 'textarea', 'max' => 500, 'wide' => true);
    }
    return $fields;
}

// The notification choices of a role: column name => label.
function profile_notification_options($role)
{
    $options = array();
    if ($role == 'CUSTOMER') {
        $options['notify_orders'] = 'Order updates (confirmed, shipped, delivered)';
        $options['notify_promos'] = 'Deals and promotions';
    } elseif ($role == 'STAFF') {
        $options['notify_orders'] = 'Orders that are ready to ship';
        $options['notify_stock'] = 'Low-stock alerts';
    } elseif ($role == 'MANAGER') {
        $options['notify_orders'] = 'Orders waiting for confirmation';
        $options['notify_stock'] = 'Reorder alerts';
    } elseif ($role == 'ADMIN') {
        $options['notify_orders'] = 'Order activity';
        $options['notify_stock'] = 'Stock and reorder alerts';
    }
    return $options;
}

function profile_shift_choices()
{
    return array('Morning', 'Evening', 'Night');
}

function profile_payment_choices()
{
    return array('Cash on Delivery', 'bKash', 'Nagad', 'Card', 'Bank');
}

// The current value of every field in the details form, keyed by field name.
function profile_values($role, $profile, $customer)
{
    $values = array();
    $fields = profile_fields($role);
    $f = 0;
    while (isset($fields[$f])) {
        $key = $fields[$f]['key'];
        $text = '';
        if (isset($fields[$f]['table']) && $fields[$f]['table'] == 'customers') {
            if (is_array($customer)) {
                if (isset($customer[$key]) && $customer[$key] !== null) {
                    $text = $customer[$key];
                }
            }
        } else {
            if (isset($profile[$key]) && $profile[$key] !== null) {
                $text = $profile[$key];
            }
        }
        $values[$key] = (string) $text;
        $f = $f + 1;
    }
    return $values;
}

// After a rejected form the page shows what the person typed, not what is saved.
function profile_field_value($key, $values, $keep_post)
{
    if ($keep_post) {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
    }
    if (isset($values[$key])) {
        return $values[$key];
    }
    return '';
}

// ---------------------------------------------------------------------------------------------
// Drawing the form fields. $style is 'portal' (staff portal layout) or 'store' (Bootstrap storefront).
// ---------------------------------------------------------------------------------------------

function profile_select_choices($def, $warehouses)
{
    $choices = array('' => 'Not set');
    if ($def['type'] == 'shift') {
        $list = profile_shift_choices();
        $c = 0;
        while (isset($list[$c])) {
            $choices[$list[$c]] = $list[$c];
            $c = $c + 1;
        }
    } elseif ($def['type'] == 'payment') {
        $list = profile_payment_choices();
        $c = 0;
        while (isset($list[$c])) {
            $choices[$list[$c]] = $list[$c];
            $c = $c + 1;
        }
    } elseif ($def['type'] == 'warehouse') {
        $c = 0;
        while (isset($warehouses[$c])) {
            $label = $warehouses[$c]['warehouse_name'];
            if ($warehouses[$c]['is_active'] == 0) {
                $label = $label . ' (inactive)';
            }
            $choices[(string) $warehouses[$c]['warehouse_id']] = $label;
            $c = $c + 1;
        }
    }
    return $choices;
}

function profile_render_field($def, $value, $style, $warehouses)
{
    $key = $def['key'];
    $type = $def['type'];
    $label = htmlspecialchars($def['label']);
    $required = '';
    if (isset($def['required'])) {
        if ($def['required']) {
            $required = ' required';
        }
    }
    $wide = false;
    if (isset($def['wide'])) {
        if ($def['wide']) {
            $wide = true;
        }
    }
    $id = 'f_' . $key;
    $name_attr = 'name="' . $key . '" id="' . $id . '"';
    $max_attr = ' maxlength="' . (int) $def['max'] . '"';
    $safe_value = htmlspecialchars((string) $value);

    // Wrapper and class names for each layout
    $input_class = '';
    $select_class = '';
    if ($style == 'store') {
        $col = 'col-md-6';
        if ($wide) {
            $col = 'col-12';
        }
        echo '<div class="' . $col . ' mb-3"><label class="form-label" for="' . $id . '">' . $label . '</label>';
        $input_class = ' class="form-control"';
        $select_class = ' class="form-select"';
    } else {
        $field_class = 'field';
        if ($wide) {
            $field_class = 'field wide';
        }
        echo '<label class="' . $field_class . '">' . $label;
    }

    if ($type == 'textarea') {
        echo '<textarea ' . $name_attr . $input_class . ' rows="3"' . $max_attr . $required . '>' . $safe_value . '</textarea>';
    } elseif ($type == 'shift' || $type == 'payment' || $type == 'warehouse') {
        $choices = profile_select_choices($def, $warehouses);
        $values = array_keys($choices);
        echo '<select ' . $name_attr . $select_class . '>';
        $c = 0;
        while (isset($values[$c])) {
            $option_value = (string) $values[$c];
            $selected = '';
            if ($option_value === (string) $value) {
                $selected = ' selected';
            }
            echo '<option value="' . htmlspecialchars($option_value) . '"' . $selected . '>' . htmlspecialchars($choices[$values[$c]]) . '</option>';
            $c = $c + 1;
        }
        echo '</select>';
    } elseif ($type == 'date') {
        echo '<input type="date" ' . $name_attr . $input_class . ' value="' . $safe_value . '" min="1900-01-01" max="' . date('Y-m-d') . '">';
    } elseif ($type == 'tel') {
        echo '<input type="tel" ' . $name_attr . $input_class . ' value="' . $safe_value . '"' . $max_attr . $required . '>';
    } else {
        echo '<input type="text" ' . $name_attr . $input_class . ' value="' . $safe_value . '"' . $max_attr . $required . '>';
    }

    if ($style == 'store') {
        echo '</div>';
    } else {
        echo '</label>';
    }
}

// The notification checkboxes of a role.
function profile_render_notifications($role, $profile, $style)
{
    $options = profile_notification_options($role);
    $keys = array_keys($options);
    $n = 0;
    while (isset($keys[$n])) {
        $key = $keys[$n];
        $checked = '';
        if (isset($profile[$key])) {
            if ((int) $profile[$key] == 1) {
                $checked = ' checked';
            }
        }
        if ($style == 'store') {
            echo '<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="' . $key . '" id="n_' . $key . '" value="1"' . $checked . '>';
            echo '<label class="form-check-label" for="n_' . $key . '">' . htmlspecialchars($options[$key]) . '</label></div>';
        } else {
            echo '<label class="profile-check"><input type="checkbox" name="' . $key . '" value="1"' . $checked . '><span>' . htmlspecialchars($options[$key]) . '</span></label>';
        }
        $n = $n + 1;
    }
}

// ---------------------------------------------------------------------------------------------
// Form safety token (the rest of the project has no token; the profile forms change passwords and
// email addresses, so they get one)
// ---------------------------------------------------------------------------------------------

function profile_csrf_token()
{
    if (isset($_SESSION['profile_csrf']) == false) {
        $_SESSION['profile_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['profile_csrf'];
}

function profile_csrf_field()
{
    echo '<input type="hidden" name="csrf" value="' . profile_csrf_token() . '">';
}

function profile_csrf_ok()
{
    if (isset($_POST['csrf']) == false) {
        return false;
    }
    if (isset($_SESSION['profile_csrf']) == false) {
        return false;
    }
    return hash_equals($_SESSION['profile_csrf'], $_POST['csrf']);
}

// ---------------------------------------------------------------------------------------------
// Saving
// ---------------------------------------------------------------------------------------------

// UPDATE table SET column=?, ... WHERE id_column=?  Column names always come from the field lists above,
// never from the request. Returns true when the statement ran.
function profile_run_update($conn, $table, $id_column, $id, $cols)
{
    $names = array_keys($cols);
    if (count($names) == 0) {
        return true;
    }
    $set = '';
    $types = '';
    $params = array();
    $n = 0;
    while (isset($names[$n])) {
        $name = $names[$n];
        if ($n > 0) {
            $set = $set . ', ';
        }
        $set = $set . $name . '=?';
        $type = 's';
        if ($name == 'warehouse_id') {
            $type = 'i';
        }
        if (substr($name, 0, 7) == 'notify_') {
            $type = 'i';
        }
        $types = $types . $type;
        $params[] = $cols[$name];
        $n = $n + 1;
    }
    $types = $types . 'i';
    $params[] = $id;
    $statement = mysqli_prepare($conn, 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $id_column . '=?');
    if ($statement == false) {
        return false;
    }
    mysqli_stmt_bind_param($statement, $types, ...$params);
    $ok = mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
    return $ok;
}

// '' when the text is acceptable for this kind of field, otherwise what is wrong with it.
function profile_check_value($type, $text, $warehouses)
{
    if ($type == 'tel') {
        if (preg_match('/^[0-9+\-() ]+$/', $text) == false) {
            return 'use digits, spaces and + - ( ) only.';
        }
    } elseif ($type == 'date') {
        $valid = false;
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $text, $parts)) {
            if (checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                if ((int) $parts[1] >= 1900 && $text <= date('Y-m-d')) {
                    $valid = true;
                }
            }
        }
        if ($valid == false) {
            return 'enter a valid date.';
        }
    } elseif ($type == 'shift') {
        if (in_array($text, profile_shift_choices()) == false) {
            return 'choose one from the list.';
        }
    } elseif ($type == 'payment') {
        if (in_array($text, profile_payment_choices()) == false) {
            return 'choose one from the list.';
        }
    } elseif ($type == 'warehouse') {
        $known = false;
        $w = 0;
        while (isset($warehouses[$w])) {
            if ((string) $warehouses[$w]['warehouse_id'] === $text) {
                $known = true;
            }
            $w = $w + 1;
        }
        if ($known == false) {
            return 'choose a warehouse from the list.';
        }
    }
    return '';
}

// Saves the details form of the signed-in person. Returns '' on success, otherwise an error message.
function profile_save_details($conn, $role, $user_id, $customer_id)
{
    $fields = profile_fields($role);
    if (count($fields) == 0) {
        return 'This account type has no profile details.';
    }
    if ($role == 'CUSTOMER' && $customer_id < 1) {
        return 'We could not find the delivery record for this account.';
    }
    $warehouses = db_rows($conn, 'SELECT warehouse_id, warehouse_name, is_active FROM warehouses');
    $profile_cols = array();
    $customer_cols = array();
    $f = 0;
    while (isset($fields[$f])) {
        $def = $fields[$f];
        $key = $def['key'];
        $text = '';
        if (isset($_POST[$key])) {
            $text = trim($_POST[$key]);
        }
        if (strlen($text) > $def['max']) {
            return $def['label'] . ' is too long.';
        }
        if (isset($def['required']) && $def['required'] && $text === '') {
            return 'Enter your ' . strtolower($def['label']) . '.';
        }
        if ($text !== '') {
            $problem = profile_check_value($def['type'], $text, $warehouses);
            if ($problem != '') {
                return $def['label'] . ': ' . $problem;
            }
        }
        if (isset($def['table']) && $def['table'] == 'customers') {
            $customer_cols[$key] = $text;
        } elseif ($text === '') {
            $profile_cols[$key] = null;
        } elseif ($def['type'] == 'warehouse') {
            $profile_cols[$key] = (int) $text;
        } else {
            $profile_cols[$key] = $text;
        }
        $f = $f + 1;
    }

    profile_load($conn, $user_id);
    mysqli_query($conn, 'START TRANSACTION');
    $saved = profile_run_update($conn, 'user_profiles', 'user_id', $user_id, $profile_cols);
    if ($saved) {
        $saved = profile_run_update($conn, 'customers', 'customer_id', $customer_id, $customer_cols);
    }
    if ($saved == false) {
        mysqli_query($conn, 'ROLLBACK');
        return 'Could not save your details. Please try again.';
    }
    mysqli_query($conn, 'COMMIT');
    return '';
}

// Another account already using this username or email?
function profile_value_taken($conn, $column, $text, $user_id)
{
    $sql = 'SELECT user_id FROM users WHERE username=? AND user_id<>?';
    if ($column == 'email') {
        $sql = 'SELECT user_id FROM users WHERE email=? AND user_id<>?';
    }
    $statement = mysqli_prepare($conn, $sql);
    if ($statement == false) {
        return true;
    }
    mysqli_stmt_bind_param($statement, 'si', $text, $user_id);
    mysqli_stmt_execute($statement);
    mysqli_stmt_store_result($statement);
    $taken = mysqli_stmt_num_rows($statement) > 0;
    mysqli_stmt_close($statement);
    return $taken;
}

// Username and sign-in email.
function profile_save_account($conn, $role, $user_id, $customer_id)
{
    $username = '';
    $email = '';
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }
    if ($username === '') {
        return 'Enter a username.';
    }
    if (strlen($username) > 50) {
        return 'Use a username with 50 characters or fewer.';
    }
    if (strlen($email) > 100) {
        return 'Use an email address with 100 characters or fewer.';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        return 'Enter a valid email address.';
    }
    if (profile_value_taken($conn, 'username', $username, $user_id)) {
        return 'That username is already taken.';
    }
    if (profile_value_taken($conn, 'email', $email, $user_id)) {
        return 'That email already belongs to another account.';
    }
    mysqli_query($conn, 'START TRANSACTION');
    $saved = profile_run_update($conn, 'users', 'user_id', $user_id, array('username' => $username, 'email' => $email));
    if ($saved && $role == 'CUSTOMER' && $customer_id > 0) {
        // The customer record keeps its own copy of the email address
        $saved = profile_run_update($conn, 'customers', 'customer_id', $customer_id, array('email' => $email));
    }
    if ($saved == false) {
        mysqli_query($conn, 'ROLLBACK');
        return 'Could not save your account details. Please try again.';
    }
    mysqli_query($conn, 'COMMIT');
    $_SESSION['username'] = $username;
    return '';
}

function profile_save_notifications($conn, $role, $user_id)
{
    $options = profile_notification_options($role);
    $keys = array_keys($options);
    $cols = array();
    $n = 0;
    while (isset($keys[$n])) {
        $cols[$keys[$n]] = 0;
        if (isset($_POST[$keys[$n]])) {
            if ($_POST[$keys[$n]] == '1') {
                $cols[$keys[$n]] = 1;
            }
        }
        $n = $n + 1;
    }
    profile_load($conn, $user_id);
    if (profile_run_update($conn, 'user_profiles', 'user_id', $user_id, $cols) == false) {
        return 'Could not save your notification choices.';
    }
    return '';
}

function profile_change_password($conn, $user_id)
{
    $current = '';
    $new = '';
    $confirm = '';
    if (isset($_POST['current_password'])) {
        $current = $_POST['current_password'];
    }
    if (isset($_POST['new_password'])) {
        $new = $_POST['new_password'];
    }
    if (isset($_POST['confirm_password'])) {
        $confirm = $_POST['confirm_password'];
    }
    if ($current === '') {
        return 'Enter your current password.';
    }
    $stored = db_rows($conn, 'SELECT password_hash FROM users WHERE user_id=' . (int) $user_id);
    if (count($stored) == 0) {
        return 'Could not find your account.';
    }
    if (password_verify($current, $stored[0]['password_hash']) == false) {
        return 'Your current password is not right.';
    }
    if (strlen($new) < 6) {
        return 'Use a new password with at least 6 characters.';
    }
    if ($new != $confirm) {
        return 'The two new passwords do not match.';
    }
    if ($new == $current) {
        return 'Choose a password that is different from the current one.';
    }
    $hash = password_hash($new, PASSWORD_DEFAULT);
    if (profile_run_update($conn, 'users', 'user_id', $user_id, array('password_hash' => $hash)) == false) {
        return 'Could not change your password. Please try again.';
    }
    session_regenerate_id(true);
    return '';
}

// ---------------------------------------------------------------------------------------------
// Profile picture (stored in uploads/avatars/, the database keeps only the file name)
// ---------------------------------------------------------------------------------------------

function profile_delete_avatar_file($stored)
{
    $stored = trim((string) $stored);
    if ($stored == '') {
        return;
    }
    if (strpos($stored, '/') !== false || strpos($stored, '\\') !== false || strpos($stored, '..') !== false) {
        return;
    }
    $path = __DIR__ . '/../uploads/avatars/' . $stored;
    if (is_file($path)) {
        @unlink($path);
    }
}

function profile_upload_avatar($conn, $user_id)
{
    if (isset($_FILES['avatar_file']) == false) {
        return 'Choose a picture to upload.';
    }
    $file = $_FILES['avatar_file'];
    if (isset($file['error']) == false) {
        return 'Choose a picture to upload.';
    }
    if ($file['error'] == UPLOAD_ERR_NO_FILE) {
        return 'Choose a picture to upload.';
    }
    if ($file['error'] != UPLOAD_ERR_OK) {
        return 'The picture did not upload. Try a smaller file.';
    }
    if ($file['size'] > 2000000) {
        return 'The picture is larger than 2 MB.';
    }
    $info = @getimagesize($file['tmp_name']);
    if ($info == false) {
        return 'That file is not a real image.';
    }
    // The file extension comes from the real image type, never from the name the browser sent
    $ext = '';
    if ($info[2] == IMAGETYPE_JPEG) {
        $ext = 'jpg';
    } elseif ($info[2] == IMAGETYPE_PNG) {
        $ext = 'png';
    } elseif ($info[2] == IMAGETYPE_GIF) {
        $ext = 'gif';
    } elseif ($info[2] == IMAGETYPE_WEBP) {
        $ext = 'webp';
    }
    if ($ext == '') {
        return 'Use a JPG, PNG, WEBP or GIF picture.';
    }
    $folder = __DIR__ . '/../uploads/avatars';
    if (is_dir($folder) == false) {
        if (@mkdir($folder, 0755, true) == false) {
            return 'Could not create the folder for profile pictures.';
        }
    }
    $new_name = 'user' . (int) $user_id . '_' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $folder . '/' . $new_name) == false) {
        return 'Could not save the picture.';
    }
    $profile = profile_load($conn, $user_id);
    $old_name = '';
    if (isset($profile['avatar'])) {
        $old_name = (string) $profile['avatar'];
    }
    if (profile_run_update($conn, 'user_profiles', 'user_id', $user_id, array('avatar' => $new_name)) == false) {
        profile_delete_avatar_file($new_name);
        return 'Could not save the picture.';
    }
    if ($old_name != $new_name) {
        profile_delete_avatar_file($old_name);
    }
    return '';
}

function profile_remove_avatar($conn, $user_id)
{
    $profile = profile_load($conn, $user_id);
    $old_name = '';
    if (isset($profile['avatar'])) {
        $old_name = (string) $profile['avatar'];
    }
    if (profile_run_update($conn, 'user_profiles', 'user_id', $user_id, array('avatar' => null)) == false) {
        return 'Could not remove the picture.';
    }
    profile_delete_avatar_file($old_name);
    return '';
}

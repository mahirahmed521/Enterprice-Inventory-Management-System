<?php
// Wishlist: the heart button posts here, and $wish_ids lists the products the signed-in customer saved.
// The page must start the session and load $conn first.
$wish_ids = array();
$wish_customer_id = 0;
$wish_user_id = 0;
$wish_action = '';
$wish_product_id = 0;
$wish_return = 'home.php';
if (isset($_SESSION['user_id'])) {
    $wish_user_id = (int) $_SESSION['user_id'];
}
if (isset($_POST['action'])) {
    $wish_action = $_POST['action'];
}
if (isset($_POST['product_id'])) {
    $wish_product_id = (int) $_POST['product_id'];
}
if (isset($_SERVER['REQUEST_URI'])) {
    $wish_return = $_SERVER['REQUEST_URI'];
}
if ($wish_user_id > 0) {
    $wish_result = mysqli_query($conn, "SELECT customer_id FROM users WHERE user_id=$wish_user_id AND role='CUSTOMER' AND is_active=1");
    if ($wish_result) {
        $wish_row = mysqli_fetch_assoc($wish_result);
        if ($wish_row) {
            $wish_customer_id = (int) $wish_row['customer_id'];
        }
    }
}

if ($wish_action == 'toggle_wish') {
    if ($wish_user_id == 0) {
        $_SESSION['redirect_after_login'] = 'wishlist.php';
        $_SESSION['store_notice'] = 'Sign in to save items to your wishlist.';
        header('Location: login.php');
        exit;
    }
    if ($wish_customer_id == 0) {
        $_SESSION['store_notice'] = 'Wishlists are available on customer accounts.';
        header('Location: ' . $wish_return);
        exit;
    }
    $wish_product = mysqli_query($conn, "SELECT product_name FROM products WHERE product_id=$wish_product_id AND is_active=1");
    $wish_product_row = null;
    if ($wish_product) {
        $wish_product_row = mysqli_fetch_assoc($wish_product);
    }
    if ($wish_product_row == null) {
        $_SESSION['store_notice'] = 'That product is not available.';
        header('Location: ' . $wish_return);
        exit;
    }
    $wish_exists = mysqli_query($conn, "SELECT wishlist_id FROM wishlist_items WHERE customer_id=$wish_customer_id AND product_id=$wish_product_id");
    if ($wish_exists == false) {
        $_SESSION['store_notice'] = 'The wishlist is not set up yet. Import dabatases/update_pictures_and_features.sql first.';
    } elseif (mysqli_num_rows($wish_exists) > 0) {
        mysqli_query($conn, "DELETE FROM wishlist_items WHERE customer_id=$wish_customer_id AND product_id=$wish_product_id");
        $_SESSION['store_notice'] = $wish_product_row['product_name'] . ' was removed from your wishlist.';
    } else {
        mysqli_query($conn, "INSERT INTO wishlist_items (customer_id, product_id) VALUES ($wish_customer_id, $wish_product_id)");
        $_SESSION['store_notice'] = $wish_product_row['product_name'] . ' was saved to your wishlist.';
    }
    header('Location: ' . $wish_return);
    exit;
}

if ($wish_customer_id > 0) {
    $wish_list = mysqli_query($conn, "SELECT product_id FROM wishlist_items WHERE customer_id=$wish_customer_id");
    if ($wish_list) {
        while ($wish_item = mysqli_fetch_assoc($wish_list)) {
            $wish_ids[] = (int) $wish_item['product_id'];
        }
    }
}

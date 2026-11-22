<?php
// Finds the signed-in customer. $customer_id stays 0 for guests and for team accounts.
$customer_id = 0;
$customer = null;
$current_user_id = 0;
if (isset($_SESSION['user_id'])) {
    $current_user_id = (int) $_SESSION['user_id'];
}
if ($current_user_id > 0) {
    $sql = "SELECT c.* FROM users u JOIN customers c ON c.customer_id=u.customer_id WHERE u.user_id=$current_user_id AND u.role='CUSTOMER' AND u.is_active=1";
    $customer_result = mysqli_query($conn, $sql);
    if ($customer_result) {
        $customer = mysqli_fetch_assoc($customer_result);
    }
    if ($customer) {
        $customer_id = (int) $customer['customer_id'];
    }
}

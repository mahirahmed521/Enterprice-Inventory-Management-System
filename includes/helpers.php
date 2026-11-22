<?php
// Shared helpers. Loaded for every page by config/db.php.

// Product pictures live in the pictures/ folder and the database keeps only the file name.
// $stored is products.product_image. $prefix is '' for storefront pages and '../' for admin pages.
if (function_exists('product_image_url') == false) {
    function product_image_url($stored, $prefix = '')
    {
        $stored = trim((string) $stored);
        if ($stored == '') {
            return $prefix . 'pictures/default.png';
        }
        if (strpos($stored, '..') !== false) {
            return $prefix . 'pictures/default.png';
        }
        // Older rows hold a path such as uploads/products/mouse.jpg. Keep those working.
        if (strpos($stored, '/') !== false) {
            $parts = explode('/', $stored);
            $encoded = '';
            $p = 0;
            while (isset($parts[$p])) {
                if ($p > 0) {
                    $encoded = $encoded . '/';
                }
                $encoded = $encoded . rawurlencode($parts[$p]);
                $p = $p + 1;
            }
            return $prefix . $encoded;
        }
        return $prefix . 'pictures/' . rawurlencode($stored);
    }
}

// The picture files that are in the pictures/ folder, for the admin "choose a picture" list.
if (function_exists('list_pictures') == false) {
    function list_pictures()
    {
        $names = array();
        $folder = __DIR__ . '/../pictures';
        $found = @scandir($folder);
        if ($found == false) {
            return $names;
        }
        $allowed = array('jpg', 'jpeg', 'png', 'webp', 'gif');
        $f = 0;
        while (isset($found[$f])) {
            $ext = strtolower(pathinfo($found[$f], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $found[$f] != 'default.png') {
                $names[] = $found[$f];
            }
            $f = $f + 1;
        }
        return $names;
    }
}

// One number from a query that returns a single value. Returns $fallback if the query fails.
if (function_exists('db_scalar') == false) {
    function db_scalar($conn, $sql, $fallback = 0)
    {
        $result = mysqli_query($conn, $sql);
        if ($result == false) {
            return $fallback;
        }
        $row = mysqli_fetch_row($result);
        if ($row == null) {
            return $fallback;
        }
        if ($row[0] === null) {
            return $fallback;
        }
        return $row[0];
    }
}

// A whole query as an array of rows. Returns an empty array if the query fails.
if (function_exists('db_rows') == false) {
    function db_rows($conn, $sql)
    {
        $rows = array();
        $result = mysqli_query($conn, $sql);
        if ($result == false) {
            return $rows;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

// 1500 -> 1.5k, 2300000 -> 2.3M, 740 -> 740. Used on the small sales chart.
if (function_exists('money_short') == false) {
    function money_short($amount)
    {
        $amount = (float) $amount;
        if ($amount >= 1000000) {
            return round($amount / 1000000, 1) . 'M';
        }
        if ($amount >= 1000) {
            return round($amount / 1000, 1) . 'k';
        }
        return number_format($amount, 0);
    }
}

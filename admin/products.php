<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Products';
$section = 'Directory';
$error = '';
include '../includes/edit_request.php';
$action = '';
$sku = '';
$product_name = '';
$barcode = '';
$category_id = '';
$preferred_supplier_id = '';
$unit_of_measure = 'pcs';
$cost_price = '0';
$selling_price = '0';
$tax_rate = '0';
$reorder_point = '10';
$reorder_quantity = '50';
$is_active = '1';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['sku'])) {
    $sku = trim($_POST['sku']);
}
if (isset($_POST['product_name'])) {
    $product_name = trim($_POST['product_name']);
}
if (isset($_POST['barcode'])) {
    $barcode = trim($_POST['barcode']);
}
if (isset($_POST['category_id'])) {
    $category_id = trim($_POST['category_id']);
}
if (isset($_POST['preferred_supplier_id'])) {
    $preferred_supplier_id = trim($_POST['preferred_supplier_id']);
}
if (isset($_POST['unit_of_measure'])) {
    $unit_of_measure = trim($_POST['unit_of_measure']);
}
if (isset($_POST['cost_price'])) {
    $cost_price = trim($_POST['cost_price']);
}
if (isset($_POST['selling_price'])) {
    $selling_price = trim($_POST['selling_price']);
}
if (isset($_POST['tax_rate'])) {
    $tax_rate = trim($_POST['tax_rate']);
}
if (isset($_POST['reorder_point'])) {
    $reorder_point = trim($_POST['reorder_point']);
}
if (isset($_POST['reorder_quantity'])) {
    $reorder_quantity = trim($_POST['reorder_quantity']);
}
if (isset($_POST['is_active'])) {
    $is_active = trim($_POST['is_active']);
}
$search = '';
$status = '';
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}
if (isset($_GET['status'])) {
    $status = $_GET['status'];
}
require '../includes/auth.php';
if ($warehouse_access == false) {
    exit('Access denied.');
}

$message = '';
if (isset($_SESSION['notice'])) {
    $message = $_SESSION['notice'];
    unset($_SESSION['notice']);
}
$can_edit = true;
if ($account['role'] == 'STAFF') {
    $can_edit = false;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        exit('Read only.');
    }
}
if ($can_edit) {
    if ($id > 0) {
        $records = mysqli_query($conn, "SELECT * FROM products WHERE product_id=$id");
        $record = null;
        if ($records) {
            $record = mysqli_fetch_assoc($records);
        }
        if ($record == null) {
            exit('Record not found.');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $sku = $record['sku'];
            $product_name = $record['product_name'];
            $barcode = $record['barcode'];
            $category_id = $record['category_id'];
            $preferred_supplier_id = $record['preferred_supplier_id'];
            $unit_of_measure = $record['unit_of_measure'];
            $cost_price = $record['cost_price'];
            $selling_price = $record['selling_price'];
            $tax_rate = $record['tax_rate'];
            $reorder_point = $record['reorder_point'];
            $reorder_quantity = $record['reorder_quantity'];
            $is_active = $record['is_active'];
        }
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    mysqli_query($conn, 'START TRANSACTION');
    $parent = mysqli_query($conn, "SELECT product_id FROM products WHERE product_id=$id FOR UPDATE");
    if ($parent == false) {
        $error = 'Could not check this record.';
    } else {
        $bins = mysqli_query($conn, "SELECT quantity_on_hand, quantity_reserved FROM warehouse_stock WHERE product_id=$id FOR UPDATE");
        if ($bins == false) {
            $error = 'Could not check warehouse stock.';
        } else {
            while ($bin = mysqli_fetch_assoc($bins)) {
                if ($bin['quantity_on_hand']) {
                    $error = 'This record is still in use.';
                    break;
                }
                if ($bin['quantity_reserved']) {
                    $error = 'This record is still in use.';
                    break;
                }
            }
        }
    }
    if ($error == '') {
        if (mysqli_query($conn, "DELETE FROM products WHERE product_id=$id") == false) {
            $error = 'This record is still in use.';
        } elseif (mysqli_affected_rows($conn) == 0) {
            $error = 'Record not found.';
        }
    }
    if ($error == '') {
        if (mysqli_query($conn, 'COMMIT') == false) {
            $error = 'Could not save the change.';
        }
    }
    if ($error) {
        mysqli_query($conn, 'ROLLBACK');
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    $valid_status = false;
    if ($is_active === '1') {
        $valid_status = true;
    } elseif ($is_active === '0') {
        $valid_status = true;
    }
    if ($sku === '') {
        $error = 'Enter a SKU.';
    } elseif ($product_name === '') {
        $error = 'Enter a product name.';
    } elseif ($unit_of_measure === '') {
        $error = 'Enter a unit of measure.';
    } elseif ($valid_status == false) {
        $error = 'Choose a valid status.';
    } elseif (is_numeric($selling_price) == false) {
        $error = 'Enter a numeric selling price.';
    } elseif (is_numeric($cost_price) == false) {
        $error = 'Enter a numeric cost price.';
    } elseif (is_numeric($tax_rate) == false) {
        $error = 'Enter a numeric tax rate.';
    } elseif ($cost_price < 0) {
        $error = 'Selling price must be zero or positive.';
    } elseif ($selling_price < 0) {
        $error = 'Cost price must be zero or positive.';
    } elseif ($tax_rate < 0) {
        $error = 'Tax rate must be zero or positive.';
    } elseif ($reorder_point == '') {
        $error = 'Enter a whole number for the reorder point.';
    } elseif ($reorder_quantity == '') {
        $error = 'Enter a whole number for the reorder quantity.';
    }
    $valid_reorder_point = isset($reorder_point[0]);
    $digit_position = 0;
    while (isset($reorder_point[$digit_position])) {
        if ($reorder_point[$digit_position] < '0') {
            $valid_reorder_point = false;
        }
        if ($reorder_point[$digit_position] > '9') {
            $valid_reorder_point = false;
        }
        $digit_position = $digit_position + 1;
    }
    $valid_reorder_quantity = isset($reorder_quantity[0]);
    $digit_position = 0;
    while (isset($reorder_quantity[$digit_position])) {
        if ($reorder_quantity[$digit_position] < '0') {
            $valid_reorder_quantity = false;
        }
        if ($reorder_quantity[$digit_position] > '9') {
            $valid_reorder_quantity = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($error == '') {
        if ($valid_reorder_point == false) {
            $error = 'Enter a whole number for the reorder point.';
        }
    }
    if ($error == '') {
        if ($valid_reorder_quantity == false) {
            $error = 'Enter a whole number for the reorder quantity.';
        }
    }

    if ($error == '') {
        $sql_text = [
            'sku' => $sku,
            'product_name' => $product_name,
            'barcode' => $barcode,
            'unit_of_measure' => $unit_of_measure
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $category_id_sql = 'NULL';
        if (isset($category_id[0])) {
            $valid_digits = isset($category_id[0]);
            $digit_position = 0;
            while (isset($category_id[$digit_position])) {
                if ($category_id[$digit_position] < '0') {
                    $valid_digits = false;
                }
                if ($category_id[$digit_position] > '9') {
                    $valid_digits = false;
                }
                $digit_position = $digit_position + 1;
            }
            if ($valid_digits == false) {
                exit('Choose a valid record.');
            }
            $category_id_sql = $category_id;
        }
        $preferred_supplier_id_sql = 'NULL';
        if (isset($preferred_supplier_id[0])) {
            $valid_digits = isset($preferred_supplier_id[0]);
            $digit_position = 0;
            while (isset($preferred_supplier_id[$digit_position])) {
                if ($preferred_supplier_id[$digit_position] < '0') {
                    $valid_digits = false;
                }
                if ($preferred_supplier_id[$digit_position] > '9') {
                    $valid_digits = false;
                }
                $digit_position = $digit_position + 1;
            }
            if ($valid_digits == false) {
                exit('Choose a valid record.');
            }
            $preferred_supplier_id_sql = $preferred_supplier_id;
        }
        $barcode_value = 'NULL';
        if (isset($barcode[0])) {
            $barcode_value = "'$sql_text[barcode]'";
        }

        if ($id == 0) {
            $sql = "INSERT INTO products (sku, product_name, barcode, category_id, preferred_supplier_id, unit_of_measure, cost_price, selling_price, tax_rate, reorder_point, reorder_quantity, is_active)
                VALUES ('$sql_text[sku]', '$sql_text[product_name]', $barcode_value, $category_id_sql, $preferred_supplier_id_sql, '$sql_text[unit_of_measure]', '$cost_price', '$selling_price', '$tax_rate', '$reorder_point', '$reorder_quantity', '$is_active')";
        } else {
            $sql = "UPDATE products SET sku='$sql_text[sku]',
                    product_name='$sql_text[product_name]',
                    barcode=$barcode_value,
                    category_id=$category_id_sql,
                    preferred_supplier_id=$preferred_supplier_id_sql,
                    unit_of_measure='$sql_text[unit_of_measure]',
                    cost_price='$cost_price',
                    selling_price='$selling_price',
                    tax_rate='$tax_rate',
                    reorder_point='$reorder_point',
                    reorder_quantity='$reorder_quantity',
                    is_active='$is_active'
                    WHERE product_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the SKU, barcode and prices.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: products.php');
        exit;
    }
}

$columns = 'p.product_id,p.product_name,p.sku,p.selling_price,p.unit_of_measure,p.is_active,p.reorder_point,
            v.category_name AS category,v.available_to_sell';
if ($can_edit) {
    $columns = $columns . ',p.cost_price';
}
$sql_text = [
    'search' => $search
];
include __DIR__ . '/../includes/quote_fields.php';
$term = $sql_text['search'];
$sql = "SELECT $columns FROM products p JOIN vw_inventory_valuation v USING(product_id)
        WHERE (p.product_name LIKE '%$term%' OR p.sku LIKE '%$term%')";
if ($status === '0') {
    $sql = $sql . " AND p.is_active=0";
} elseif ($status === '1') {
    $sql = $sql . " AND p.is_active=1";
}
$products = mysqli_query($conn, $sql . ' ORDER BY p.product_name,p.product_id');
if ($products == false) {
    exit('Could not load the catalog.');
}
$show_editor = false;
if ($can_edit) {
    if ($editor) {
        $show_editor = true;
    }
}
if ($show_editor) {
    $categories = mysqli_query($conn, 'SELECT category_id,name FROM categories ORDER BY name');
    $suppliers = mysqli_query($conn, 'SELECT supplier_id,company_name FROM suppliers ORDER BY company_name');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Products | Stockroom</title>
    <?php include '../includes/admin_css.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/business-overview.css">
    <link rel="stylesheet" href="../assets/css/modules.css">
</head>
<body class="workspace-page">
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>
    <main class="page-wrapper">
        <div class="page-content"><?php include '../templates/products.php'; ?></div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/admin_script.php'; ?>
</body>
</html>

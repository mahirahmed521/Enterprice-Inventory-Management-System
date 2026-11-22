<?php
require_once __DIR__ . '/../includes/check_fields.php';
include '../includes/edit_request.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
$pageTitle = 'Suppliers';
$section = 'Directory';
$error = '';

$action = '';
$company_name = '';
$contact_person = '';
$email = '';
$phone = '';
$tax_id = '';
$address = '';
$is_active = '1';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
if (isset($_POST['company_name'])) {
    $company_name = $_POST['company_name'];
}
if (isset($_POST['contact_person'])) {
    $contact_person = $_POST['contact_person'];
}
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
if (isset($_POST['phone'])) {
    $phone = $_POST['phone'];
}
if (isset($_POST['tax_id'])) {
    $tax_id = $_POST['tax_id'];
}
if (isset($_POST['address'])) {
    $address = $_POST['address'];
}
if (isset($_POST['is_active'])) {
    $is_active = $_POST['is_active'];
}


if ($supervisor == false) {
    exit('Access denied.');
}

if ($id > 0) {
    $records = mysqli_query($conn, "SELECT * FROM suppliers WHERE supplier_id=$id");
    $record = null;
    if ($records) {
        $record = mysqli_fetch_assoc($records);
    }
    if ($record == null) {
        exit('Record not found.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $company_name = $record['company_name'];
        $contact_person = $record['contact_person'];
        $email = $record['email'];
        $phone = $record['phone'];
        $tax_id = $record['tax_id'];
        $address = $record['address'];
        $is_active = $record['is_active'];
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    $deleted = mysqli_query($conn, "DELETE FROM suppliers WHERE supplier_id=$id");
    if ($deleted == false) {
        $error = 'This record is still in use.';
    } elseif (mysqli_affected_rows($conn) == 0) {
        $error = 'This record is still in use.';
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    $valid_status = false;
    if ($is_active === '1') {
        $valid_status = true;
    } elseif ($is_active === '0') {
        $valid_status = true;
    }
    if ($company_name === '') {
        $error = 'Complete the required fields.';
    } elseif ($valid_status == false) {
        $error = 'Choose a valid status.';
    }

    if ($error == '') {
        $sql_text = [
            'company_name' => $company_name,
            'contact_person' => $contact_person,
            'email' => $email,
            'phone' => $phone,
            'tax_id' => $tax_id,
            'address' => $address
        ];
        include __DIR__ . '/../includes/quote_fields.php';

        if ($id == 0) {
            $sql = "INSERT INTO suppliers (company_name, contact_person, email, phone, tax_id, address, is_active)
                VALUES ('$sql_text[company_name]', '$sql_text[contact_person]', '$sql_text[email]', '$sql_text[phone]', '$sql_text[tax_id]', '$sql_text[address]', '$is_active')";
        } else {
            $sql = "UPDATE suppliers SET company_name='$sql_text[company_name]',
                    contact_person='$sql_text[contact_person]',
                    email='$sql_text[email]',
                    phone='$sql_text[phone]',
                    tax_id='$sql_text[tax_id]',
                    address='$sql_text[address]',
                    is_active='$is_active'
                    WHERE supplier_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the supplier name and contact details.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: suppliers.php');
        exit;
    }
}

$rows = mysqli_query($conn, 'SELECT supplier_id,company_name,contact_person,email,phone,is_active FROM suppliers ORDER BY supplier_id DESC');
include '../includes/workspace.php';
include '../templates/suppliers.php';
include '../includes/end.php';

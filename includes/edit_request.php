<?php
require_once __DIR__ . '/check_fields.php';
$id = 0;
$editor = '';
if (isset($_GET['id'])) {
    $valid_digits = isset($_GET['id'][0]);
    $digit_position = 0;
    while (isset($_GET['id'][$digit_position])) {
        if ($_GET['id'][$digit_position] < '0') {
            $valid_digits = false;
        }
        if ($_GET['id'][$digit_position] > '9') {
            $valid_digits = false;
        }
        $digit_position = $digit_position + 1;
    }
    if ($valid_digits == false) {
        exit('Record not found.');
    }
    $id = $_GET['id'];
}
if (isset($_GET['editor'])) {
    if ($_GET['editor'] == 'new') {
        $editor = 'new';
    }
}
if ($id > 0) {
    $editor = 'edit';
}

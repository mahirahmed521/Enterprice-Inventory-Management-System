<?php
require_once __DIR__ . '/check_fields.php';
$id = 0;
$editor = '';
if (isset($_GET['id'])) {
    if (ctype_digit($_GET['id']) == false) {
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

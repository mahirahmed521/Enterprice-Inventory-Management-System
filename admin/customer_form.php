<?php
include '../includes/edit_request.php';
require_once __DIR__ . '/../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

$destination = 'customers.php?editor=new';
if ($id > 0) {
    $destination = 'customers.php?id=' . $id;
}
header('Location: ' . $destination);
exit;

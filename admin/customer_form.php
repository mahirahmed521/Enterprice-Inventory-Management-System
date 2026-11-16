<?php
include '../includes/edit_request.php';
require '../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

$destination = 'customers.php?editor=new';
if ($id > 0) {
    $destination = 'customers.php?id=' . $id;
}
header('Location: ' . $destination);
exit;

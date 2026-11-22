<?php
$database = getenv('STOCKROOM_DB');
if ($database == false) {
    $database = 'intermediate_inventory';
}
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect('127.0.0.1', 'root', '', $database, 3306);
if ($conn == false) {
    exit('Start MySQL in Laragon and check config/db.php.');
}
if (mysqli_set_charset($conn, 'utf8mb4') == false) {
    exit('Could not set up the database connection.');
}
$sql = "SET SESSION sql_mode = CONCAT(@@sql_mode, ',NO_BACKSLASH_ESCAPES')";
if (mysqli_query($conn, $sql) == false) {
    exit('Could not set up the database connection.');
}
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/prg.php';

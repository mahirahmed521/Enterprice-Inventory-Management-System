<?php
// Laragon database settings.
$dbHost = '127.0.0.1';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'intermediate_inventory';
$dbPort = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Database connection failed. Start MySQL in Laragon and check config/db.php.');
}

<?php
// Koneksi Database
$DB_HOST = 'localhost';
$DB_USER = 'root'; // XAMPP default
$DB_PASS = '';
$DB_NAME = 'sikos';
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    error_log('DB Connect error: ' . $mysqli->connect_error, 3, __DIR__ . '/../logs/app.log');
    die('Database error. Hubungi admin.');
}
?>
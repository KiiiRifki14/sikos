<?php
require 'inc/koneksi.php';
session_start();
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='OWNER') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Owner Dashboard - SIKOS</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Owner Dashboard</h2>
  <p>Selamat Datang, Owner!</p>
  <ul>
    <li><a href="admin/kamar_data.php">Manajemen Kamar</a></li>
    <li><a href="admin/booking_data.php">Booking</a></li>
    <li><a href="admin/penghuni_data.php">Data Penghuni</a></li>
    <li><a href="admin/pengumuman_data.php">Pengumuman</a></li>
    <li><a href="admin/laporan.php">Laporan</a></li>
  </ul>
  <a href="logout.php">Logout</a>
</div>
</body>
</html>
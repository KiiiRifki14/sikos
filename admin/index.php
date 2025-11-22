<?php
session_start();
require '../inc/guard.php';
if (!is_admin() && !is_owner()) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel - SIKOS Paadaasih</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body>
<div class="header">SIKOS - Panel Admin</div>
<div class="container">
  <h2>Dashboard Admin</h2>
  <ul>
    <li><a href="kamar_data.php">Manajemen Kamar</a></li>
    <li><a href="booking_data.php">Manajemen Booking</a></li>
    <li><a href="penghuni_data.php">Data Penghuni</a></li>
    <li><a href="pengumuman_data.php">Pengumuman</a></li>
    <li><a href="laporan.php">Laporan</a></li>
    <li><a href="settings.php">Pengaturan</a></li>
  </ul>
  <p><a href="../logout.php">Logout</a></p>
</div>
<div class="footer">SIKOS &copy; 2025</div>
</body>
</html>
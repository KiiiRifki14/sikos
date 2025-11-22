<?php
require 'inc/koneksi.php';
session_start();
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') {
    header('Location: login.php');
    exit;
}
$id_pengguna = $_SESSION['id_pengguna'];
$stmt = $mysqli->prepare("SELECT nama FROM pengguna WHERE id_pengguna=?");
$stmt->bind_param('i', $id_pengguna);
$stmt->execute(); $row=$stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Penghuni - SIKOS</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Dashboard Penghuni</h2>
  <p>Selamat Datang, <?= htmlspecialchars($row['nama']) ?>!</p>
  <ul>
    <li><a href="kamar_saya.php">Kamar Saya</a></li>
    <li><a href="tagihan_saya.php">Tagihan & Pembayaran</a></li>
    <li><a href="pengumuman.php">Pengumuman</a></li>
    <li><a href="keluhan.php">Keluhan</a></li>
  </ul>
  <a href="logout.php">Logout</a>
</div>
</body>
</html>
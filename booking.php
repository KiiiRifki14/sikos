<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php?info=Login dulu sebelum booking!');
    exit;
}
$id_kamar = intval($_GET['id_kamar'] ?? 0);
// KODE BARU (Sudah diperbaiki)
if ($id_kamar <= 0) pesan_error("index.php", "Kamar tidak ditemukan atau URL salah!");

// 1. Ambil detail kamar utama
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) pesan_error("index.php", "Maaf, data kamar tersebut tidak ditemukan.");

?>
<!DOCTYPE html>
<html>
<head>
  <title>Booking Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
<h3>Booking Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h3>
<form method="POST" action="proses_booking.php" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">
  KTP (jpg/png/webp): <input type="file" name="ktp_opt" required><br>
  Check-in Rencana: <input type="date" name="checkin_rencana" required><br>
  Durasi (bulan): <input type="number" name="durasi_bulan_rencana" min="1" value="12" required oninput="this.value = !!this.value && Math.abs(this.value) >= 1 ? Math.abs(this.value) : 1"><br>
  <button type="submit">Booking</button>
</form>
</div>
</body>
</html>
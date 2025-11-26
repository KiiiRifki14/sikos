<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

$id_booking = intval($_GET['booking'] ?? 0);
if ($id_booking < 1) die("Invalid booking!");

$res = $mysqli->prepare("SELECT p.*, b.id_kamar, b.checkin_rencana FROM pembayaran p JOIN booking b ON p.ref_id=b.id_booking WHERE b.id_booking=? AND p.ref_type='BOOKING'");
$res->bind_param('i', $id_booking);
$res->execute();
$row = $res->get_result()->fetch_assoc();
if (!$row) die("Data booking tidak ditemukan!");

?>
<!DOCTYPE html>
<html>
<head><title>Pembayaran Booking Kamar</title>
<link rel="stylesheet" href="assets/css/app.css"/></head>
<body>
<div class="container">
  <h2>Pembayaran Booking Kamar</h2>
  <p>Booking Kamar ID: <?= $id_booking ?></p>
  <b>Jumlah yang harus dibayar: Rp<?= number_format($row['jumlah'],0,',','.') ?></b><br>
  <b>Rekening: BANK BCA 1473210151 a/n KOS PAADAASIH</b><br>
  <br>
  <form method="POST" action="proses_pembayaran.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>">
    Upload Bukti Transfer (jpg/png/webp, max 2MB): <input type="file" name="bukti_tf" required><br><br>
    <button type="submit">Upload Bukti</button>
  </form>
  <br>
  <a href="penghuni_dashboard.php">Kembali ke Dashboard</a>
</div>
</body>
</html>
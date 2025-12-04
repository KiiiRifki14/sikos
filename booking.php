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
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Booking Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h3>Booking Kamar <?= htmlspecialchars($row['kode_kamar']) ?> - <?= htmlspecialchars($row['nama_tipe'] ?? '') ?></h3>
  <form method="POST" action="proses_booking.php" enctype="multipart/form-data" class="space-y-4" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">

    <div>
      <label for="ktp_opt" class="block text-sm font-medium text-slate-700 mb-1">KTP (jpg/png/webp)</label>
      <input id="ktp_opt" type="file" name="ktp_opt" accept="image/jpeg,image/png,image/webp" required class="form-input">
      <p class="text-xs text-slate-400 mt-1">Maks 2MB. Foto KTP harus jelas dan terbaca.</p>
    </div>

    <div>
      <label for="checkin_rencana" class="block text-sm font-medium text-slate-700 mb-1">Check-in Rencana</label>
      <input id="checkin_rencana" type="date" name="checkin_rencana" required class="form-input">
    </div>

    <div>
      <label for="durasi_bulan_rencana" class="block text-sm font-medium text-slate-700 mb-1">Durasi (bulan)</label>
      <input id="durasi_bulan_rencana" type="number" name="durasi_bulan_rencana" min="1" value="12" required oninput="this.value = !!this.value && Math.abs(this.value) >= 1 ? Math.abs(this.value) : 1" class="form-input">
    </div>

    <button type="submit" class="btn-primary">Booking</button>
  </form>
</div>
</body>
</html>
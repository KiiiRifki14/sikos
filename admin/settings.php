<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Contoh proses penyimpanan, edit sesuai kebutuhan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simpan pengaturan (boleh ke database/file, di sini hanya contoh)
    $nama_kos = $_POST['nama_kos'] ?? '';
    $telp_kos = $_POST['telp_kos'] ?? '';
    $alamat_kos = $_POST['alamat_kos'] ?? '';
    $pesan = '<div style="color:green;">Pengaturan disimpan.</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Pengaturan Admin - SIKOS Paadaasih</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Pengaturan Admin</h2>
  <?php if (!empty($pesan)) echo $pesan; ?>
  <form method="post">
    Nama Kos: <input type="text" name="nama_kos" value="<?= htmlspecialchars($nama_kos ?? 'KOS Paadaasih') ?>"><br>
    Telepon Kos: <input type="text" name="telp_kos" value="<?= htmlspecialchars($telp_kos ?? '0812xxxxxxxx') ?>"><br>
    Alamat Kos: <textarea name="alamat_kos"><?= htmlspecialchars($alamat_kos ?? 'Jl. Paadaasih No. 123, Cimahi') ?></textarea><br>
    <button type="submit">Simpan Pengaturan</button>
  </form>
  <br>
  <a href="index.php" class="button">Kembali ke Dashboard</a>
</div>
</body>
</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Dummy Save Process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = '<div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:1rem;">Pengaturan berhasil disimpan!</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Pengaturan - SIKOS Admin</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏠</div>
      <div class="brand-text"><h1>SIKOS</h1><p>ADMIN PANEL</p></div>
    </div>
    <ul class="nav-links">
      <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
      
      <li><a href="pembayaran_data.php"><span class="nav-icon">💰</span> Pembayaran</a></li>
      <li><a href="kamar_data.php"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
      <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
      <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
      <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
      <li><a href="laporan.php" class="active"><span class="nav-icon">📈</span> Laporan</a></li>
      <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
      <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
      <h2>Pengaturan Sistem</h2>
    </header>

    <div class="settings-card">
        <?php if(isset($pesan)) echo $pesan; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label">Nama Kost</label>
                <input type="text" name="nama_kos" class="form-input" value="Kost Paadaasih">
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Telepon Pengelola</label>
                <input type="text" name="telp_kos" class="form-input" value="0812-3456-7890">
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat_kos" class="form-input" rows="4">Jl. Paadaasih No. 123, Cimahi</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Rekening Bank (untuk transfer)</label>
                <input type="text" name="rek_bank" class="form-input" value="BCA 123456789 a.n Owner">
            </div>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
    </div>
  </main>
</body>
</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Dummy Save Process
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = '<div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:24px; font-size:14px;">✅ Pengaturan berhasil disimpan!</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Pengaturan Sistem</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <aside class="sidebar">
    <div class="mb-8 px-2 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">A</div>
        <div>
            <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            <p class="text-xs text-slate-400">Management Panel</p>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto;">
        <a href="index.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-blue-500"></i> Dashboard</a>
        <a href="kamar_data.php" class="sidebar-link"><i class="fa-solid fa-door-open w-6 text-orange-500"></i> Data Kamar</a>
        <a href="booking_data.php" class="sidebar-link"><i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> Booking</a>
        <a href="pembayaran_data.php" class="sidebar-link"><i class="fa-solid fa-sack-dollar w-6 text-yellow-500"></i> Pembayaran</a>
        <a href="penghuni_data.php" class="sidebar-link"><i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni</a>
        <a href="keluhan_data.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain</a>
        <a href="laporan.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-teal-500"></i> Laporan</a>
        <a href="settings.php" class="sidebar-link active"><i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan</a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Pengaturan Sistem</h1>
    <?= $pesan ?>

    <div class="card-white" style="max-width:600px;">
        <form method="post">
            <div style="margin-bottom:20px;">
                <label class="form-label">Nama Kost</label>
                <input type="text" name="nama_kos" class="form-input" value="Kost Paadaasih">
            </div>
            <div style="margin-bottom:20px;">
                <label class="form-label">Nomor Telepon Pengelola</label>
                <input type="text" name="telp_kos" class="form-input" value="0812-3456-7890">
            </div>
            <div style="margin-bottom:20px;">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat_kos" class="form-input" rows="3">Jl. Paadaasih No. 123, Cimahi</textarea>
            </div>
            <div style="margin-bottom:32px;">
                <label class="form-label">Rekening Bank (untuk transfer)</label>
                <input type="text" name="rek_bank" class="form-input" value="BCA 123456789 a.n Owner">
            </div>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
    </div>
  </main>
</body>
</html>
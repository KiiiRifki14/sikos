<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

// Hitung Data
$total_kamar = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$kamar_terisi = $mysqli->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
$booking_pending = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];
$omset = $mysqli->query("SELECT SUM(harga) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="mb-8">
            <div class="text-2xl font-bold text-blue-600 mb-1">SIKOS</div>
            <div class="text-xs text-gray-400 uppercase tracking-wider">Admin Panel</div>
        </div>
        <nav class="flex-1 overflow-y-auto">
            <a href="index.php" class="sidebar-link active">📊 Dashboard</a>
            <a href="kamar_data.php" class="sidebar-link">🛏️ Kelola Kamar</a>
            <a href="booking_data.php" class="sidebar-link">📝 Booking <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full"><?= $booking_pending ?></span></a>
            <a href="pembayaran_data.php" class="sidebar-link">💰 Pembayaran</a>
            <a href="penghuni_data.php" class="sidebar-link">👥 Penghuni</a>
            <a href="keluhan_data.php" class="sidebar-link">🔧 Komplain</a>
            <a href="laporan.php" class="sidebar-link">📈 Laporan</a>
            <a href="settings.php" class="sidebar-link">⚙️ Pengaturan</a>
        </nav>
        <a href="../logout.php" class="sidebar-link text-red-600 mt-4 hover:bg-red-50">🚪 Logout</a>
    </aside>

    <main class="main-content">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
            <button class="btn btn-primary">+ Tambah Kamar</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <div class="text-gray-500 text-xs uppercase font-bold mb-2">Total Kamar</div>
                <div class="text-3xl font-bold text-gray-800"><?= $total_kamar ?></div>
            </div>
            <div class="card">
                <div class="text-gray-500 text-xs uppercase font-bold mb-2">Terisi</div>
                <div class="text-3xl font-bold text-green-600"><?= $kamar_terisi ?></div>
            </div>
            <div class="card">
                <div class="text-gray-500 text-xs uppercase font-bold mb-2">Booking Pending</div>
                <div class="text-3xl font-bold text-amber-500"><?= $booking_pending ?></div>
            </div>
            <div class="card">
                <div class="text-gray-500 text-xs uppercase font-bold mb-2">Est. Omset</div>
                <div class="text-3xl font-bold text-blue-600"><?= number_format($omset/1000000, 1) ?> Jt</div>
            </div>
        </div>

        <div class="card">
            <h3 class="font-bold text-gray-800 mb-4">Booking Terbaru</h3>
            <p class="text-gray-400 text-sm">Silakan cek menu Booking untuk verifikasi.</p>
        </div>
    </main>
</div>

</body>
</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

// HITUNG STATISTIK (Real Data)
$total_kamar = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$kamar_terisi = $mysqli->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
$booking_pending = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];

// Hitung Estimasi Pendapatan (Total harga kamar yang terisi)
$pendapatan = $mysqli->query("SELECT SUM(harga) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard - SIKOS</title>
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
    <li><a href="kamar_data.php"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
    <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
    <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
    <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
    <li><a href="laporan.php"><span class="nav-icon">📈</span> Laporan</a></li>
    <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
    <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</nav>
  <main class="main-content">
    
    <header class="admin-header">
      <h2>Dashboard Overview</h2>
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="text-align:right;">
            <div style="font-weight:bold;">Administrator</div>
            <div style="font-size:0.8rem; color:#64748b;">Super User</div>
        </div>
        <div style="width:40px; height:40px; background:#f59e0b; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold;">A</div>
      </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon bg-blue-light">🛏️</div>
          <div class="stat-info">
            <h3><?= $total_kamar ?></h3>
            <p>Total Kamar</p>
          </div>
        </div>
        <div class="stat-card red">
          <div class="stat-icon bg-red-light">👥</div>
          <div class="stat-info">
            <h3><?= $kamar_terisi ?></h3>
            <p>Kamar Terisi</p>
          </div>
        </div>
        <div class="stat-card yellow">
          <div class="stat-icon bg-yellow-light">⏳</div>
          <div class="stat-info">
            <h3><?= $booking_pending ?></h3>
            <p>Booking Pending</p>
          </div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon bg-green-light">💰</div>
          <div class="stat-info">
            <h3>Rp<?= number_format($pendapatan, 0, ',', '.') ?></h3>
            <p>Estimasi Omset/Bulan</p>
          </div>
        </div>
    </div>

    <div class="data-section">
        <div class="section-header">
            <h3>Aktivitas Terbaru</h3>
        </div>
        <p style="color:#64748b;">Selamat datang di panel admin SIKOS. Gunakan menu di sebelah kiri untuk mengelola data.</p>
    </div>

  </main>
</body>
</html>
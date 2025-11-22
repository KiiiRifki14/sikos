<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $row_penghuni['id_penghuni'] ?? 0;

// Ambil data kontrak aktif
$row_kontrak = $mysqli->query("SELECT * FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kamar Saya - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏠</div>
      <div class="brand-text"><h1>SIKOS</h1><p>TENANT AREA</p></div>
    </div>
    <ul class="nav-links">
      <li><a href="penghuni_dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
      <li><a href="kamar_saya.php" class="active"><span class="nav-icon">🛏️</span> Kamar Saya</a></li>
      <li><a href="tagihan_saya.php"><span class="nav-icon">💳</span> Tagihan & Bayar</a></li>
      <li><a href="keluhan.php"><span class="nav-icon">🔧</span> Keluhan</a></li>
      <li><a href="pengumuman.php"><span class="nav-icon">📢</span> Pengumuman</a></li>
      <li style="margin-top: 2rem;"><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
        <h2>Informasi Kamar Saya</h2>
    </header>

    <div class="data-section">
        <?php if (!$row_kontrak) { ?>
            <div style="text-align:center; padding:3rem;">
                <div style="font-size:3rem;">🛏️</div>
                <h3>Anda belum menyewa kamar.</h3>
                <p>Silakan lakukan booking kamar terlebih dahulu di halaman utama.</p>
                <a href="index.php" class="btn-solid btn-blue">Cari Kamar</a>
            </div>
        <?php 
        } else { 
            $id_kamar = $row_kontrak['id_kamar'];
            $row_kamar = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=$id_kamar")->fetch_assoc();
        ?>
            <div class="booking-header">
                <div class="booking-info">
                    <h4>Kamar <?= htmlspecialchars($row_kamar['kode_kamar']) ?></h4>
                    <div class="booking-date"><?= htmlspecialchars($row_kamar['nama_tipe']) ?></div>
                </div>
                <span class="status-badge badge-active">AKTIF</span>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem; margin-top:1rem;">
                <div>
                    <?php if($row_kamar['foto_cover']){ ?>
                        <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" style="width:100%; border-radius:10px;">
                    <?php } else { ?>
                        <div style="background:#eee; height:200px; display:flex; align-items:center; justify-content:center; border-radius:10px;">No Image</div>
                    <?php } ?>
                </div>

                <div>
                    <div class="detail-row"><span class="detail-label">Check-in</span> <span class="detail-value"><?= date('d M Y', strtotime($row_kontrak['tanggal_mulai'])) ?></span></div>
                    <div class="detail-row"><span class="detail-label">Berakhir</span> <span class="detail-value"><?= date('d M Y', strtotime($row_kontrak['tanggal_selesai'])) ?></span></div>
                    <div class="detail-row"><span class="detail-label">Durasi</span> <span class="detail-value"><?= $row_kontrak['durasi_bulan'] ?> Bulan</span></div>
                    <div class="detail-row"><span class="detail-label">Luas</span> <span class="detail-value"><?= $row_kamar['luas_m2'] ?> m²</span></div>
                    <div class="detail-row"><span class="detail-label">Lantai</span> <span class="detail-value"><?= $row_kamar['lantai'] ?></span></div>
                    <div class="detail-row"><span class="detail-label">Fasilitas</span> <span class="detail-value"><?= htmlspecialchars($row_kamar['catatan']) ?></span></div>
                </div>
            </div>
        <?php } ?>
    </div>
  </main>
</body>
</html>
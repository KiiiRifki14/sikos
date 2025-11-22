<?php
require 'inc/koneksi.php';
session_start();

// Cek Login & Peran
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil Data Penghuni
$stmt = $mysqli->prepare("SELECT * FROM pengguna WHERE id_pengguna=?");
$stmt->bind_param('i', $id_pengguna);
$stmt->execute(); 
$user = $stmt->get_result()->fetch_assoc();

// Cari Data Penghuni Detail (untuk cek status kamar dll)
$stmt2 = $mysqli->prepare("SELECT id_penghuni FROM penghuni WHERE id_pengguna=?");
$stmt2->bind_param('i', $id_pengguna);
$stmt2->execute();
$penghuni = $stmt2->get_result()->fetch_assoc();
$id_penghuni = $penghuni['id_penghuni'] ?? 0;

// Hitung Tagihan Belum Bayar
$tagihan_pending = 0;
if ($id_penghuni) {
    $q_tagihan = $mysqli->query("SELECT COUNT(*) FROM tagihan t 
        JOIN kontrak k ON t.id_kontrak=k.id_kontrak 
        WHERE k.id_penghuni=$id_penghuni AND t.status='BELUM'");
    $tagihan_pending = $q_tagihan->fetch_row()[0] ?? 0;
}

// Cek Kamar Aktif
$kamar_aktif = '-';
if ($id_penghuni) {
    $q_kamar = $mysqli->query("SELECT km.kode_kamar FROM kontrak k 
        JOIN kamar km ON k.id_kamar=km.id_kamar 
        WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF' LIMIT 1");
    if ($row = $q_kamar->fetch_assoc()) {
        $kamar_aktif = $row['kode_kamar'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Dashboard Penghuni - SIKOS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏠</div>
      <div class="brand-text">
        <h1>SIKOS</h1>
        <p>TENANT AREA</p>
      </div>
    </div>
    
    <ul class="nav-links">
      <li><a href="penghuni_dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard</a></li>
      <li><a href="kamar_saya.php"><span class="nav-icon">🛏️</span> Kamar Saya</a></li>
      <li><a href="tagihan_saya.php"><span class="nav-icon">💳</span> Tagihan & Bayar</a></li>
      <li><a href="keluhan.php"><span class="nav-icon">🔧</span> Keluhan</a></li>
      <li><a href="pengumuman.php"><span class="nav-icon">📢</span> Pengumuman</a></li>
      
      <li style="margin-top: 2rem;"><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>

    <div class="user-profile">
        <div class="avatar"><?= strtoupper(substr($user['nama'], 0, 2)) ?></div>
        <div style="font-size:0.85rem;">
            <div style="font-weight:600;"><?= htmlspecialchars($user['nama']) ?></div>
            <div style="opacity:0.7; font-size:0.75rem;">Penghuni</div>
        </div>
    </div>
  </nav>

  <main class="main-content">
    
    <header class="admin-header">
      <h2>Dashboard Penghuni</h2>
      <div style="color:var(--text-muted); font-size:0.9rem;">
        Selamat Datang, <b><?= htmlspecialchars($user['nama']) ?></b>!
      </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card blue">
          <div class="stat-icon bg-blue-light">🛏️</div>
          <div class="stat-info">
            <h3><?= $kamar_aktif ?></h3>
            <p>Kamar Aktif</p>
          </div>
        </div>

        <div class="stat-card <?= ($tagihan_pending > 0) ? 'red' : 'green' ?>">
          <div class="stat-icon <?= ($tagihan_pending > 0) ? 'bg-red-light' : 'bg-green-light' ?>">💳</div>
          <div class="stat-info">
            <h3><?= $tagihan_pending ?></h3>
            <p>Tagihan Belum Dibayar</p>
          </div>
        </div>

        <div class="stat-card yellow">
          <div class="stat-icon bg-yellow-light">📢</div>
          <div class="stat-info">
            <h3>Info</h3>
            <p>Cek Pengumuman</p>
          </div>
        </div>
    </div>

    <div class="data-section">
        <div class="section-header">
            <h3>Akses Cepat</h3>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
            <a href="kamar_saya.php" class="btn-solid btn-blue" style="text-align:center; padding:1.5rem;">
                <div style="font-size:2rem; margin-bottom:10px;">🏠</div>
                Lihat Kamar Saya
            </a>
            <a href="tagihan_saya.php" class="btn-solid btn-green" style="text-align:center; padding:1.5rem;">
                <div style="font-size:2rem; margin-bottom:10px;">💰</div>
                Bayar Tagihan
            </a>
            <a href="keluhan.php" class="btn-solid btn-red" style="text-align:center; padding:1.5rem;">
                <div style="font-size:2rem; margin-bottom:10px;">🔧</div>
                Lapor Kerusakan
            </a>
        </div>
    </div>

  </main>

</body>
</html>
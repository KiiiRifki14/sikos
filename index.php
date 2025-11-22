<?php
session_start();
require 'inc/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS - Kost Paadaasih</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>

<header class="header">
   <div class="container">
    <nav class="nav-wrapper">
     <a href="index.php" class="logo-section">
      <svg class="logo" viewBox="0 0 100 100" style="border-radius:8px;">
        <rect width="100" height="100" fill="#1e88e5"/>
        <path d="M 50 20 L 80 50 L 20 50 Z" fill="#ffc107"/>
        <rect x="30" y="50" width="40" height="30" fill="white"/>
      </svg>
      <div class="brand-text">
       <h1>SIKOS</h1>
       <p>Paadaasih</p>
      </div>
     </a>
     
     <ul class="nav-menu">
      <li><a href="index.php">Beranda</a></li>
      <li><a href="#kamar">Kamar</a></li>
      <li><a href="#fasilitas">Fasilitas</a></li>
      
      <?php if (isset($_SESSION['id_pengguna'])): ?>
          <?php if($_SESSION['peran'] == 'ADMIN'): ?>
            <li><a href="admin/index.php" class="btn-login">Dashboard Admin</a></li>
          <?php elseif($_SESSION['peran'] == 'OWNER'): ?>
            <li><a href="owner_dashboard.php" class="btn-login">Dashboard Owner</a></li>
          <?php else: ?>
            <li><a href="penghuni_dashboard.php" class="btn-login">Dashboard Saya</a></li>
          <?php endif; ?>
      <?php else: ?>
          <li><a href="login.php" class="btn-login">🔐 Login</a></li>
      <?php endif; ?>
     </ul>
    </nav>
   </div>
</header>

<section class="hero">
    <div class="container">
     <h2>Temukan Kamar Kost Impian Anda</h2>
     <p>Pilih dari berbagai tipe kamar dengan fasilitas lengkap di lokasi strategis Cimahi, Bandung</p>
    </div>
</section>

<div class="container" id="kamar">
    <div class="section-title">
     <h3>Kamar Tersedia</h3>
     <p>Berbagai pilihan kamar sesuai kebutuhan Anda</p>
    </div>

    <div class="rooms-grid">
    <?php
    // Ambil data kamar dari database
    $res = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC");
    
    while($row = $res->fetch_assoc()){
      $statusClass = ($row['status_kamar'] == 'TERSEDIA') ? 'status-available' : 'status-occupied';
      $statusText  = ($row['status_kamar'] == 'TERSEDIA') ? '✓ Tersedia' : '● Terisi';
    ?>
     <div class="room-card">
      <div class="room-image">
       <?php if($row['foto_cover']): ?>
         <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" style="width:100%; height:100%; object-fit:cover;">
       <?php else: ?>
         🛏️
       <?php endif; ?>
      </div>
      <div class="room-content">
       <div class="room-header">
        <div class="room-code"><?= htmlspecialchars($row['kode_kamar']) ?></div>
        <div class="room-type"><?= htmlspecialchars($row['nama_tipe']) ?></div>
       </div>
       <div class="room-price">
        Rp <?= number_format($row['harga'], 0, ',', '.') ?> <span>/bulan</span>
       </div>
       <div class="room-status <?= $statusClass ?>">
        <?= $statusText ?>
       </div>
       <a href="detail_kamar.php?id=<?= $row['id_kamar'] ?>" class="button btn-detail">Lihat Detail</a>
      </div>
     </div>
    <?php } ?>
    </div>
</div>

<section class="container" id="fasilitas" style="margin-bottom:4rem;">
    <div class="section-title">
      <h3>Fasilitas Umum</h3>
    </div>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; text-align:center;">
        <div style="background:white; padding:2rem; border-radius:10px; width:150px;">📶 WiFi</div>
        <div style="background:white; padding:2rem; border-radius:10px; width:150px;">🅿️ Parkir</div>
        <div style="background:white; padding:2rem; border-radius:10px; width:150px;">🍳 Dapur</div>
        <div style="background:white; padding:2rem; border-radius:10px; width:150px;">🔐 CCTV</div>
    </div>
</section>

<footer class="footer">
   <div class="container">
    <div class="footer-grid">
     <div>
      <h4>SIKOS - Kost Paadaasih</h4>
      <p>Kost modern dan nyaman di lokasi strategis Cimahi.</p>
     </div>
     <div>
      <h4>Kontak Kami</h4>
      <p>📍 Jl. Paadaasih No. 123, Cimahi</p>
      <p>📞 +62 812-3456-7890</p>
     </div>
    </div>
    <div class="copyright">
     <p>&copy; 2025 SIKOS Paadaasih. All rights reserved.</p>
    </div>
   </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
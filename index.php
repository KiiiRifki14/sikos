<?php
session_start();
require 'inc/koneksi.php';

// Ambil data kamar
$query = "SELECT k.*, t.nama_tipe FROM kamar k 
          JOIN tipe_kamar t ON k.id_tipe=t.id_tipe 
          ORDER BY k.status_kamar ASC, k.kode_kamar ASC";
$res = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS - Sistem Informasi Kost Paadaasih</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>

  <nav class="nav">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
            <span>🏠 SIKOS</span>
        </a>

        <div class="nav-links">
            <a href="#beranda" class="nav-link">Beranda</a>
            <a href="#kamar" class="nav-link">Kamar</a>
            <a href="#fasilitas" class="nav-link">Fasilitas</a>
            
            <?php if (isset($_SESSION['id_pengguna'])): ?>
                <?php 
                  $dashboard = 'penghuni_dashboard.php';
                  if($_SESSION['peran'] == 'ADMIN') $dashboard = 'admin/index.php';
                  if($_SESSION['peran'] == 'OWNER') $dashboard = 'owner_dashboard.php';
                ?>
                <a href="<?= $dashboard ?>" class="btn btn-primary">Dashboard Saya</a>
                <a href="logout.php" class="nav-link" style="color:#ef4444;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Masuk</a>
                <a href="register.php" class="btn btn-primary">Daftar Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
  </nav>

  <section id="beranda" class="hero">
      <h1>Temukan Kos Impian Anda</h1>
      <p>Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.</p>
      <div style="display:flex; gap:15px; justify-content:center;">
          <a href="#kamar" class="btn btn-primary">🔍 Cari Kamar</a>
          <a href="#" class="btn btn-secondary">📞 Hubungi Kami</a>
      </div>
  </section>

  <div class="container" id="kamar">
      <div style="margin-bottom: 40px;">
          <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Kamar Tersedia</h2>
          <p style="color: #64748b;">Pilih kamar yang sesuai dengan kebutuhan Anda</p>
      </div>

      <div class="grid-3">
        <?php while($row = $res->fetch_assoc()){ 
            $isAvailable = ($row['status_kamar'] == 'TERSEDIA');
        ?>
        <article class="room-card">
            <div class="room-image">
                <?php if($row['foto_cover']): ?>
                    <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" alt="Kamar">
                <?php else: ?>
                    <span>🛏️</span>
                <?php endif; ?>
                
                <?php if($isAvailable): ?>
                    <div class="badge text-green">✓ TERSEDIA</div>
                <?php else: ?>
                    <div class="badge text-red">⛔ TERISI</div>
                <?php endif; ?>
            </div>

            <div class="room-content">
                <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                    <?= htmlspecialchars($row['nama_tipe']) ?>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
                    Kamar <?= htmlspecialchars($row['kode_kamar']) ?>
                </h3>
                
                <div class="room-price">
                    Rp <?= number_format($row['harga'], 0, ',', '.') ?> <span>/bulan</span>
                </div>

                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <span style="background: #f1f5f9; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; color: #64748b;">
                        📐 <?= $row['luas_m2'] ?> m²
                    </span>
                    <span style="background: #f1f5f9; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; color: #64748b;">
                        🏢 Lantai <?= $row['lantai'] ?>
                    </span>
                </div>

                <a href="detail_kamar.php?id=<?= $row['id_kamar'] ?>" 
                   class="btn <?= $isAvailable ? 'btn-primary' : 'btn-secondary' ?>" 
                   style="width: 100%;">
                   <?= $isAvailable ? 'Lihat Detail & Booking' : 'Tidak Tersedia' ?>
                </a>
            </div>
        </article>
        <?php } ?>
      </div>
  </div>

  <div class="container" id="fasilitas" style="text-align: center; padding-bottom: 80px;">
      <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 40px;">Fasilitas Umum</h2>
      <div class="grid-3">
          <div class="room-card" style="padding: 30px;">
              <div style="font-size: 3rem; margin-bottom: 15px;">📶</div>
              <h3 style="font-weight: 700; margin-bottom: 10px;">WiFi Cepat</h3>
              <p style="color: #64748b;">Koneksi internet stabil untuk kebutuhan kerja dan belajar.</p>
          </div>
          <div class="room-card" style="padding: 30px;">
              <div style="font-size: 3rem; margin-bottom: 15px;">🅿️</div>
              <h3 style="font-weight: 700; margin-bottom: 10px;">Parkir Luas</h3>
              <p style="color: #64748b;">Area parkir motor dan mobil yang aman dan tertata.</p>
          </div>
          <div class="room-card" style="padding: 30px;">
              <div style="font-size: 3rem; margin-bottom: 15px;">🔐</div>
              <h3 style="font-weight: 700; margin-bottom: 10px;">Keamanan CCTV</h3>
              <p style="color: #64748b;">Pengawasan 24 jam untuk keamanan dan kenyamanan Anda.</p>
          </div>
      </div>
  </div>

  <footer style="background: white; border-top: 1px solid #e2e8f0; padding: 40px 0; text-align: center; color: #64748b;">
      <p>&copy; 2025 SIKOS Paadaasih. All rights reserved.</p>
  </footer>

</body>
</html>
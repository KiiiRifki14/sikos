<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Pastikan koneksi sudah di-load. 
// Gunakan require_once agar tidak error jika sudah diload di header
require_once __DIR__ . '/../inc/koneksi.php';

$db_footer = new Database();
$app = $db_footer->ambil_pengaturan();
?>
<footer class="footer">
  <div class="footer-grid">
    <div>
      <div style="font-size:24px; font-weight:700; color:white; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
        <div style="width:32px; height:32px; background:var(--primary); border-radius:6px; display:flex; align-items:center; justify-content:center;">S</div> 
        <?= htmlspecialchars($app['nama_kos']) ?>
      </div>
      <p style="line-height:1.6; font-size:14px;">
        Platform penyewaan kost modern yang mengutamakan kenyamanan dan keamanan penghuni dengan sistem digital yang terintegrasi.
      </p>
    </div>
    
    <div>
      <h4>KONTAK</h4>
      <ul>
        <li>📍 <?= htmlspecialchars($app['alamat']) ?></li>
        <li>📞 <?= htmlspecialchars($app['no_hp']) ?></li>
        <li>✉️ <?= htmlspecialchars($app['email']) ?></li>
      </ul>
    </div>
    
    <div>
      <h4>MENU</h4>
      <ul>
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="#">Syarat & Ketentuan</a></li> 
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($app['nama_kos']) ?>. All rights reserved.
  </div>
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</footer>
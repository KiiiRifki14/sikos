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
      <p style="line-height:1.6; font-size:14px; margin-bottom:20px;">
        <?= htmlspecialchars($app['deskripsi_footer'] ?? 'Platform penyewaan kost modern...') ?>
      </p>
      <div class="flex gap-4">
        <?php if(!empty($app['link_fb']) && $app['link_fb'] != '#'): ?>
            <a href="<?= htmlspecialchars($app['link_fb']) ?>" target="_blank" style="color:var(--text-muted); font-size:18px;"><i class="fa-brands fa-facebook"></i></a>
        <?php endif; ?>
        <?php if(!empty($app['link_ig']) && $app['link_ig'] != '#'): ?>
            <a href="<?= htmlspecialchars($app['link_ig']) ?>" target="_blank" style="color:var(--text-muted); font-size:18px;"><i class="fa-brands fa-instagram"></i></a>
        <?php endif; ?>
      </div>
    </div>
    
    <div>
      <h4>KONTAK</h4>
      <ul>
        <li>📍 <?= htmlspecialchars($app['alamat']) ?></li>
        <li>📞 <a href="https://wa.me/<?= htmlspecialchars($app['no_wa'] ?? '62881011201664') ?>" target="_blank"><?= htmlspecialchars($app['no_wa'] ?? '628xxx') ?></a></li>
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
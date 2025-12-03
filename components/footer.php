<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Load Settings
$file_settings = __DIR__ . '/../inc/settings_data.json';
$app = [
    'nama_kos' => 'SIKOS Paadaasih',
    'alamat' => 'Jl. Paadaasih No. 123, Bandung',
    'no_hp' => '0812-3456-7890',
    'email' => 'help@sikos.com'
];
if (file_exists($file_settings)) {
    $app = array_merge($app, json_decode(file_get_contents($file_settings), true));
}
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
        <li><a href="login.php">Login Admin</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($app['nama_kos']) ?>. All rights reserved.
  </div>
</footer>
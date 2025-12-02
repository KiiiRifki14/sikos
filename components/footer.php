<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// LOAD SETTINGS (Sama seperti di kuitansi)
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
<footer class="bg-slate-900 text-slate-300 pt-16 pb-8">
  <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
    <div class="col-span-1 md:col-span-2">
      <div class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">S</div> <?= htmlspecialchars($app['nama_kos']) ?>
      </div>
      <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
        Platform penyewaan kost modern yang mengutamakan kenyamanan dan keamanan penghuni dengan sistem digital yang terintegrasi.
      </p>
    </div>
    <div>
      <h4 class="text-white font-bold mb-4">Kontak</h4>
      <ul class="space-y-3 text-sm text-slate-400">
        <li>📍 <?= htmlspecialchars($app['alamat']) ?></li>
        <li>📞 <?= htmlspecialchars($app['no_hp']) ?></li>
        <li>✉️ <?= htmlspecialchars($app['email']) ?></li>
      </ul>
    </div>
    <div>
      <h4 class="text-white font-bold mb-4">Menu</h4>
      <ul class="space-y-3 text-sm text-slate-400">
        <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
        <li><a href="#" class="hover:text-white">Syarat & Ketentuan</a></li>
        <li><a href="login.php" class="hover:text-white">Login Admin</a></li>
      </ul>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-6 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($app['nama_kos']) ?>. All rights reserved.
  </div>
</footer>
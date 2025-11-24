<?php
session_start();
require 'inc/koneksi.php';
// Ambil data kamar
$res = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.status_kamar ASC, k.kode_kamar ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS Paadaasih</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="bg-slate-50">

  <nav class="fixed top-0 left-0 w-full bg-white/95 backdrop-blur border-b border-slate-100 z-50 h-20 flex items-center">
    <div class="w-full max-w-7xl mx-auto px-6 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2 group">
           <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">S</div>
           <div class="flex flex-col">
             <span class="text-xl font-bold text-slate-800 leading-none">SIKOS</span>
             <span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Paadaasih</span>
           </div>
        </a>

        <div class="hidden md:flex items-center gap-8">
            <a href="#beranda" class="text-sm font-medium text-slate-600 hover:text-blue-600">Beranda</a>
            <a href="#kamar" class="text-sm font-medium text-slate-600 hover:text-blue-600">Kamar</a>
            <a href="#fasilitas" class="text-sm font-medium text-slate-600 hover:text-blue-600">Fasilitas</a>
            
            <div class="h-6 w-px bg-slate-200 mx-2"></div>

            <?php if (isset($_SESSION['id_pengguna'])): ?>
                <?php 
                  $dash = ($_SESSION['peran'] == 'PENGHUNI') ? 'penghuni_dashboard.php' : 'admin/index.php';
                ?>
                <a href="<?= $dash ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-lg shadow-blue-200 transition">
                  Dashboard
                </a>
                <a href="logout.php" class="border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                  Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-6 py-2.5 rounded-lg text-sm font-bold transition">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
  </nav>

  <section id="beranda" class="hero-container mb-16">
      <div class="hero-box">
          <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-6">
              Temukan Kos Impian Anda
          </h1>
          <p class="text-lg text-slate-500 mb-10 max-w-2xl mx-auto">
              Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.
          </p>
          <div class="flex justify-center gap-4">
              <a href="#kamar" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">
                  🔍 Cari Kamar
              </a>
              <a href="https://wa.me/6281234567890" target="_blank" class="bg-white border border-slate-200 text-slate-700 px-8 py-3 rounded-xl font-bold hover:bg-slate-50 transition flex items-center gap-2">
                  📞 Hubungi Kami
              </a>
          </div>
      </div>
  </section>

  <section id="kamar" class="max-w-7xl mx-auto px-6 mb-20">
      <div class="flex items-end justify-between mb-8">
          <div>
            <h2 class="text-2xl font-bold text-slate-900">Kamar Tersedia</h2>
            <p class="text-slate-500">Pilih kamar yang sesuai dengan kebutuhan Anda</p>
          </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php while($row = $res->fetch_assoc()){ ?>
        <div class="card-white group hover:shadow-xl transition duration-300">
            <div class="h-60 bg-slate-100 relative overflow-hidden">
                <?php if($row['foto_cover']): ?>
                    <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-4xl text-slate-300">🏠</div>
                <?php endif; ?>
                
                <div class="absolute top-4 right-4">
                    <?php if($row['status_kamar'] == 'TERSEDIA'): ?>
                        <span class="status-tersedia badge-status">✓ Tersedia</span>
                    <?php else: ?>
                        <span class="status-terisi badge-status">✕ Terisi</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-6">
                <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1"><?= htmlspecialchars($row['nama_tipe']) ?></div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h3>
                
                <div class="flex items-baseline gap-1 mb-4">
                    <span class="text-2xl font-bold text-blue-600">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                    <span class="text-sm text-slate-400">/bulan</span>
                </div>

                <div class="flex gap-3 text-xs text-slate-500 mb-6">
                    <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">📐 <?= $row['luas_m2'] ?> m²</span>
                    <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">⚡ Listrik</span>
                    <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">🚿 Dalam</span>
                </div>
                
                <?php if($row['status_kamar'] == 'TERSEDIA'): ?>
                    <a href="detail_kamar.php?id=<?= $row['id_kamar'] ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-xl font-bold transition">
                        Lihat Detail & Pesan
                    </a>
                <?php else: ?>
                    <button disabled class="block w-full bg-slate-100 text-slate-400 text-center py-3 rounded-xl font-bold cursor-not-allowed">
                        Tidak Tersedia
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php } ?>
      </div>
  </section>

  <footer class="bg-slate-900 text-slate-300 pt-16 pb-8">
      <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
          <div class="col-span-1 md:col-span-2">
              <div class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                  <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">S</div> SIKOS
              </div>
              <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                  Platform penyewaan kost modern yang mengutamakan kenyamanan dan keamanan penghuni dengan sistem digital yang terintegrasi.
              </p>
          </div>
          <div>
              <h4 class="text-white font-bold mb-4">Kontak</h4>
              <ul class="space-y-3 text-sm text-slate-400">
                  <li>📍 Jl. Paadaasih No. 123, Bandung</li>
                  <li>📞 0812-3456-7890</li>
                  <li>✉️ help@sikos.com</li>
              </ul>
          </div>
          <div>
              <h4 class="text-white font-bold mb-4">Menu</h4>
              <ul class="space-y-3 text-sm text-slate-400">
                  <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
                  <li><a href="#" class="hover:text-white">Syarat & Ketentuan</a></li>
                  <li><a href="#" class="hover:text-white">Kebijakan Privasi</a></li>
              </ul>
          </div>
      </div>
      
      <div class="max-w-7xl mx-auto px-6 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
          &copy; 2025 SIKOS Paadaasih. All rights reserved. Made with ❤️ in Bandung.
      </div>
  </footer>

</body>
</html>
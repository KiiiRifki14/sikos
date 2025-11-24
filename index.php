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
  <title>SIKOS - Landing Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>

  <nav class="bg-white/95 backdrop-blur-sm fixed w-full z-50 border-b border-gray-100 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20"> <a href="index.php" class="flex items-center gap-3 group">
           <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-200 group-hover:scale-105 transition-transform duration-300">
             S
           </div>
           <div class="flex flex-col">
             <span class="text-xl font-bold text-gray-800 leading-none group-hover:text-blue-600 transition-colors">SIKOS</span>
             <span class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mt-1">Paadaasih</span>
           </div>
        </a>
        
        <div class="hidden md:flex items-center gap-8">
          <a href="index.php" class="text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors">
            Beranda
          </a>
          <a href="#kamar" class="text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors">
            Kamar
          </a>
          <a href="#fasilitas" class="text-sm font-semibold text-gray-600 hover:text-blue-600 transition-colors">
            Fasilitas
          </a>
          
          <div class="flex items-center gap-3 pl-4 border-l border-gray-200 ml-2">
            <?php if (isset($_SESSION['id_pengguna'])): ?>
                <?php 
                  $dashboard = 'penghuni_dashboard.php';
                  if($_SESSION['peran'] == 'ADMIN') $dashboard = 'admin/index.php';
                  if($_SESSION['peran'] == 'OWNER') $dashboard = 'owner_dashboard.php';
                ?>
                
                <a href="<?= $dashboard ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-full text-sm font-semibold shadow-md shadow-blue-200 transition-all hover:-translate-y-0.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                  </svg>
                  Dashboard
                </a>

                <a href="logout.php" class="flex items-center gap-2 text-gray-500 hover:text-red-600 hover:bg-red-50 px-4 py-2.5 rounded-full text-sm font-semibold transition-colors border border-transparent hover:border-red-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
                </a>

            <?php else: ?>
                <a href="login.php" class="text-sm font-bold text-gray-700 hover:text-blue-600 transition-colors px-3">
                    Masuk
                </a>
                <a href="register.php" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-full text-sm font-semibold shadow-lg shadow-gray-200 transition-all hover:-translate-y-0.5">
                    Daftar Sekarang
                </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="md:hidden flex items-center">
            <button class="text-gray-500 hover:text-blue-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

      </div>
    </div>
  </nav>

  <div class="container">
   <section class="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden mb-12">
    <div class="relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-blue-800">
            Temukan Kos Impian Anda
        </h1>
        <p class="text-gray-500 text-lg mb-8 max-w-2xl mx-auto">
            Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.
        </p>
        <div class="flex gap-4 justify-center">
            <a href="#kamar" class="btn btn-primary">🔍 Cari Kamar</a>
            <a href="#" class="btn btn-secondary">📞 Hubungi Kami</a>
        </div>
    </div>
   </section>

   <div id="kamar">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Kamar Tersedia</h2>
            <p class="text-muted">Pilih kamar yang sesuai dengan kebutuhan Anda</p>
        </div>
    </div>
    
    <div class="grid grid-3">
        <?php while($row = $res->fetch_assoc()){ 
            $tersedia = ($row['status_kamar'] == 'TERSEDIA');
        ?>
        <article class="room-card group">
            <div class="room-image">
                <?php if($row['foto_cover']): ?>
                    <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" alt="Foto Kamar">
                <?php else: ?>
                    <span class="text-6xl">🛏️</span>
                <?php endif; ?>
                
                <?php if($tersedia): ?>
                    <span class="badge badge-success">✓ TERSEDIA</span>
                <?php else: ?>
                    <span class="badge badge-danger">✕ TERISI</span>
                <?php endif; ?>
            </div>
            
            <div class="room-content">
                <div class="text-xs font-bold text-blue-600 mb-1 uppercase tracking-wider">
                    <?= htmlspecialchars($row['nama_tipe']) ?>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h3>
                <div class="room-price">
                    Rp <?= number_format($row['harga'], 0, ',', '.') ?> <span>/bulan</span>
                </div>
                
                <div class="flex gap-2 mb-4 text-xs text-gray-500">
                    <span class="bg-gray-100 px-2 py-1 rounded">📏 <?= $row['luas_m2'] ?> m²</span>
                    <span class="bg-gray-100 px-2 py-1 rounded">Lantai <?= $row['lantai'] ?></span>
                </div>

                <a href="detail_kamar.php?id=<?= $row['id_kamar'] ?>" class="btn <?= $tersedia ? 'btn-primary' : 'btn-secondary' ?> w-full">
                    <?= $tersedia ? 'Lihat Detail & Booking' : 'Lihat Detail' ?>
                </a>
            </div>
        </article>
        <?php } ?>
    </div>
   </div>
   
   <div class="mt-20 grid grid-3 text-center">
       <div class="card">
           <div class="text-4xl mb-4">🔒</div>
           <h3 class="font-bold mb-2">Keamanan 24/7</h3>
           <p class="text-muted">Dilengkapi CCTV dan penjaga untuk keamanan maksimal.</p>
       </div>
       <div class="card">
           <div class="text-4xl mb-4">📍</div>
           <h3 class="font-bold mb-2">Lokasi Strategis</h3>
           <p class="text-muted">Dekat dengan kampus, pusat perbelanjaan dan akses tol.</p>
       </div>
       <div class="card">
           <div class="text-4xl mb-4">✨</div>
           <h3 class="font-bold mb-2">Fasilitas Lengkap</h3>
           <p class="text-muted">WiFi, dapur bersama, parkir luas tersedia untuk Anda.</p>
       </div>
   </div>

  </div> <footer class="mt-12 py-8 border-t border-gray-200 bg-white text-center text-muted">
    <p>&copy; 2025 SIKOS Paadaasih. All rights reserved.</p>
  </footer>

</body>
</html>
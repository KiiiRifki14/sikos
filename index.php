<?php
session_start();
require 'inc/koneksi.php';

// Ambil data kamar dari database
$query = "SELECT k.*, t.nama_tipe 
          FROM kamar k 
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .hero-pattern {
        background-color: #3b82f6;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <nav class="bg-white/90 backdrop-blur-md fixed w-full z-50 shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" onclick="window.location='index.php'">
           <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg">
             S
           </div>
           <div>
             <h1 class="text-xl font-bold text-gray-800 leading-none">SIKOS</h1>
             <p class="text-xs text-gray-500 font-medium">Paadaasih</p>
           </div>
        </div>
        
        <div class="hidden md:flex items-center space-x-8">
          <a href="#beranda" class="text-gray-600 hover:text-blue-600 font-medium transition">Beranda</a>
          <a href="#kamar" class="text-gray-600 hover:text-blue-600 font-medium transition">Kamar</a>
          <a href="#fasilitas" class="text-gray-600 hover:text-blue-600 font-medium transition">Fasilitas</a>
          
          <?php if (isset($_SESSION['id_pengguna'])): ?>
              <?php 
                $dashboard = 'penghuni_dashboard.php';
                if($_SESSION['peran'] == 'ADMIN') $dashboard = 'admin/index.php';
                if($_SESSION['peran'] == 'OWNER') $dashboard = 'owner_dashboard.php';
              ?>
              <a href="<?= $dashboard ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-full font-semibold transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                Dashboard Saya
              </a>
          <?php else: ?>
              <a href="login.php" class="text-blue-600 font-semibold hover:text-blue-700">Masuk</a>
              <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-full font-semibold transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                Daftar Sekarang
              </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <section id="beranda" class="hero-pattern pt-32 pb-20 px-4 sm:px-6 lg:px-8 text-center text-white relative overflow-hidden">
    <div class="relative z-10 max-w-4xl mx-auto">
      <span class="bg-white/20 text-white text-sm font-semibold px-4 py-1.5 rounded-full inline-block mb-6 backdrop-blur-sm border border-white/30">
        ✨ Kost Nyaman, Harga Teman
      </span>
      <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
        Temukan Hunian Impian<br>Di Lokasi Strategis
      </h2>
      <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
        Nikmati pengalaman nge-kost dengan fasilitas lengkap, keamanan 24 jam, dan lingkungan yang nyaman di Cimahi.
      </p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="#kamar" class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold px-8 py-4 rounded-xl transition shadow-lg transform hover:-translate-y-1">
          Cari Kamar
        </a>
        <a href="https://wa.me/6281234567890" target="_blank" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl backdrop-blur-md border border-white/30 transition">
          Hubungi Admin
        </a>
      </div>
    </div>
  </section>

  <section id="kamar" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h3 class="text-3xl font-bold text-gray-900 mb-4">Pilihan Kamar</h3>
      <div class="w-20 h-1.5 bg-blue-600 mx-auto rounded-full"></div>
      <p class="text-gray-600 mt-4 text-lg">Sesuaikan dengan budget dan kebutuhan Anda</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php while($row = $res->fetch_assoc()){ 
        $isAvailable = ($row['status_kamar'] == 'TERSEDIA');
    ?>
      <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition duration-300 border border-gray-100 group">
        
        <div class="relative h-64 overflow-hidden">
           <?php if($row['foto_cover']): ?>
             <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
           <?php else: ?>
             <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">
                <span class="text-4xl">🏠</span>
             </div>
           <?php endif; ?>
           
           <div class="absolute top-4 right-4">
             <?php if($isAvailable): ?>
                <span class="bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">TERSEDIA</span>
             <?php else: ?>
                <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">TERISI</span>
             <?php endif; ?>
           </div>
           
           <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur text-gray-900 font-bold px-4 py-2 rounded-lg shadow-sm">
             Rp <?= number_format($row['harga'], 0, ',', '.') ?> <span class="text-xs font-normal text-gray-500">/bulan</span>
           </div>
        </div>

        <div class="p-6">
          <div class="flex justify-between items-start mb-2">
            <div>
                <p class="text-blue-600 text-sm font-semibold uppercase tracking-wide"><?= htmlspecialchars($row['nama_tipe']) ?></p>
                <h4 class="text-xl font-bold text-gray-900">Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h4>
            </div>
            <div class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">
                Lt. <?= $row['lantai'] ?>
            </div>
          </div>
          
          <div class="flex items-center gap-4 text-gray-500 text-sm mb-6 mt-4">
            <span class="flex items-center gap-1">📏 <?= $row['luas_m2'] ?> m²</span>
            <span class="flex items-center gap-1">⚡ 1300 VA</span>
            <span class="flex items-center gap-1">🚿 Dalam</span>
          </div>

          <a href="detail_kamar.php?id=<?= $row['id_kamar'] ?>" 
             class="block w-full text-center py-3 rounded-xl font-bold transition
             <?= $isAvailable 
                ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-200 shadow-lg' 
                : 'bg-gray-100 text-gray-400 cursor-not-allowed' ?>">
             <?= $isAvailable ? 'Lihat & Booking' : 'Tidak Tersedia' ?>
          </a>
        </div>
      </div>
    <?php } ?>
    </div>
  </section>

  <section id="fasilitas" class="py-20 bg-gray-50">
     <div class="max-w-7xl mx-auto px-4 text-center">
        <h3 class="text-3xl font-bold text-gray-900 mb-12">Fasilitas Umum</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="text-4xl mb-3">📶</div>
                <h4 class="font-bold text-gray-800">WiFi Kencang</h4>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="text-4xl mb-3">🅿️</div>
                <h4 class="font-bold text-gray-800">Parkir Luas</h4>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="text-4xl mb-3">🍳</div>
                <h4 class="font-bold text-gray-800">Dapur Bersama</h4>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="text-4xl mb-3">🔐</div>
                <h4 class="font-bold text-gray-800">CCTV 24 Jam</h4>
            </div>
        </div>
     </div>
  </section>

  <footer class="bg-slate-900 text-slate-300 py-12 mt-auto">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
      <div>
        <h4 class="text-white text-xl font-bold mb-4">SIKOS Paadaasih</h4>
        <p class="text-sm opacity-80 leading-relaxed">
            Menyediakan hunian kost yang nyaman, aman, dan terjangkau bagi mahasiswa dan karyawan di area Cimahi.
        </p>
      </div>
      <div>
        <h4 class="text-white text-xl font-bold mb-4">Kontak</h4>
        <ul class="space-y-2 text-sm">
            <li>📍 Jl. Paadaasih No. 123, Cimahi</li>
            <li>📞 +62 812-3456-7890</li>
            <li>✉️ info@sikos.com</li>
        </ul>
      </div>
      <div>
        <h4 class="text-white text-xl font-bold mb-4">Akses Cepat</h4>
        <ul class="space-y-2 text-sm">
            <li><a href="login.php" class="hover:text-blue-400 transition">Login Penghuni</a></li>
            <li><a href="register.php" class="hover:text-blue-400 transition">Daftar Akun</a></li>
        </ul>
      </div>
    </div>
    <div class="text-center border-t border-slate-800 mt-10 pt-6 text-sm opacity-50">
        &copy; 2025 SIKOS Paadaasih. All rights reserved.
    </div>
  </footer>

</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="fixed top-0 left-0 w-full bg-white/95 backdrop-blur border-b border-slate-100 z-50 h-20 flex items-center">
  <div class="w-full max-w-7xl mx-auto px-6 flex justify-between items-center">
    <a href="index.php" class="flex items-center gap-2 group">
      <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl" aria-label="Logo SIKOS">S</div>
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
        <?php $dash = ($_SESSION['peran'] == 'PENGHUNI') ? 'penghuni_dashboard.php' : 'admin/index.php'; ?>
        <a href="<?= $dash ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-lg shadow-blue-200 transition">Dashboard</a>
        <a href="logout.php" class="border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">Logout</a>
      <?php else: ?>
        <a href="login.php" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-6 py-2.5 rounded-lg text-sm font-bold transition">Login</a>
      <?php endif; ?>
    </div>

    <!-- Mobile: hamburger -->
    <button class="md:hidden text-slate-700 hover:text-blue-600 text-2xl" aria-label="Buka menu" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">☰</button>
  </div>

  <!-- Mobile menu -->
  <div id="mobileMenu" class="md:hidden hidden bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col gap-3">
      <a href="#beranda" class="text-sm font-medium text-slate-700 hover:text-blue-600">Beranda</a>
      <a href="#kamar" class="text-sm font-medium text-slate-700 hover:text-blue-600">Kamar</a>
      <a href="#fasilitas" class="text-sm font-medium text-slate-700 hover:text-blue-600">Fasilitas</a>
      <div class="h-px w-full bg-slate-200 my-2"></div>
      <?php if (isset($_SESSION['id_pengguna'])): ?>
        <?php $dash = ($_SESSION['peran'] == 'PENGHUNI') ? 'penghuni_dashboard.php' : 'admin/index.php'; ?>
        <a href="<?= $dash ?>" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold">Dashboard</a>
        <a href="logout.php" class="border border-red-200 bg-red-50 text-red-600 px-5 py-2 rounded-lg text-sm font-semibold">Logout</a>
      <?php else: ?>
        <a href="login.php" class="bg-blue-50 text-blue-600 px-5 py-2 rounded-lg text-sm font-bold">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
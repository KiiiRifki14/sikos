<?php
// Tentukan menu aktif otomatis berdasarkan nama file script saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<aside class="sidebar">
    <div class="mb-8 px-2 flex items-center gap-3" style="margin-top: 40px;"> 
        <div>
            <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            <p class="text-xs text-slate-400">Management Panel</p>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto;">
        <a href="index.php" class="sidebar-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie w-6 text-blue-500"></i> Dashboard
        </a>
        <a href="kamar_data.php" class="sidebar-link <?= strpos($current_page, 'kamar') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-house-chimney w-6 text-orange-500"></i> Kelola Kamar
        </a>
        <a href="booking_data.php" class="sidebar-link <?= strpos($current_page, 'booking') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> Booking
        </a>
        <a href="keuangan_index.php" class="sidebar-link <?= ($current_page == 'keuangan_index.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-sack-dollar w-6 text-yellow-500"></i> Keuangan
        </a>
        <a href="penghuni_data.php" class="sidebar-link <?= strpos($current_page, 'penghuni') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni
        </a>
        <a href="keluhan_data.php" class="sidebar-link <?= strpos($current_page, 'keluhan') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain
        </a>
        <a href="pengumuman_data.php" class="sidebar-link <?= strpos($current_page, 'pengumuman') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-bullhorn w-6 text-cyan-500"></i> Pengumuman
        </a>
        <a href="pengeluaran_data.php" class="sidebar-link <?= ($current_page == 'pengeluaran_data.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-wallet w-6 text-red-500"></i> Pengeluaran
        </a>
        <a href="fasilitas_data.php" class="sidebar-link <?= ($current_page == 'fasilitas_data.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-list-check w-6 text-green-500"></i> Fasilitas
        </a>
        <a href="log_aktivitas.php" class="sidebar-link <?= ($current_page == 'log_aktivitas.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-clock-rotate-left w-6 text-gray-500"></i> Log Aktivitas
        </a>
        <a href="settings.php" class="sidebar-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan
        </a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4 mb-6">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
</aside>
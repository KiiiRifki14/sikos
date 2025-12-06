<?php
// Tentukan menu aktif otomatis berdasarkan nama file script saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Tombol Hamburger Mobile -->
<button class="sidebar-toggle" onclick="toggleSidebar()" aria-controls="sidebar" aria-expanded="false">
    <i class="fa-solid fa-bars"></i>
</button>

<aside id="sidebar" class="sidebar">
    <!-- Header Sidebar -->
    <div class="mb-8 px-2 flex items-center gap-3" style="margin-top: 40px;"> 
        <div>
            <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            <p class="text-xs text-slate-400">Management Panel</p>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto;">
        
        <!-- KATEGORI: UTAMA -->
        <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin: 16px 16px 8px; letter-spacing:0.5px;">
            Menu Utama
        </div>

        <a href="index.php" class="sidebar-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <div class="flex items-center gap-3 w-full">
                <i class="fa-solid fa-chart-pie w-6 text-blue-500"></i> 
                <span>Dashboard</span>
            </div>
        </a>
        <a href="kamar_data.php" class="sidebar-link <?= strpos($current_page, 'kamar') !== false ? 'active' : '' ?>">
            <div class="flex items-center gap-3 w-full">
                <i class="fa-solid fa-house-chimney w-6 text-orange-500"></i> 
                <span>Kelola Kamar</span>
            </div>
        </a>
        
        <?php 
            $count_booking = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0]; 
        ?>
        <a href="booking_data.php" class="sidebar-link <?= strpos($current_page, 'booking') !== false ? 'active' : '' ?>">
            <div class="flex items-center gap-3 w-full">
                <i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> 
                <span>Booking</span>
            </div>
            <?php if($count_booking > 0): ?>
                <span class="badge-notif"><?= $count_booking ?></span>
            <?php endif; ?>
        </a>

        <!-- KATEGORI: KEUANGAN (DROPDOWN) -->
        <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin: 24px 16px 8px; letter-spacing:0.5px;">
            Keuangan
        </div>

        <?php
            // Hitung Pending Pembayaran
            $c_bayar = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0]; 
            // Hitung Tagihan Belum Lunas
            $c_tagih = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE status='BELUM'")->fetch_row()[0];
            
            // Total Notif Keuangan (untuk badge parent)
            $total_keuangan = $c_bayar + $c_tagih;
        ?>

        <!-- Tombol Dropdown -->
        <div class="sidebar-dropdown">
            <button class="sidebar-link w-full justify-between <?= ($current_page == 'keuangan_index.php') ? 'active' : '' ?>" 
                    onclick="this.nextElementSibling.classList.toggle('show'); this.querySelector('.arrow').classList.toggle('rotate');">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-sack-dollar w-6 text-green-600"></i> 
                    <span>Pusat Keuangan</span>
                </div>
                <!-- Badge Total di Parent Menu -->
                <?php if($total_keuangan > 0): ?>
                    <span class="badge-notif" style="margin-right:8px;"><?= $total_keuangan ?></span>
                <?php endif; ?>
                <i class="fa-solid fa-chevron-down arrow text-xs transition-transform"></i>
            </button>
            
            <div class="dropdown-content <?= ($current_page == 'keuangan_index.php') ? 'show' : '' ?>">
                <a href="keuangan_index.php?tab=verifikasi" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'verifikasi') ? 'text-blue-600 font-bold bg-blue-50' : '' ?>">
                    <div class="flex items-center gap-2 w-full">
                        <i class="fa-solid fa-check-double w-4"></i> 
                        <span>Verifikasi Bayar</span>
                    </div>
                    <?php if($c_bayar > 0): ?>
                        <span class="badge-notif"><?= $c_bayar ?></span>
                    <?php endif; ?>
                </a>
                <a href="keuangan_index.php?tab=tagihan" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'tagihan') ? 'text-blue-600 font-bold bg-blue-50' : '' ?>">
                    <div class="flex items-center gap-2 w-full">
                        <i class="fa-solid fa-file-invoice w-4"></i> 
                        <span>Kelola Tagihan</span>
                    </div>
                    <?php if($c_tagih > 0): ?>
                        <span class="badge-notif"><?= $c_tagih ?></span>
                    <?php endif; ?>
                </a>
                <a href="keuangan_index.php?tab=pengeluaran" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'pengeluaran') ? 'text-blue-600 font-bold bg-blue-50' : '' ?>">
                    <i class="fa-solid fa-wallet w-4"></i> Pengeluaran
                </a>
                <a href="keuangan_index.php?tab=laporan" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'laporan') ? 'text-blue-600 font-bold bg-blue-50' : '' ?>">
                    <i class="fa-solid fa-chart-line w-4"></i> Laporan
                </a>
            </div>
        </div>

        <!-- KATEGORI: PENGHUNI & KOMPLAIN -->
        <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin: 24px 16px 8px; letter-spacing:0.5px;">
            Administrasi
        </div>

        <a href="penghuni_data.php" class="sidebar-link <?= strpos($current_page, 'penghuni') !== false ? 'active' : '' ?>">
            <div class="flex items-center gap-3 w-full">
                <i class="fa-solid fa-users w-6 text-purple-500"></i> 
                <span>Penghuni</span>
            </div>
        </a>
        
        <?php 
            $count_keluhan = $mysqli->query("SELECT COUNT(*) FROM keluhan WHERE status='BARU'")->fetch_row()[0]; 
        ?>
        <a href="keluhan_data.php" class="sidebar-link <?= strpos($current_page, 'keluhan') !== false ? 'active' : '' ?>">
            <div class="flex items-center gap-3 w-full">
                <i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> 
                <span>Komplain</span>
            </div>
            <?php if($count_keluhan > 0): ?>
                <span class="badge-notif"><?= $count_keluhan ?></span>
            <?php endif; ?>
        </a>
        
        <!-- KATEGORI: SYSTEM -->
        <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin: 24px 16px 8px; letter-spacing:0.5px;">
            Lainnya
        </div>

        <a href="settings.php" class="sidebar-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan
        </a>
        <a href="log_aktivitas.php" class="sidebar-link <?= ($current_page == 'log_aktivitas.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-clock-rotate-left w-6 text-gray-500"></i> Log Aktivitas
        </a>
        <a href="fasilitas_data.php" class="sidebar-link <?= strpos($current_page, 'fasilitas') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-list-check w-6 text-teal-500"></i> Master Fasilitas
        </a>

    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-6 mb-4" style="border-top: 1px solid #e2e8f0; padding-top: 16px;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>

    <style>
        /* CSS Inline Khusus Sidebar Dropdown */
        .dropdown-content {
            display: none;
            padding-left: 34px; /* Indentasi */
            background: #f8fafc;
            border-radius: 0 0 8px 8px;
            margin-top: -4px;
            margin-bottom: 4px;
        }
        .dropdown-content.show { display: block; }
        
        .sidebar-sublink {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px;
            font-size: 13px; color: var(--text-muted);
            text-decoration: none;
            border-left: 2px solid transparent;
            transition: 0.2s;
        }
        .sidebar-sublink:hover { color: var(--primary); border-left-color: var(--primary); background: #eff6ff; }
        
        .arrow.rotate { transform: rotate(180deg); }
        .w-full { width: 100%; }
        .justify-between { justify-content: space-between; }
        
        /* Override tombol sidebar agar bisa jadi button */
        button.sidebar-link {
            background: none; border: none; width: 100%; text-align: left; cursor: pointer;
        }
    </style>
</aside>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mobile Hamburger -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<aside id="sidebar" class="sidebar">
    <!-- Header -->
    <div style="padding: 30px 24px 20px; text-align: left;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fa-solid fa-building-user text-xl"></i>
            </div>
            <div>
                <h1 style="color: white; font-size: 18px; margin: 0; font-weight: 700;">SIKOS Admin</h1>
                <p style="color: rgba(255,255,255,0.5); font-size: 11px; margin: 0;">Management Panel</p>
            </div>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto; padding-bottom: 20px;">
        
        <div style="padding: 16px 24px 8px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;">
            Menu Utama
        </div>

        <a href="index.php" class="sidebar-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>
        <a href="kamar_data.php" class="sidebar-link <?= strpos($current_page, 'kamar') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-house-chimney"></i>
            <span>Kelola Kamar</span>
        </a>
        
        <?php 
            $count_booking = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0]; 
        ?>
        <a href="booking_data.php" class="sidebar-link <?= strpos($current_page, 'booking') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Booking</span>
            <?php if($count_booking > 0): ?>
                <span class="badge-notif"><?= $count_booking ?></span>
            <?php endif; ?>
        </a>

        <!-- KATEGORI: KEUANGAN -->
        <div style="padding: 24px 24px 8px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;">
            Keuangan
        </div>

        <?php
            // Hitung Pending Pembayaran & Tagihan
            $c_bayar = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0]; 
            $c_tagih = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE status='BELUM'")->fetch_row()[0];
            $total_keuangan = $c_bayar + $c_tagih;
        ?>

        <div class="sidebar-dropdown">
            <button class="sidebar-link w-full justify-between <?= ($current_page == 'keuangan_index.php') ? 'active' : '' ?>" 
                    onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate');">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-sack-dollar"></i> 
                    <span>Pusat Keuangan</span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if($total_keuangan > 0): ?>
                        <span class="badge-notif" style="background: var(--warning); color: black;"><?= $total_keuangan ?></span>
                    <?php endif; ?>
                    <i class="fa-solid fa-chevron-down arrow text-xs"></i>
                </div>
            </button>
            
            <div class="dropdown-content <?= ($current_page == 'keuangan_index.php') ? 'open' : '' ?>">
                <a href="keuangan_index.php?tab=verifikasi" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'verifikasi') ? 'active' : '' ?>">
                    <span>Verifikasi Bayar</span>
                    <?php if($c_bayar > 0): ?>
                        <span class="badge-notif"><?= $c_bayar ?></span>
                    <?php endif; ?>
                </a>
                <a href="keuangan_index.php?tab=tagihan" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'tagihan') ? 'active' : '' ?>">
                    <span>Kelola Tagihan</span>
                    <?php if($c_tagih > 0): ?>
                        <span class="badge-notif"><?= $c_tagih ?></span>
                    <?php endif; ?>
                </a>
                <a href="keuangan_index.php?tab=pengeluaran" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'pengeluaran') ? 'active' : '' ?>">
                    <span>Pengeluaran</span>
                </a>
                <a href="keuangan_index.php?tab=laporan" class="sidebar-sublink <?= (isset($_GET['tab']) && $_GET['tab'] == 'laporan') ? 'active' : '' ?>">
                    <span>Laporan</span>
                </a>
            </div>
        </div>

        <!-- ADMINISTRASI -->
        <div style="padding: 24px 24px 8px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;">
            Administrasi
        </div>

        <a href="penghuni_data.php" class="sidebar-link <?= strpos($current_page, 'penghuni') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Penghuni</span>
        </a>
        
        <?php 
            $count_keluhan = $mysqli->query("SELECT COUNT(*) FROM keluhan WHERE status='BARU'")->fetch_row()[0]; 
        ?>
        <a href="keluhan_data.php" class="sidebar-link <?= strpos($current_page, 'keluhan') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Komplain</span>
            <?php if($count_keluhan > 0): ?>
                <span class="badge-notif"><?= $count_keluhan ?></span>
            <?php endif; ?>
        </a>

        <!-- SYSTEM -->
        <div style="padding: 24px 24px 8px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;">
            Lainnya
        </div>

        <a href="edit_landing.php" class="sidebar-link <?= $current_page == 'edit_landing.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-laptop-house"></i>
            <span>Edit Landing Page</span>
        </a>

        <a href="settings.php" class="sidebar-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-sliders"></i>
            <span>Pengaturan</span>
        </a>
        <a href="log_aktivitas.php" class="sidebar-link <?= ($current_page == 'log_aktivitas.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Log Aktivitas</span>
        </a>
        <a href="fasilitas_data.php" class="sidebar-link <?= strpos($current_page, 'fasilitas') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-list-check"></i>
            <span>Master Fasilitas</span>
        </a>

    </nav>

    <div style="padding: 24px; border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="../logout.php" class="btn btn-danger w-full text-sm" style="justify-content: center;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>
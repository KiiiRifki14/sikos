<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Pastikan path koneksi benar
require_once __DIR__ . '/../inc/koneksi.php';

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$page = basename($_SERVER['PHP_SELF']);

// Inject class khusus ke body agar CSS terpisah dari admin
echo '<script>document.body.classList.add("role-penghuni");</script>';

global $mysqli;
if(!isset($mysqli)) { 
    $db = new Database(); 
    $mysqli = $db->koneksi; 
}

$q_user = "SELECT u.nama, p.foto_profil 
           FROM pengguna u 
           LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna 
           WHERE u.id_pengguna = $id_pengguna";
$user_sb = $mysqli->query($q_user)->fetch_assoc();
$nama_user = htmlspecialchars($user_sb['nama'] ?? 'Pengguna');
// Gunakan path absolut web root untuk gambar agar aman di subfolder
$foto_user = !empty($user_sb['foto_profil']) ? "assets/uploads/profil/".$user_sb['foto_profil'] : "assets/img/avatar.png"; 
?>

<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="mobile-header">
    <button class="mobile-toggle-btn" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="mobile-user-info">
        <span class="mobile-user-name"><?= explode(' ', $nama_user)[0] ?></span>
        <img src="<?= $foto_user ?>" alt="Foto" class="mobile-user-img">
    </div>
</div>

<aside id="sidebar" class="sidebar-penghuni">
    <div class="sidebar-profile">
        <div class="profile-img-box">
            <?php if(!empty($user_sb['foto_profil'])): ?>
                <img src="<?= $foto_user ?>" alt="Foto">
            <?php else: ?>
                <div class="initial-avatar"><?= strtoupper(substr($nama_user, 0, 1)) ?></div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h4 title="<?= $nama_user ?>"><?= $nama_user ?></h4>
            <p>Penghuni Kost</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        
        <a href="penghuni_dashboard.php" class="nav-link <?= $page=='penghuni_dashboard.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-house"></i></div>
            <span>Dashboard</span>
        </a>
        <a href="kamar_saya.php" class="nav-link <?= $page=='kamar_saya.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-bed"></i></div>
            <span>Kamar Saya</span>
        </a>
        <a href="tagihan_saya.php" class="nav-link <?= $page=='tagihan_saya.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <span>Tagihan</span>
        </a>
        <a href="keluhan.php" class="nav-link <?= $page=='keluhan.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <span>Lapor Keluhan</span>
        </a>
        <a href="pengumuman.php" class="nav-link <?= $page=='pengumuman.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-bullhorn"></i></div>
            <span>Info & Pengumuman</span>
        </a>

        <div class="nav-label mt-4">Akun</div>
        
        <a href="profil.php" class="nav-link <?= $page=='profil.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-user-gear"></i></div>
            <span>Edit Profil</span>
        </a>
        <a href="logout.php" class="nav-link text-red">
            <div class="icon-wrap"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <span>Keluar</span>
        </a>
    </nav>
</aside>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }
</script>
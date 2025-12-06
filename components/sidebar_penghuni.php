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

// Hitung Tagihan Belum Bayar
$count_tagihan = $mysqli->query("SELECT COUNT(*) FROM tagihan t 
    JOIN kontrak k ON t.id_kontrak = k.id_kontrak 
    WHERE k.status='AKTIF' AND t.status='BELUM' 
    AND k.id_penghuni = (SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna)")->fetch_row()[0];
?>

<!-- Tombol Toggle Desktop -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

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
        <a href="profil.php" class="profile-img-box" style="display:block; position:relative; cursor:pointer;" title="Klik untuk Ganti Foto">
            <?php if(!empty($user_sb['foto_profil'])): ?>
                <img src="<?= $foto_user ?>" alt="Foto">
            <?php else: ?>
                <div class="initial-avatar"><?= strtoupper(substr($nama_user, 0, 1)) ?></div>
            <?php endif; ?>
            
            <div style="position:absolute; bottom:0; right:0; background:#2563eb; color:white; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <i class="fa-solid fa-pencil" style="font-size:10px;"></i>
            </div>
        </a>
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
            <?php if($count_tagihan > 0): ?>
                <span class="badge-notif"><?= $count_tagihan ?></span>
            <?php endif; ?>
        </a>
<?php
        // Hitung Keluhan "PROSES" (Sedang dikerjakan admin)
        $c_keluhan = $mysqli->query("SELECT COUNT(*) FROM keluhan WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) AND status='PROSES'")->fetch_row()[0];
        
        // Hitung Pengumuman Baru (7 Hari Terakhir)
        $c_info = $mysqli->query("SELECT COUNT(*) FROM pengumuman WHERE is_aktif=1 AND aktif_mulai >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_row()[0];
        ?>
        
        <a href="keluhan.php" class="nav-link <?= $page=='keluhan.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <span>Lapor Keluhan</span>
            <?php if($c_keluhan > 0): ?>
                <span class="badge-notif"><?= $c_keluhan ?></span>
            <?php endif; ?>
        </a>
        <a href="pengumuman.php" class="nav-link <?= $page=='pengumuman.php'?'active':'' ?>">
            <div class="icon-wrap"><i class="fa-solid fa-bullhorn"></i></div>
            <span>Info & Pengumuman</span>
            <?php if($c_info > 0): ?>
                <span class="badge-notif" style="background:#f59e0b;">New</span>
            <?php endif; ?>
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
        // Toggle untuk Mobile
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
        
        // Toggle untuk Desktop (Memperluas konten)
        document.body.classList.toggle('sidebar-collapsed');
    }
</script>
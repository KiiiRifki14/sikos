<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Pastikan path koneksi benar relatif terhadap file yang memanggil
require_once __DIR__ . '/../inc/koneksi.php';

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
$page = basename($_SERVER['PHP_SELF']);

// Tambah class scoping ke body jika peran PENGHUNI
if (isset($_SESSION['peran']) && $_SESSION['peran'] === 'PENGHUNI') {
    echo '<script>document.addEventListener("DOMContentLoaded", function(){ document.body.classList.add("role-penghuni"); });</script>';
}

global $mysqli;
if(!isset($mysqli)) { 
    $db = new Database(); 
    $mysqli = $db->koneksi; 
}

$q_user = "SELECT u.nama, p.foto_profil 
           FROM pengguna u 
           LEFT JOIN penghuni p ON u.id_pengguna = p.id_pengguna 
           WHERE u.id_pengguna = $id_pengguna";
$user_sidebar = $mysqli->query($q_user)->fetch_assoc();
?>

<!-- Tombol toggle selalu ada (hamburger) agar konsisten di semua halaman penghuni -->
<button class="sidebar-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
    <i class="fa-solid fa-bars" aria-hidden="true"></i>
</button>

<aside id="sidebar" class="sidebar" aria-label="Sidebar Penghuni">
    <div class="mb-8 flex items-center gap-3">
        <div style="width:40px; height:40px; border-radius:50%; overflow:hidden; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-weight:bold; border:1px solid #e2e8f0;">
            <?php if(!empty($user_sidebar['foto_profil'])): ?>
                <img src="assets/uploads/profil/<?= htmlspecialchars($user_sidebar['foto_profil']) ?>" alt="Foto profil <?= htmlspecialchars($user_sidebar['nama'] ?? 'Pengguna') ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <?= htmlspecialchars(substr($user_sidebar['nama'] ?? 'U',0,1)) ?>
            <?php endif; ?>
        </div>
        <div>
            <div style="font-weight:700; color:#1e293b; font-size:14px;"><?= htmlspecialchars($user_sidebar['nama'] ?? 'Pengguna') ?></div>
            <div style="font-size:12px; color:#64748b;">Penghuni</div>
        </div>
    </div>

    <nav style="flex:1;" role="navigation" aria-label="Menu Penghuni">
        <a href="penghuni_dashboard.php" class="sidebar-link <?= $page=='penghuni_dashboard.php'?'active':'' ?>">
            <i class="fa-solid fa-chart-pie w-6"></i> Dashboard
        </a>
        <a href="kamar_saya.php" class="sidebar-link <?= $page=='kamar_saya.php'?'active':'' ?>">
            <i class="fa-solid fa-bed w-6"></i> Kamar Saya
        </a>
        <a href="tagihan_saya.php" class="sidebar-link <?= $page=='tagihan_saya.php'?'active':'' ?>">
            <i class="fa-solid fa-credit-card w-6"></i> Tagihan
        </a>
        <a href="keluhan.php" class="sidebar-link <?= $page=='keluhan.php'?'active':'' ?>">
            <i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan
        </a>
        <a href="pengumuman.php" class="sidebar-link <?= $page=='pengumuman.php'?'active':'' ?>">
            <i class="fa-solid fa-bullhorn w-6"></i> Info
        </a>
        <a href="profil.php" class="sidebar-link <?= $page=='profil.php'?'active':'' ?>">
            <i class="fa-solid fa-user-gear w-6"></i> Profil Saya
        </a>
    </nav>

    <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
</aside>
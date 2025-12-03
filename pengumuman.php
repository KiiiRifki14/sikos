<?php
session_start();
require 'inc/koneksi.php';

// Cek Login
if (!isset($_SESSION['id_pengguna'])) { 
    header('Location: login.php'); 
    exit; 
}

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengumuman - SIKOS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">
      <i class="fa-solid fa-bars"></i>
  </button>

  <aside class="sidebar">
    <div class="mb-8 flex items-center gap-3 mt-10 md:mt-0">
        <div style="width:40px; height:40px; background:#eff6ff; color:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
            <?= substr($user['nama'],0,1) ?>
        </div>
        <div>
            <div class="font-bold text-sm"><?= htmlspecialchars($user['nama']) ?></div>
            <div class="text-xs text-muted">Penghuni</div>
        </div>
    </div>

    <nav style="flex:1;">
        <a href="penghuni_dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-pie w-6"></i> Dashboard</a>
        <a href="kamar_saya.php" class="sidebar-link"><i class="fa-solid fa-bed w-6"></i> Kamar Saya</a>
        <a href="tagihan_saya.php" class="sidebar-link"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
        <a href="keluhan.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
        <a href="pengumuman.php" class="sidebar-link active"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
        <a href="profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear w-6"></i> Profil Saya</a>
    </nav>

    <a href="logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Papan Pengumuman</h1>
        <p class="text-xs text-muted">Informasi terbaru seputar kosan.</p>
    </div>

    <div style="display:grid; gap:20px;">
        <?php
        // Ambil pengumuman yang aktif
        $stmt = $mysqli->prepare("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=? ORDER BY aktif_mulai DESC");
        $stmt->bind_param('ss', $today, $today);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
        ?>
            <div class="card-white" style="border-left: 4px solid var(--primary);">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg" style="color: var(--primary);">
                        <i class="fa-solid fa-thumbtack mr-2"></i><?= htmlspecialchars($row['judul']) ?>
                    </h3>
                    <span class="text-xs font-bold" style="background:#eff6ff; color:var(--primary); padding:4px 8px; border-radius:4px;">INFO</span>
                </div>
                
                <div class="text-sm mb-4" style="color: var(--text-main); line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($row['isi'])) ?>
                </div>
                
                <div class="text-xs text-muted border-t pt-3 flex items-center gap-2" style="border-color: var(--border);">
                    <i class="fa-regular fa-clock"></i> Diposting: <?= date('d M Y', strtotime($row['aktif_mulai'])) ?>
                </div>
            </div>
        <?php 
            }
        } else {
            // Jika kosong
            echo '
            <div class="card-white text-center py-10">
                <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                <p class="text-muted">Belum ada pengumuman aktif saat ini.</p>
            </div>';
        }
        ?>
    </div>
  </main>

</body>
</html>
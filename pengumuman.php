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
    <meta charset="utf-8">
    <title>Pengumuman - SIKOS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
      <i class="fa-solid fa-bars"></i>
  </button>

  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content" aria-labelledby="announcements-heading">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <h1 id="announcements-heading" class="font-bold text-xl" style="margin:0;">Papan Pengumuman</h1>
            <p style="color:#64748b; margin:0; font-size:14px;">Informasi terbaru seputar kosan.</p>
        </div>
        <div>
            <a href="#" class="btn btn-primary">Buat Pengumuman</a>
        </div>
    </div>

    <div style="display:grid; gap:16px;">
        <?php
        // Ambil pengumuman yang aktif
        $stmt = $mysqli->prepare("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=? ORDER BY aktif_mulai DESC");
        $stmt->bind_param('ss', $today, $today);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
        ?>
            <article class="card-white" style="border-left: 4px solid var(--primary);" aria-labelledby="p<?=$row['id_pengumuman']?>">
                <div style="display:flex; justify-content:space-between; align-items:start; gap:12px;">
                    <div style="flex:1;">
                        <h3 id="p<?=$row['id_pengumuman']?>" class="font-bold text-lg" style="color: var(--primary); margin-bottom:6px;">
                            <i class="fa-solid fa-thumbtack mr-2" aria-hidden="true"></i><?= htmlspecialchars($row['judul']) ?>
                        </h3>
                        <div class="text-sm" style="color: var(--text-main); line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($row['isi'])) ?>
                        </div>
                    </div>
                    <div style="min-width:120px; text-align:right;">
                        <span class="text-xs font-bold" style="background:#eff6ff; color:var(--primary); padding:6px 10px; border-radius:6px; display:inline-block;">INFO</span>
                        <div style="font-size:12px; color:#64748b; margin-top:10px;">Diposting: <?= date('d M Y', strtotime($row['aktif_mulai'])) ?></div>
                    </div>
                </div>
            </article>
        <?php 
            }
        } else {
            // Jika kosong
            echo '
            <div class="card-white text-center py-10">
                <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                <p class="text-muted" style="color:#64748b;">Belum ada pengumuman aktif saat ini.</p>
            </div>';
        }
        ?>
    </div>
  </main>

</body>
</html>
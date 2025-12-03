<?php
session_start();
require 'inc/koneksi.php';
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengumuman - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <aside class="sidebar">
    <div class="mb-8 flex items-center gap-3">
        <div style="width:40px; height:40px; background:#eff6ff; color:#2563eb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
            <?= substr($user['nama'],0,1) ?>
        </div>
        <div>
            <div style="font-weight:700; color:#1e293b; font-size:14px;"><?= htmlspecialchars($user['nama']) ?></div>
            <div style="font-size:12px; color:#64748b;">Penghuni</div>
        </div>
    </div>
    <nav style="flex:1;">
        <a href="penghuni_dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-pie w-6"></i> Dashboard</a>
        <a href="kamar_saya.php" class="sidebar-link"><i class="fa-solid fa-bed w-6"></i> Kamar Saya</a>
        <a href="tagihan_saya.php" class="sidebar-link"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
        <a href="keluhan.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
        <a href="pengumuman.php" class="sidebar-link"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
        <a href="profil.php" class="sidebar-link active"><i class="fa-solid fa-user-gear w-6"></i> Profil Saya</a>
    </nav>
    <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h2 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:24px;">Papan Pengumuman</h2>

    <div style="display:grid; gap:24px;">
        <?php
        $stmt = $mysqli->prepare("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=? ORDER BY aktif_mulai DESC");
        $stmt->bind_param('ss', $today, $today);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
        ?>
            <div class="card-white" style="border-left:4px solid #2563eb;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h3 style="font-weight:700; color:#1e293b; font-size:18px;"><?= htmlspecialchars($row['judul']) ?></h3>
                    <span style="background:#eff6ff; color:#2563eb; padding:4px 12px; border-radius:99px; font-size:11px; font-weight:700;">INFO</span>
                </div>
                <p style="color:#64748b; line-height:1.6; font-size:14px;">
                    <?= nl2br(htmlspecialchars($row['isi'])) ?>
                </p>
                <div style="margin-top:16px; font-size:12px; color:#94a3b8;">
                    Diposting: <?= date('d M Y', strtotime($row['aktif_mulai'])) ?>
                </div>
            </div>
        <?php 
            }
        } else {
            echo '<div class="card-white" style="text-align:center; color:#94a3b8; padding:40px;">Tidak ada pengumuman aktif saat ini.</div>';
        }
        ?>
    </div>
  </main>
</body>
</html>
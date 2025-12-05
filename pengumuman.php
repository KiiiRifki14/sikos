<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengumuman</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content">
    
    <div class="mb-6">
        <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Papan Pengumuman</h1>
        <p style="font-size:13px; color:#64748b;">Informasi terbaru dari pengelola kost.</p>
    </div>

    <div style="display:flex; flex-direction:column; gap:15px;">
        <?php
        $res = $mysqli->query("SELECT * FROM pengumuman WHERE is_aktif=1 ORDER BY aktif_mulai DESC");
        if($res->num_rows > 0):
            while($r = $res->fetch_assoc()):
        ?>
            <div class="card-white" style="padding:25px; position:relative; overflow:hidden; border-left:5px solid #3b82f6;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                    <h3 style="font-size:18px; font-weight:700; color:#1e293b; margin:0;"><?= htmlspecialchars($r['judul']) ?></h3>
                    <span style="background:#f1f5f9; color:#64748b; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600;">
                        <i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y', strtotime($r['aktif_mulai'])) ?>
                    </span>
                </div>
                <div style="font-size:14px; color:#475569; line-height:1.6; white-space:pre-wrap;"><?= htmlspecialchars($r['isi']) ?></div>
            </div>
        <?php endwhile; else: ?>
            <div class="card-white" style="text-align:center; padding:50px;">
                <i class="fa-solid fa-folder-open" style="font-size:40px; color:#cbd5e1; margin-bottom:15px;"></i>
                <p style="color:#64748b;">Tidak ada pengumuman saat ini.</p>
            </div>
        <?php endif; ?>
    </div>

  </main>
</body>
</html>
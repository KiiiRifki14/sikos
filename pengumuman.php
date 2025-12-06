<?php
session_start();
require 'inc/koneksi.php';
if (!isset($_SESSION['id_pengguna'])) { header('Location: login.php'); exit; }
$id_pengguna = $_SESSION['id_pengguna'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengumuman</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pengumuman-item {
            background: white; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 20px; margin-bottom: 15px; border-left: 4px solid #2563eb;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s;
        }
        .pengumuman-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .p-header { display: flex; justify-content: space-between; margin-bottom: 10px; align-items: flex-start; }
        .p-title { font-weight: 700; font-size: 16px; color: #1e293b; margin: 0; }
        .p-date { font-size: 11px; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; color: #64748b; white-space: nowrap; }
        .p-body { font-size: 14px; color: #475569; line-height: 1.6; }
    </style>
</head>
<body class="role-penghuni">
  <?php include 'components/sidebar_penghuni.php'; ?>
  <main class="main-content animate-fade-up">

    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Papan Pengumuman</h1>
        <p style="font-size: 13px; color: #64748b;">Info terbaru dari pengelola kost.</p>
    </div>

    <div style="max-width: 800px;">
        <?php
        $res = $mysqli->query("SELECT * FROM pengumuman WHERE is_aktif=1 ORDER BY aktif_mulai DESC");
        if($res->num_rows > 0):
            while($r = $res->fetch_assoc()):
        ?>
            <div class="pengumuman-item">
                <div class="p-header">
                    <h3 class="p-title"><?= htmlspecialchars($r['judul']) ?></h3>
                    <span class="p-date"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($r['aktif_mulai'])) ?></span>
                </div>
                <div class="p-body"><?= nl2br(htmlspecialchars($r['isi'])) ?></div>
            </div>
        <?php endwhile; else: ?>
            <div class="card-white" style="text-align:center; padding:40px; color:#94a3b8;">
                <i class="fa-regular fa-folder-open" style="font-size:32px; margin-bottom:10px;"></i><br>
                Tidak ada pengumuman saat ini.
            </div>
        <?php endif; ?>
    </div>

  </main>
</body>
</html>
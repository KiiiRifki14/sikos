<?php
session_start();
require 'inc/koneksi.php';
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;
$kontrak = null;
if($id_penghuni) {
    $kontrak = $mysqli->query("SELECT k.*, km.kode_kamar, km.harga FROM kontrak k JOIN kamar km ON k.id_kamar=km.id_kamar WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF'")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Dashboard Penghuni</title>
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
            <a href="penghuni_dashboard.php" class="sidebar-link active"><i class="fa-solid fa-chart-pie w-6"></i> Dashboard</a>
            <a href="kamar_saya.php" class="sidebar-link"><i class="fa-solid fa-bed w-6"></i> Kamar Saya</a>
            <a href="tagihan_saya.php" class="sidebar-link"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
            <a href="keluhan.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
            <a href="pengumuman.php" class="sidebar-link"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
        </nav>

        <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
            <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
        </a>
    </aside>

    <main class="main-content">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:8px;">Selamat Datang!</h1>
        <p style="color:#64748b; margin-bottom:32px; font-size:14px;">Berikut ringkasan status sewa Anda hari ini.</p>

        <div class="grid-stats">
            <div class="card-white">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Status Kontrak</div>
                <?php if($kontrak): ?>
                    <div style="font-size:28px; font-weight:700; color:#16a34a; margin-bottom:4px;">AKTIF</div>
                    <div style="font-size:13px; color:#64748b;">Berakhir: <?= date('d M Y', strtotime($kontrak['tanggal_selesai'])) ?></div>
                <?php else: ?>
                    <div style="font-size:24px; font-weight:700; color:#94a3b8;">Tidak Aktif</div>
                <?php endif; ?>
            </div>

            <div class="card-white">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Kamar Anda</div>
                <div style="font-size:28px; font-weight:700; color:#1e293b; margin-bottom:4px;"><?= $kontrak['kode_kamar'] ?? '-' ?></div>
                <div style="font-size:13px; color:#64748b;">
                    <?= $kontrak ? 'Rp '.number_format($kontrak['harga']).'/bulan' : '-' ?>
                </div>
            </div>

            <div class="card-white">
                <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Tagihan Pending</div>
                <?php 
                    $tagihan = 0;
                    if($kontrak) $tagihan = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} AND status='BELUM'")->fetch_row()[0];
                ?>
                <div style="font-size:28px; font-weight:700; color: <?= $tagihan>0 ? '#f59e0b' : '#2563eb' ?>;">
                    <?= $tagihan ?>
                </div>
                <div style="font-size:13px; color:#64748b;">Perlu dibayar</div>
            </div>
        </div>

        <div class="card-white">
            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:16px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
                Tagihan Terbaru
            </h3>
            
            <?php if($tagihan > 0): ?>
                <div style="background:#fffbeb; color:#b45309; padding:16px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:14px;">Anda memiliki <b><?= $tagihan ?></b> tagihan yang belum dibayar.</span>
                    <a href="tagihan_saya.php" class="btn-primary" style="text-decoration:none;">Bayar Sekarang</a>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#94a3b8; padding:20px; font-size:14px;">Tidak ada tagihan pending. Terima kasih!</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
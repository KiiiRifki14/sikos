<?php
session_start();
require 'inc/koneksi.php';
require 'inc/utils.php';

// Cek Login & Peran
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') { 
    header('Location: login.php'); exit; 
}

$id_pengguna = $_SESSION['id_pengguna'];
// Ambil data user
$user = $mysqli->query("SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
// Ambil data penghuni & kontrak aktif
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;

$kontrak = null;
if($id_penghuni) {
    $kontrak = $mysqli->query("SELECT k.*, km.kode_kamar, km.harga FROM kontrak k JOIN kamar km ON k.id_kamar=km.id_kamar WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF'")->fetch_assoc();
}

// LOGIKA NOTIFIKASI (BANNER)
$notif_banner = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'pembayaran_berhasil') {
        $notif_banner = '
        <div role="status" aria-live="polite" style="background-color: #dcfce7; border: 1px solid #22c55e; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; display:flex; align-items:center; gap:12px;">
            <i class="fa-solid fa-circle-check" style="font-size:20px;"></i>
            <div>
                <strong style="display:block; margin-bottom:2px;">Pembayaran Berhasil Diupload!</strong>
                <span style="font-size:14px;">Bukti transfer Anda sudah masuk. Mohon tunggu verifikasi admin 1x24 jam.</span>
            </div>
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Penghuni</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

<?php include 'components/sidebar_penghuni.php'; ?>

<main class="main-content" aria-labelledby="welcome-heading">
    <?= $notif_banner ?>

    <header style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px;">
        <div>
            <h1 id="welcome-heading" style="font-size:22px; font-weight:700; color:#1e293b; margin-bottom:6px;">Selamat Datang, <?= htmlspecialchars($user['nama']) ?></h1>
            <p style="color:#64748b; margin:0; font-size:14px;">Ringkasan singkat status sewa & tindakan cepat untuk penghuni.</p>
        </div>

        <div style="display:flex; gap:8px; align-items:center;">
            <a href="tagihan_saya.php" class="btn btn-secondary" style="padding:8px 12px; display:inline-flex; align-items:center;">
                <i class="fa-solid fa-credit-card mr-2"></i> Tagihan
            </a>
            <a href="keluhan.php" class="btn btn-primary" style="padding:8px 12px; display:inline-flex; align-items:center;">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> Ajukan Keluhan
            </a>
        </div>
    </header>

    <section class="grid-stats" aria-label="Ringkasan">
        <div class="card-white" role="article" aria-labelledby="contract-status">
            <div id="contract-status" style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Status Kontrak</div>
            <?php if($kontrak): ?>
                <div style="font-size:26px; font-weight:800; color:#16a34a; margin-top:8px; margin-bottom:6px;">AKTIF</div>
                <div style="font-size:13px; color:#64748b;">Berakhir: <strong><?= date('d M Y', strtotime($kontrak['tanggal_selesai'])) ?></strong></div>
                <div style="margin-top:12px;"><a href="kamar_saya.php" class="btn btn-secondary">Lihat Kamar Saya</a></div>
            <?php else: ?>
                <div style="font-size:22px; font-weight:700; color:#94a3b8; margin-top:8px;">Tidak Aktif</div>
                <div style="margin-top:12px;"><a href="index.php#kamar" class="btn btn-primary">Cari Kamar</a></div>
            <?php endif; ?>
        </div>

        <div class="card-white" role="article" aria-labelledby="my-room">
            <div id="my-room" style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Kamar Anda</div>
            <div style="font-size:26px; font-weight:800; color:#1e293b; margin-top:8px;"><?= $kontrak['kode_kamar'] ?? '-' ?></div>
            <div style="font-size:13px; color:#64748b; margin-top:6px;">
                <?= $kontrak ? 'Rp '.number_format($kontrak['harga']).'/bulan' : '-' ?>
            </div>
        </div>

        <div class="card-white" role="article" aria-labelledby="pending-bills">
            <div id="pending-bills" style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Tagihan Pending</div>
            <?php 
                $tagihan = 0;
                if($kontrak) {
                    $id_k = $kontrak['id_kontrak'];
                    $tagihan = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE id_kontrak=$id_k AND status='BELUM'")->fetch_row()[0];
                }
            ?>
            <div style="font-size:28px; font-weight:800; color: <?= $tagihan>0 ? '#f59e0b' : '#2563eb' ?>; margin-top:8px;"><?= $tagihan ?></div>
            <div style="font-size:13px; color:#64748b; margin-top:6px;">Perlu dibayar</div>
            <?php if($tagihan>0): ?>
                <div style="margin-top:12px;"><a href="tagihan_saya.php" class="btn btn-primary">Bayar Sekarang</a></div>
            <?php endif; ?>
        </div>
    </section>

    <section style="margin-top:18px;" aria-label="Info & Pengumuman">
        <div class="card-white" style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="font-size:16px; font-weight:700; color:#1e293b;">Informasi Terbaru</h2>
                <a href="pengumuman.php" class="text-sm" style="color:var(--primary); text-decoration:none;">Lihat Semua →</a>
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <?php
                // ambil 3 pengumuman terbaru
                $res = $mysqli->query("SELECT id_pengumuman, judul, aktif_mulai FROM pengumuman WHERE is_aktif=1 ORDER BY aktif_mulai DESC LIMIT 3");
                if($res->num_rows > 0){
                    while($r = $res->fetch_assoc()){
                        echo '<div style="min-width:200px; flex:1; max-width:360px;">
                                <div style="padding:12px; border-radius:10px; background:#f8fafc;">
                                  <div style="font-weight:700; color:#0f172a;">'.htmlspecialchars($r['judul']).'</div>
                                  <div style="font-size:12px; color:#64748b; margin-top:6px;">'.date('d M Y',strtotime($r['aktif_mulai'])).'</div>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div style="color:#64748b;">Belum ada pengumuman.</div>';
                }
                ?>
            </div>
        </div>
    </section>
</main>

</body>
</html>
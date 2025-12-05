<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php'); exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;

// Ambil data kontrak & kamar
$row_kontrak = $mysqli->query("SELECT * FROM kontrak WHERE id_penghuni=$id_penghuni AND status='AKTIF'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kamar Saya - SIKOS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content" aria-labelledby="room-heading">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px;">
        <div>
            <h1 id="room-heading" style="font-size:22px; font-weight:700; color:#1e293b;">Informasi Kamar</h1>
            <p style="color:#64748b; margin:0; font-size:14px;">Detail kontrak dan fasilitas kamar Anda.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="cetak_kontrak.php?id=<?= $id_penghuni ?>&token=<?= md5('kemanan_sederhana'.$id_penghuni) ?>" target="_blank" class="btn btn-secondary" style="padding:8px 12px;">
                <i class="fa-solid fa-print mr-1"></i> Cetak Kontrak
            </a>
            <a href="keluhan.php" class="btn btn-primary" style="padding:8px 12px;">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Laporkan Masalah
            </a>
        </div>
    </div>

    <?php if (!$row_kontrak) { ?>
        <div class="card-white" style="text-align:center; padding:48px;">
            <div style="font-size:48px; margin-bottom:16px;">🛏️</div>
            <h3 style="font-weight:700; margin-bottom:8px;">Belum Ada Kamar</h3>
            <p style="color:#64748b; margin-bottom:24px;">Anda belum menyewa kamar apapun saat ini.</p>
            <a href="index.php#kamar" class="btn btn-primary" style="text-decoration:none;">Cari Kamar</a>
        </div>
    <?php 
    } else { 
        $id_kamar = $row_kontrak['id_kamar'];
        $row_kamar = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=$id_kamar")->fetch_assoc();
    ?>
        <div class="card-white" style="margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div>
                    <h3 style="font-size:20px; font-weight:700; color:#1e293b;">Kamar <?= htmlspecialchars($row_kamar['kode_kamar']) ?></h3>
                    <div style="font-size:13px; color:#64748b;"><?= htmlspecialchars($row_kamar['nama_tipe']) ?></div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:#dcfce7; color:#166534; padding:6px 12px; border-radius:99px; font-size:12px; font-weight:700;">AKTIF</span>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:28px;">
                <div style="border-radius:12px; overflow:hidden; height:380px; background:#f8fafc; display:flex; align-items:center; justify-content:center;">
                    <?php if(!empty($row_kamar['foto_cover'])){ ?>
                        <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" alt="Foto Kamar <?= htmlspecialchars($row_kamar['kode_kamar']) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php } else { ?>
                        <span style="font-size:48px; color:#cbd5e1;">🏠</span>
                    <?php } ?>
                </div>

                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:flex; justify-content:space-between;">
                        <div>
                            <div style="color:#64748b; font-size:14px;">Check-in</div>
                            <div style="font-weight:700; color:#1e293b; margin-top:6px;"><?= date('d M Y', strtotime($row_kontrak['tanggal_mulai'])) ?></div>
                        </div>
                        <div>
                            <div style="color:#64748b; font-size:14px;">Berakhir</div>
                            <div style="font-weight:700; color:#1e293b; margin-top:6px;"><?= date('d M Y', strtotime($row_kontrak['tanggal_selesai'])) ?></div>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:160px; background:#f8fafc; padding:12px; border-radius:10px;">
                            <div style="font-size:12px; color:#64748b;">Durasi Sewa</div>
                            <div style="font-weight:700; color:#1e293b; margin-top:6px;"><?= $row_kontrak['durasi_bulan'] ?> Bulan</div>
                        </div>
                        <div style="flex:1; min-width:160px; background:#f8fafc; padding:12px; border-radius:10px;">
                            <div style="font-size:12px; color:#64748b;">Luas Kamar</div>
                            <div style="font-weight:700; color:#1e293b; margin-top:6px;"><?= $row_kamar['luas_m2'] ?> m²</div>
                        </div>
                        <div style="flex:1; min-width:160px; background:#f8fafc; padding:12px; border-radius:10px;">
                            <div style="font-size:12px; color:#64748b;">Lokasi Lantai</div>
                            <div style="font-weight:700; color:#1e293b; margin-top:6px;"><?= $row_kamar['lantai'] ?></div>
                        </div>
                    </div>

                    <div style="background:#fffbeb; padding:12px; border-radius:8px; border:1px solid #fef3c7; font-size:13px; color:#b45309;">
                        <strong>📝 Catatan Penghuni:</strong><br>
                        <?= htmlspecialchars($row_kamar['catatan'] ?? 'Tidak ada catatan khusus.') ?>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:auto;">
                        <a href="tagihan_saya.php" class="btn btn-primary">Lihat Tagihan</a>
                        <a href="keluhan.php" class="btn btn-secondary">Buat Keluhan</a>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
  </main>
</body>
</html>
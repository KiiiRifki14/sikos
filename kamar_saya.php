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
    <title>Kamar Saya - SIKOS</title>
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
        <a href="kamar_saya.php" class="sidebar-link active"><i class="fa-solid fa-bed w-6"></i> Kamar Saya</a>
        <a href="tagihan_saya.php" class="sidebar-link"><i class="fa-solid fa-credit-card w-6"></i> Tagihan</a>
        <a href="keluhan.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6"></i> Keluhan</a>
        <a href="pengumuman.php" class="sidebar-link"><i class="fa-solid fa-bullhorn w-6"></i> Info</a>
    </nav>
    <a href="logout.php" class="sidebar-link" style="color:#dc2626; margin-top:auto;">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h2 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:24px;">Informasi Kamar</h2>

    <?php if (!$row_kontrak) { ?>
        <div class="card-white" style="text-align:center; padding:48px;">
            <div style="font-size:48px; margin-bottom:16px;">🛏️</div>
            <h3 style="font-weight:700; margin-bottom:8px;">Belum Ada Kamar</h3>
            <p style="color:#64748b; margin-bottom:24px;">Anda belum menyewa kamar apapun saat ini.</p>
            <a href="index.php#kamar" class="btn-primary" style="text-decoration:none;">Cari Kamar</a>
        </div>
    <?php 
    } else { 
        $id_kamar = $row_kontrak['id_kamar'];
        $row_kamar = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=$id_kamar")->fetch_assoc();
    ?>
        <div class="card-white" style="margin-bottom:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
                <div>
                    <h4 style="font-size:20px; font-weight:700; color:#1e293b;">Kamar <?= htmlspecialchars($row_kamar['kode_kamar']) ?></h4>
                    <span style="font-size:13px; color:#64748b;"><?= htmlspecialchars($row_kamar['nama_tipe']) ?></span>
                </div>
                <span style="background:#dcfce7; color:#166534; padding:4px 12px; border-radius:99px; font-size:12px; font-weight:700;">AKTIF</span>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:32px;">
                <div style="border-radius:12px; overflow:hidden; height:350px; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                    <?php if($row_kamar['foto_cover']){ ?>
                        <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php } else { ?>
                        <span style="font-size:48px; color:#cbd5e1;">🏠</span>
                    <?php } ?>
                </div>

                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f8fafc; padding-bottom:8px;">
                        <span style="color:#64748b; font-size:14px;">Check-in</span>
                        <span style="font-weight:600; color:#1e293b;"><?= date('d M Y', strtotime($row_kontrak['tanggal_mulai'])) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f8fafc; padding-bottom:8px;">
                        <span style="color:#64748b; font-size:14px;">Berakhir</span>
                        <span style="font-weight:600; color:#1e293b;"><?= date('d M Y', strtotime($row_kontrak['tanggal_selesai'])) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f8fafc; padding-bottom:8px;">
                        <span style="color:#64748b; font-size:14px;">Durasi Sewa</span>
                        <span style="font-weight:600; color:#1e293b;"><?= $row_kontrak['durasi_bulan'] ?> Bulan</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f8fafc; padding-bottom:8px;">
                        <span style="color:#64748b; font-size:14px;">Luas Kamar</span>
                        <span style="font-weight:600; color:#1e293b;"><?= $row_kamar['luas_m2'] ?> m²</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f8fafc; padding-bottom:8px;">
                        <span style="color:#64748b; font-size:14px;">Lokasi Lantai</span>
                        <span style="font-weight:600; color:#1e293b;"><?= $row_kamar['lantai'] ?></span>
                    </div>
                    
                    <div style="background:#fffbeb; padding:12px; border-radius:8px; border:1px solid #fef3c7; font-size:13px; color:#b45309;">
                        <strong>📝 Catatan Penghuni:</strong><br>
                        <?= htmlspecialchars($row_kamar['catatan'] ?? 'Tidak ada catatan khusus.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
            <div class="card-white">
                <h4 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-wifi text-blue-500"></i> Fasilitas Kamar
                </h4>
                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:12px;">
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-wind"></i> AC Pendingin
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-bed"></i> Kasur Springbed
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-shower"></i> Kamar Mandi Dalam
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-box-archive"></i> Lemari Pakaian
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-wifi"></i> WiFi Gratis
                    </div>
                    <div style="background:#f8fafc; padding:10px; border-radius:8px; font-size:13px; color:#475569; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-bolt"></i> Listrik Token
                    </div>
                </div>
            </div>

            <div class="card-white">
                <h4 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-book text-red-500"></i> Peraturan Kost
                </h4>
                <ul style="font-size:13px; color:#475569; list-style:none; display:flex; flex-direction:column; gap:10px;">
                    <li style="display:flex; gap:10px; align-items:start;">
                        <i class="fa-solid fa-circle-exclamation text-red-400" style="margin-top:3px;"></i>
                        Dilarang membawa hewan peliharaan.
                    </li>
                    <li style="display:flex; gap:10px; align-items:start;">
                        <i class="fa-solid fa-circle-exclamation text-red-400" style="margin-top:3px;"></i>
                        Tamu lawan jenis dilarang masuk kamar.
                    </li>
                    <li style="display:flex; gap:10px; align-items:start;">
                        <i class="fa-solid fa-circle-exclamation text-red-400" style="margin-top:3px;"></i>
                        Wajib menjaga kebersihan dan ketenangan.
                    </li>
                    <li style="display:flex; gap:10px; align-items:start;">
                        <i class="fa-solid fa-circle-exclamation text-red-400" style="margin-top:3px;"></i>
                        Gerbang ditutup pukul 22.00 WIB.
                    </li>
                </ul>
            </div>
        </div>

    <?php } ?>
  </main>
</body>
</html>
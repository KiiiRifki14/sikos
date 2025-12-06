<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php'); exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
// Pastikan $mysqli tersedia
$db = new Database();
$mysqli = $db->koneksi;

// Ambil Nama User
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();

// Cek ID Penghuni (Gunakan fetch_object dengan safety check)
$q_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna");
$id_penghuni = ($q_penghuni->num_rows > 0) ? $q_penghuni->fetch_object()->id_penghuni : 0;

$row_kamar = null; // Default null

// Hanya jalankan query kontrak jika id_penghuni ada
if ($id_penghuni > 0) {
    $q_kamar = "SELECT k.*, t.nama_tipe, ko.tanggal_mulai, ko.tanggal_selesai, ko.id_kontrak 
                FROM kontrak ko 
                JOIN kamar k ON ko.id_kamar = k.id_kamar 
                JOIN tipe_kamar t ON k.id_tipe = t.id_tipe 
                WHERE ko.id_penghuni = $id_penghuni AND ko.status = 'AKTIF'";
                
    $res_kamar = $mysqli->query($q_kamar);
    if ($res_kamar && $res_kamar->num_rows > 0) {
        $row_kamar = $res_kamar->fetch_assoc();
    }
}

$fasilitas = [];
if($row_kamar) {
    $id_kamar = $row_kamar['id_kamar'];
    $q_fas = $mysqli->query("SELECT f.nama_fasilitas, f.icon FROM kamar_fasilitas kf JOIN fasilitas_master f ON kf.id_fasilitas=f.id_fasilitas WHERE kf.id_kamar=$id_kamar");
    while($f = $q_fas->fetch_assoc()) { $fasilitas[] = $f; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kamar Saya</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS KHUSUS HALAMAN INI */
        .kamar-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr; /* Kiri Foto, Kanan Info */
            gap: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .kamar-foto {
            position: relative;
            min-height: 300px;
            background-color: #f1f5f9;
        }
        .kamar-foto img {
            width: 100%; height: 100%; object-fit: cover; display: block;
        }
        .tipe-badge {
            position: absolute; bottom: 20px; left: 20px;
            background: rgba(0,0,0,0.7); color: white;
            padding: 6px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .kamar-info { padding: 30px; }
        .info-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 25px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;
        }
        .kode-besar { font-size: 32px; font-weight: 800; color: #1e293b; line-height: 1; margin: 5px 0 0; }
        .harga-besar { font-size: 20px; font-weight: 700; color: #2563eb; }
        
        .grid-info {
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;
        }
        .info-box {
            background: #f8fafc; padding: 12px; border-radius: 8px;
        }
        .info-label { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .info-val { font-weight: 600; color: #334155; font-size: 14px; margin-top: 4px; }
        
        .fasilitas-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 25px; }
        .fas-item {
            font-size: 12px; background: #eff6ff; color: #2563eb;
            padding: 6px 12px; border-radius: 20px; border: 1px solid #dbeafe;
            display: flex; align-items: center; gap: 6px;
        }
        
        /* Responsif HP */
        @media (max-width: 768px) {
            .kamar-layout { grid-template-columns: 1fr; }
            .kamar-foto { height: 200px; min-height: auto; }
        }
    </style>
</head>
<body class="role-penghuni">
  <?php include 'components/sidebar_penghuni.php'; ?>
  
  <main class="main-content">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Kamar Saya</h1>
        <p style="font-size: 13px; color: #64748b;">Detail kamar yang sedang Anda tempati.</p>
    </div>

    <?php if($row_kamar): ?>
        <div class="kamar-layout">
            <div class="kamar-foto">
                <?php if(!empty($row_kamar['foto_cover'])): ?>
                    <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>">
                <?php else: ?>
                    <div style="height:100%; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                        <i class="fa-regular fa-image" style="font-size:40px;"></i>
                    </div>
                <?php endif; ?>
                <div class="tipe-badge">
                    <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($row_kamar['nama_tipe']) ?>
                </div>
            </div>

            <div class="kamar-info">
                <div class="info-header">
                    <div>
                        <span style="font-size:11px; font-weight:700; color:#2563eb; letter-spacing:1px;">KODE KAMAR</span>
                        <h2 class="kode-besar"><?= htmlspecialchars($row_kamar['kode_kamar']) ?></h2>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:block; font-size:12px; color:#64748b;">Biaya Sewa</span>
                        <span class="harga-besar">Rp <?= number_format($row_kamar['harga']) ?></span>
                    </div>
                </div>

                <div class="grid-info">
                    <div class="info-box">
                        <div class="info-label">Mulai Sewa</div>
                        <div class="info-val"><?= date('d M Y', strtotime($row_kamar['tanggal_mulai'])) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Berakhir</div>
                        <div class="info-val"><?= date('d M Y', strtotime($row_kamar['tanggal_selesai'])) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Lantai</div>
                        <div class="info-val"><?= htmlspecialchars($row_kamar['lantai']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Luas</div>
                        <div class="info-val"><?= htmlspecialchars($row_kamar['luas_m2']) ?> m²</div>
                    </div>
                </div>

                <div style="margin-bottom: 10px; font-size: 13px; font-weight: 700; color: #334155;">Fasilitas:</div>
                <div class="fasilitas-list">
                    <?php if(!empty($fasilitas)): foreach($fasilitas as $f): ?>
                        <span class="fas-item">
                            <i class="fa-solid <?= $f['icon'] ?>"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                        </span>
                    <?php endforeach; else: ?>
                        <span style="font-size:12px; color:#94a3b8;">-</span>
                    <?php endif; ?>
                </div>

                <?php if(!empty($row_kamar['catatan'])): ?>
                <div style="background:#fff7ed; border:1px solid #ffedd5; padding:15px; border-radius:8px;">
                    <strong style="display:block; font-size:12px; color:#9a3412; margin-bottom:5px;">Catatan:</strong>
                    <p style="font-size:13px; color:#c2410c; margin:0;"><?= htmlspecialchars($row_kamar['catatan']) ?></p>
                </div>
                
                <?php endif; ?>
                
            </div>
            
        </div>
    <?php else: ?>
        
        <div class="card-white" style="text-align:center; padding:60px 20px;">
            <div style="margin-bottom:20px;">
                <i class="fa-solid fa-bed" style="font-size:64px; color:#cbd5e1;"></i>
            </div>
            <h3 style="color:#64748b; font-size:18px; font-weight:600; margin-bottom:10px;">
                Anda belum menyewa kamar.
            </h3>
            <p style="color:#94a3b8; font-size:14px; margin-bottom:30px;">
                Silakan cari kamar yang tersedia dan ajukan penyewaan.
            </p>
            
            <a href="index.php#kamar" class="btn btn-primary" style="padding: 12px 30px;">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Cari Kamar Sekarang
            </a>
        </div>

    <?php endif; ?>
    
  </main>
</body>
</html>
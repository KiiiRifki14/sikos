<?php
session_start();
require 'inc/koneksi.php';

// Cek Login
if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php'); exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT nama FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;

// [PERBAIKAN QUERY]
// Menghapus 'ko.harga_deal' yang bikin error. 
// Kita pakai harga dari tabel kamar (k.harga) yang otomatis terambil lewat k.*
$q_kamar = "SELECT k.*, t.nama_tipe, ko.tanggal_mulai, ko.tanggal_selesai 
            FROM kontrak ko 
            JOIN kamar k ON ko.id_kamar = k.id_kamar 
            JOIN tipe_kamar t ON k.id_tipe = t.id_tipe 
            WHERE ko.id_penghuni = $id_penghuni AND ko.status = 'AKTIF'";
            
$res_kamar = $mysqli->query($q_kamar);
$row_kamar = $res_kamar->fetch_assoc();

// Ambil Fasilitas Kamar
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
</head>
<body class="dashboard-body"> <?php include 'components/sidebar_penghuni.php'; ?>
  
  <main class="main-content">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Kamar Saya</h1>
        <p style="font-size:13px; color:#64748b;">Informasi detail kamar yang sedang Anda sewa.</p>
    </div>

    <?php if(!$row_kamar): ?>
        <div class="card-white" style="text-align:center; padding:50px;">
            <i class="fa-solid fa-bed" style="font-size:48px; color:#cbd5e1; margin-bottom:15px;"></i>
            <h3 style="color:#64748b;">Anda belum menyewa kamar.</h3>
            <p class="text-sm text-muted mb-4">Silakan lakukan pemesanan kamar terlebih dahulu.</p>
            <a href="index.php" class="btn btn-primary">Cari Kamar</a>
        </div>
    <?php else: ?>
        
        <div class="card-white" style="padding:0; overflow:hidden;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                
                <div style="position:relative; min-height:300px; background:#f1f5f9;">
                    <?php if(!empty($row_kamar['foto_cover'])): ?>
                        <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" style="width:100%; height:100%; object-fit:cover; position:absolute;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; position:absolute; color:#94a3b8;">
                            <i class="fa-regular fa-image" style="font-size:40px;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="position:absolute; bottom:20px; left:20px; background:rgba(0,0,0,0.7); color:white; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                        <i class="fa-solid fa-tag mr-1"></i> <?= htmlspecialchars($row_kamar['nama_tipe']) ?>
                    </div>
                </div>

                <div style="padding:30px;">
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:20px;">
                        <div>
                            <span style="font-size:12px; font-weight:700; color:#2563eb; letter-spacing:1px;">KODE KAMAR</span>
                            <h2 style="font-size:32px; font-weight:800; color:#1e293b; line-height:1;"><?= htmlspecialchars($row_kamar['kode_kamar']) ?></h2>
                        </div>
                        <div style="text-align:right;">
                            <span style="display:block; font-size:12px; color:#64748b;">Biaya Sewa</span>
                            <span style="font-size:18px; font-weight:700; color:#1e293b;">Rp <?= number_format($row_kamar['harga']) ?></span>
                            <span style="font-size:12px; color:#64748b;">/ bulan</span>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:25px;">
                        <div style="background:#f8fafc; padding:12px; border-radius:8px;">
                            <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase;">Mulai Sewa</div>
                            <div style="font-weight:600; color:#334155;"><?= date('d M Y', strtotime($row_kamar['tanggal_mulai'])) ?></div>
                        </div>
                        <div style="background:#f8fafc; padding:12px; border-radius:8px;">
                            <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase;">Berakhir</div>
                            <div style="font-weight:600; color:#334155;"><?= date('d M Y', strtotime($row_kamar['tanggal_selesai'])) ?></div>
                        </div>
                        <div style="background:#f8fafc; padding:12px; border-radius:8px;">
                            <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase;">Lantai</div>
                            <div style="font-weight:600; color:#334155;"><?= htmlspecialchars($row_kamar['lantai']) ?></div>
                        </div>
                        <div style="background:#f8fafc; padding:12px; border-radius:8px;">
                            <div style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase;">Luas</div>
                            <div style="font-weight:600; color:#334155;"><?= htmlspecialchars($row_kamar['luas_m2']) ?> m²</div>
                        </div>
                    </div>

                    <h4 style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px;">Fasilitas Termasuk</h4>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:25px;">
                        <?php if(!empty($fasilitas)): foreach($fasilitas as $f): ?>
                            <span style="font-size:12px; background:#eff6ff; color:#2563eb; padding:6px 12px; border-radius:20px; border:1px solid #dbeafe;">
                                <i class="fa-solid <?= $f['icon'] ?> mr-1"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                            </span>
                        <?php endforeach; else: ?>
                            <span style="font-size:12px; color:#94a3b8;">Tidak ada data fasilitas.</span>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($row_kamar['catatan'])): ?>
                    <div style="background:#fff7ed; border:1px solid #ffedd5; padding:15px; border-radius:8px;">
                        <h5 style="font-size:13px; font-weight:700; color:#9a3412; margin-bottom:5px;"><i class="fa-solid fa-circle-info mr-1"></i> Catatan Kamar</h5>
                        <p style="font-size:13px; color:#c2410c; line-height:1.5;"><?= htmlspecialchars($row_kamar['catatan']) ?></p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    <?php endif; ?>
  </main>
</body>
</html>
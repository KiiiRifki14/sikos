<?php
session_start();
require 'inc/koneksi.php';

$id_kamar = intval($_GET['id'] ?? 0);
if ($id_kamar <= 0) {
    echo "<script>alert('Kamar tidak ditemukan'); window.location='index.php';</script>";
    exit;
}

// 1. Ambil Detail Kamar
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo "<script>alert('Data kamar tidak ditemukan'); window.location='index.php';</script>";
    exit;
}

// 2. Ambil Galeri Foto
$gallery = [];
if (!empty($row['foto_cover'])) {
    $gallery[] = $row['foto_cover'];
}

// Cek tabel kamar_foto jika ada
try {
    $test = $mysqli->query("SHOW COLUMNS FROM kamar_foto");
    if ($test) {
        $stmt_foto = $mysqli->prepare("SELECT file_nama FROM kamar_foto WHERE id_kamar = ?");
        $stmt_foto->bind_param('i', $id_kamar);
        $stmt_foto->execute();
        $res_foto = $stmt_foto->get_result();
        while ($f = $res_foto->fetch_assoc()) {
            $gallery[] = $f['file_nama'];
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Layout Grid: Kiri Konten, Kanan Booking Card --- */
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 40px;
            margin-top: 30px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .detail-layout { grid-template-columns: 1fr; gap: 20px; }
        }

        /* --- Slider Foto --- */
        .slider-container {
            position: relative; width: 100%; height: 450px;
            background: #e2e8f0; border-radius: 16px; overflow: hidden;
        }
        .slider-wrapper { display: flex; height: 100%; transition: transform 0.5s ease-in-out; }
        .slide { min-width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
        .slide img { width: 100%; height: 100%; object-fit: cover; }
        
        .slider-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.8); border: none; width: 40px; height: 40px;
            border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: 0.2s; z-index: 10; font-size: 18px;
        }
        .slider-nav:hover { background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .prev-btn { left: 20px; }
        .next-btn { right: 20px; }

        @media (max-width: 768px) { .slider-container { height: 250px; } }

        /* --- Informasi Kamar --- */
        .room-title { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1.2; }
        .room-type-badge { 
            display: inline-block; background: #eff6ff; color: var(--primary); 
            font-weight: bold; font-size: 12px; padding: 4px 10px; border-radius: 6px; margin-bottom: 10px;
        }

        .facility-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-top: 20px;
        }
        .facility-box {
            background: #f8fafc; border: 1px solid var(--border); padding: 15px;
            border-radius: 10px; text-align: center; font-size: 13px; font-weight: 500; color: var(--text-muted);
        }
        .facility-box i { display: block; font-size: 20px; color: var(--primary); margin-bottom: 8px; }

        /* --- Booking Card (Kanan) --- */
        .booking-card {
            background: white; border: 1px solid var(--border); padding: 24px;
            border-radius: 16px; position: sticky; top: 100px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .price-text { font-size: 28px; font-weight: 800; color: var(--primary); }
        .price-period { font-size: 14px; color: var(--text-muted); font-weight: normal; }
        
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
        .info-row span:last-child { font-weight: 600; color: var(--text-main); }
    </style>
</head>
<body style="background-color: #fcfcfc;">

  <?php include 'components/header.php'; ?>

  <div style="max-width: 1100px; margin: 0 auto; padding: 100px 20px 60px;">
    
    <div class="slider-container">
        <?php if (count($gallery) > 0): ?>
            <div class="slider-wrapper" id="sliderWrapper">
                <?php foreach ($gallery as $foto): ?>
                    <div class="slide"><img src="assets/uploads/kamar/<?= htmlspecialchars($foto) ?>"></div>
                <?php endforeach; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
                <button class="slider-nav prev-btn" onclick="moveSlide(-1)">❮</button>
                <button class="slider-nav next-btn" onclick="moveSlide(1)">❯</button>
            <?php endif; ?>
        <?php else: ?>
            <div class="slide" style="background:#cbd5e1; color:white; flex-direction:column;">
                <i class="fa-solid fa-image" style="font-size:40px; margin-bottom:10px;"></i>
                Tidak ada foto
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-layout">
        
        <div>
            <span class="room-type-badge"><?= htmlspecialchars($row['nama_tipe']) ?></span>
            <h1 class="room-title">Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h1>
            <p style="color:var(--text-muted); margin-top:10px;">
                Nikmati kenyamanan tinggal di kamar ini. Lokasi strategis, aman, dan fasilitas lengkap siap memanjakan hari-hari Anda.
            </p>

            <h3 style="margin-top:30px; font-weight:700;">Fasilitas Kamar</h3>
            <div class="facility-grid">
                <?php
                $q_fas = $mysqli->query("SELECT f.nama_fasilitas, f.icon FROM kamar_fasilitas kf JOIN fasilitas_master f ON kf.id_fasilitas = f.id_fasilitas WHERE kf.id_kamar = $id_kamar");
                if($q_fas->num_rows > 0) {
                    while($f = $q_fas->fetch_assoc()) {
                        echo '<div class="facility-box"><i class="fa-solid '.$f['icon'].'"></i> '.$f['nama_fasilitas'].'</div>';
                    }
                } else {
                    echo '<div style="color:var(--text-muted); font-size:13px;">Fasilitas standar tersedia.</div>';
                }
                ?>
            </div>

            <h3 style="margin-top:30px; font-weight:700;">Catatan / Peraturan</h3>
            <div style="background:#fffbeb; border:1px solid #fcd34d; padding:15px; border-radius:10px; font-size:14px; color:#92400e; line-height:1.6;">
                <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tidak ada catatan khusus.')) ?>
            </div>
        </div>

        <div class="booking-card">
            <div class="price-text">
                Rp <?= number_format($row['harga'],0,',','.') ?>
                <span class="price-period">/ bln</span>
            </div>

            <div style="margin-top:20px;">
                <div class="info-row">
                    <span>Luas Kamar</span> <span><?= $row['luas_m2'] ?> m²</span>
                </div>
                <div class="info-row">
                    <span>Lantai</span> <span><?= $row['lantai'] ?></span>
                </div>
                <div class="info-row" style="border:none;">
                    <span>Status</span> 
                    <?php if($row['status_kamar']=='TERSEDIA'): ?>
                        <span style="color:var(--success);">Tersedia</span>
                    <?php else: ?>
                        <span style="color:var(--danger);">Penuh</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top:20px;">
                <?php if($row['status_kamar'] == 'TERSEDIA'): ?>
                    <form method="post" action="goto_booking.php">
                        <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">
                        <button type="submit" class="btn btn-primary w-full" style="padding:14px; justify-content:center; font-size:16px;">
                            Ajukan Sewa
                        </button>
                    </form>
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-secondary w-full" style="margin-top:10px; justify-content:center;">
                        <i class="fa-brands fa-whatsapp"></i> Tanya Pemilik
                    </a>
                <?php else: ?>
                    <button disabled class="btn w-full" style="background:#f1f5f9; color:#94a3b8; cursor:not-allowed; justify-content:center; padding:14px;">
                        Kamar Tidak Tersedia
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div>
  </div>

  <?php include 'components/footer.php'; ?>

  <script>
    /* Simple Native Slider */
    let currentIndex = 0;
    const wrapper = document.getElementById('sliderWrapper');
    const totalSlides = document.querySelectorAll('.slide').length;

    function moveSlide(step) {
        currentIndex += step;
        if (currentIndex >= totalSlides) currentIndex = 0;
        else if (currentIndex < 0) currentIndex = totalSlides - 1;
        wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
    }
  </script>

</body>
</html>
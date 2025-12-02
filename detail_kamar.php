<?php
session_start();
require 'inc/koneksi.php';

// Ambil id_kamar dari GET
$id_kamar = intval($_GET['id'] ?? 0);
// KODE BARU (Sudah diperbaiki)
if ($id_kamar <= 0) pesan_error("index.php", "Kamar tidak ditemukan atau URL salah!");

// 1. Ambil detail kamar utama
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) pesan_error("index.php", "Maaf, data kamar tersebut tidak ditemukan.");

// 2. Ambil Galeri Foto
$gallery = [];
if (!empty($row['foto_cover'])) {
    $gallery[] = $row['foto_cover'];
}
try {
    $test = $mysqli->query("SHOW COLUMNS FROM kamar_foto LIKE 'file_nama'");
    if ($test && $test->num_rows > 0) {
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
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CUSTOM STYLE UNTUK HALAMAN INI --- */
        
        /* Layout Grid Utama: Kiri (Konten) vs Kanan (Booking Card) */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 380px; /* Kiri auto, Kanan fixed width */
            gap: 2rem;
            margin-top: 1.5rem;
            align-items: start;
        }

        /* Styling Bagian Kiri (Konten) */
        .main-info {
            background: transparent; 
        }

        /* Judul & Deskripsi */
        .room-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
        .room-subtitle { color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 2rem; }

        /* Grid Fasilitas (Kotak-kotak abu seperti di gambar) */
        .facility-section-title {
            font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; color: #334155; 
            display: flex; align-items: center; gap: 8px;
        }
        
        .facility-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 Kolom */
            gap: 16px;
            margin-bottom: 2.5rem;
        }

        .facility-item {
            background: #f1f5f9; /* Abu muda */
            padding: 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            color: #334155;
            font-size: 0.95rem;
        }
        .facility-item i { color: #2563eb; font-size: 1.1rem; width: 24px; text-align: center; }

        /* List Peraturan */
        .rules-list { list-style: none; padding: 0; }
        .rules-list li {
            position: relative;
            padding-left: 24px;
            margin-bottom: 10px;
            color: #64748b;
            font-size: 0.95rem;
        }
        .rules-list li::before {
            content: "•"; color: #94a3b8; font-weight: bold; font-size: 1.2rem;
            position: absolute; left: 0; top: -2px;
        }

        /* --- STYLING KARTU KANAN (BOOKING CARD) --- */
        .booking-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            position: sticky; /* Agar ikut scroll */
            top: 2rem; 
            border: 1px solid #f1f5f9;
        }

        .price-tag {
            font-size: 1.5rem; font-weight: 700; color: #2563eb;
            margin-bottom: 1.5rem;
        }
        .price-period { font-size: 0.9rem; color: #64748b; font-weight: 400; }

        /* Detail Baris (Ukuran, Lantai, dll) */
        .detail-row {
            display: flex; justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
        .detail-row:last-of-type { border-bottom: none; margin-bottom: 1.5rem; }
        .detail-label { color: #94a3b8; }
        .detail-value { font-weight: 600; color: #1e293b; }

        /* Tombol */
        .btn-full { width: 100%; justify-content: center; padding: 14px; font-size: 1rem; border-radius: 10px; }
        .btn-outline {
            background: white; border: 1px solid #e2e8f0; color: #1e293b; margin-top: 12px;
        }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* --- SLIDER CSS (Sama seperti sebelumnya) --- */
        .slider-container {
            position: relative; width: 100%; height: 400px;
            background: #e2e8f0; border-radius: 16px; overflow: hidden; margin-bottom: 1rem;
        }
        .slider-wrapper { display: flex; height: 100%; transition: transform 0.5s ease-in-out; }
        .slide { min-width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
        .slide img { width: 100%; height: 100%; object-fit: cover; }
        .slider-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.8); color: #333; border: none;
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; transition: 0.2s; z-index: 10;
        }
        .slider-btn:hover { background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .prev-btn { left: 20px; }
        .next-btn { right: 20px; }
        .slider-dots {
            position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
            display: flex; gap: 8px; z-index: 10;
        }
        .dot { width: 8px; height: 8px; background: rgba(255,255,255,0.6); border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .dot.active { background: white; transform: scale(1.3); }

        /* Responsif untuk Mobile */
        @media (max-width: 900px) {
            .detail-grid { grid-template-columns: 1fr; } /* Stack ke bawah di HP */
            .booking-card { position: static; margin-top: 2rem; }
            .slider-container { height: 250px; }
        }
    </style>
</head>
<body>

<header class="nav" style="position:relative; margin-bottom:20px;">
   <div class="nav-container">
     <div class="brand-text"><h3>SIKOS</h3></div>
     <div class="nav-menu">
         <a href="index.php" class="btn btn-secondary">← Kembali</a>
     </div>
   </div>
</header>

<div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 24px 60px;">

    <div class="slider-container">
        <?php if (count($gallery) > 0): ?>
            <div class="slider-wrapper" id="sliderWrapper">
                <?php foreach ($gallery as $foto): ?>
                <div class="slide">
                    <img src="assets/uploads/kamar/<?= htmlspecialchars($foto) ?>" alt="Foto Kamar">
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
                <button class="slider-btn prev-btn" onclick="moveSlide(-1)">❮</button>
                <button class="slider-btn next-btn" onclick="moveSlide(1)">❯</button>
                <div class="slider-dots">
                    <?php foreach ($gallery as $i => $f): ?>
                        <div class="dot <?= $i===0?'active':'' ?>" onclick="goToSlide(<?= $i ?>)"></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="slide" style="flex-direction:column; color:#94a3b8;">
                <i class="fa-regular fa-image" style="font-size:3rem; margin-bottom:10px;"></i>
                <p>Belum ada foto tersedia</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-grid">
        
        <div class="main-info">
            <h1 class="room-title">Kamar <?= htmlspecialchars($row['kode_kamar']) ?> - <?= htmlspecialchars($row['nama_tipe']) ?></h1>
            <p class="room-subtitle">
                Kamar nyaman dengan fasilitas lengkap, cocok untuk mahasiswa atau pekerja profesional yang menginginkan kenyamanan maksimal.
            </p>

            <div class="facility-section-title"><i class="fa-solid fa-wand-magic-sparkles" style="color:#eab308;"></i> Fasilitas Kamar</div>
            
            <div class="facility-grid">
                <?php
                // Ambil fasilitas dari database berdasarkan ID Kamar ini
                $q_fas = $mysqli->query("SELECT f.nama_fasilitas, f.icon 
                                         FROM kamar_fasilitas kf 
                                         JOIN fasilitas_master f ON kf.id_fasilitas = f.id_fasilitas 
                                         WHERE kf.id_kamar = $id_kamar");
                
                if($q_fas->num_rows > 0) {
                    while($f = $q_fas->fetch_assoc()) {
                ?>
                    <div class="facility-item">
                        <i class="fa-solid <?= $f['icon'] ?>"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                    </div>
                <?php 
                    } 
                } else {
                    echo '<div style="color:#64748b; font-size:14px; grid-column: span 2;">Belum ada data fasilitas.</div>';
                }
                ?>
            </div>
            <div class="facility-section-title"><i class="fa-regular fa-clipboard"></i> Peraturan Kos</div>
            <ul class="rules-list">
                <?php 
                    // Jika ada catatan dari DB, tampilkan. Jika tidak, tampilkan default
                    $catatan = trim($row['catatan'] ?? '');
                    if(!empty($catatan)) {
                        echo "<li>".nl2br(htmlspecialchars($catatan))."</li>";
                    } else {
                ?>
                    <li>Tidak boleh membawa hewan peliharaan</li>
                    <li>Tamu pulang maksimal pukul 22.00 WIB</li>
                    <li>Jam malam penghuni: 23.00 WIB</li>
                    <li>Dilarang merokok di dalam kamar</li>
                    <li>Pembayaran setiap tanggal 5</li>
                <?php } ?>
            </ul>
        </div>

        <div class="booking-card">
            <div class="price-tag">
                Rp <?= number_format($row['harga'],0,',','.') ?>
                <span class="price-period">/bulan</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Ukuran:</span>
                <span class="detail-value"><?= $row['luas_m2'] ?> meter</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Lantai:</span>
                <span class="detail-value">Lantai <?= $row['lantai'] ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">No. Kamar:</span>
                <span class="detail-value"><?= htmlspecialchars($row['kode_kamar']) ?></span>
            </div>

            <?php if($row['status_kamar'] == 'TERSEDIA'): ?>
                <form method="post" action="goto_booking.php">
                    <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">
                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fa-solid fa-file-signature"></i> Booking Sekarang
                    </button>
                </form>
                
                <a href="#" class="btn btn-full btn-outline">
                    <i class="fa-solid fa-phone"></i> Hubungi Pemilik
                </a>
            <?php else: ?>
                <button disabled class="btn btn-full" style="background:#e2e8f0; color:#94a3b8; cursor:not-allowed;">
                    ⛔ Kamar Sudah Terisi
                </button>
            <?php endif; ?>

        </div>

    </div>
</div>

<script>
    /* Javascript Slider Logic */
    let currentIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;
    const wrapper = document.getElementById('sliderWrapper');

    function updateSlider() {
        if(wrapper) wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, index) => {
            if(dot) dot.classList.toggle('active', index === currentIndex);
        });
    }

    function moveSlide(step) {
        currentIndex += step;
        if (currentIndex >= totalSlides) currentIndex = 0;
        else if (currentIndex < 0) currentIndex = totalSlides - 1;
        updateSlider();
    }

    function goToSlide(index) {
        currentIndex = index;
        updateSlider();
    }
</script>

</body>
</html>
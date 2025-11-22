<?php
session_start();
require 'inc/koneksi.php';

// Ambil id_kamar dari GET
$id_kamar = intval($_GET['id'] ?? 0);
if ($id_kamar <= 0) die("Kamar tidak ditemukan!");

// 1. Ambil detail kamar utama
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) die("Kamar tidak ditemukan!");

// 2. Ambil Galeri Foto Tambahan (Dengan Try-Catch agar tidak Fatal Error)
$gallery = [];

// Masukkan Foto Cover sebagai foto pertama
if (!empty($row['foto_cover'])) {
    $gallery[] = $row['foto_cover'];
}

try {
    // Cek apakah tabel/kolom ada sebelum query untuk menghindari crash
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
} catch (Exception $e) {
    // Jika error database, diam saja (jangan crash), cukup tampilkan foto cover saja
    // error_log($e->getMessage()); 
}

// Status Class untuk UI
$statusClass = ($row['status_kamar'] == 'TERSEDIA') ? 'status-available' : 'status-occupied';
$statusText  = ($row['status_kamar'] == 'TERSEDIA') ? '✓ Tersedia' : '● Terisi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <style>
        /* --- CSS SLIDER --- */
        .slider-container {
            position: relative;
            width: 100%;
            height: 450px;
            background: #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .slider-wrapper {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }
        .slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ddd;
        }
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.4);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            z-index: 10;
        }
        .slider-btn:hover { background: rgba(0, 0, 0, 0.8); }
        .prev-btn { left: 20px; }
        .next-btn { right: 20px; }
        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        .dot {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
        }
        .dot.active { background: white; transform: scale(1.2); }
    </style>
</head>
<body>

<header class="header">
   <div class="container">
    <div class="nav-wrapper">
     <a href="index.php" class="logo-section" style="text-decoration:none; color:inherit;">
      <div class="brand-text"><h1>SIKOS</h1></div>
     </a>
     <div class="nav-menu">
         <a href="index.php" class="button" style="background:#f0f0f0; color:#333; width:auto; display:inline-block;">← Kembali</a>
     </div>
    </div>
   </div>
</header>

<div class="detail-page active" style="padding-top:2rem;">
   <div class="container">
    
    <div class="detail-header" style="background:white; padding:1.5rem; border-radius:12px; margin-bottom:1.5rem; box-shadow:0 4px 20px rgba(0,0,0,0.05);">
     <h2 style="margin:0;">Detail Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h2>
     <p style="color:#666; margin-top:5px;"><?= htmlspecialchars($row['nama_tipe']) ?> • Lantai <?= $row['lantai'] ?></p>
    </div>

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
            <div class="slide">
                <div style="text-align:center; color:#888;">
                    <span style="font-size:4rem;">📷</span><br>Belum ada foto
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-content" style="background: white; padding: 2rem; border-radius: 12px; box-shadow:0 4px 20px rgba(0,0,0,0.05);">
     <h3>Spesifikasi & Harga</h3>
     <div class="spec-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
      <div class="spec-item" style="background:#f8f9fa; padding:1rem; border-radius:8px;">
       <div class="spec-label" style="color:#666; font-size:0.9rem;">Harga Sewa</div>
       <div class="spec-value" style="font-size:1.2rem; font-weight:bold; color:#1e88e5;">
        Rp <?= number_format($row['harga'],0,',','.') ?> <span style="font-size:0.9rem; color:#666; font-weight:normal;">/bulan</span>
       </div>
      </div>
      <div class="spec-item" style="background:#f8f9fa; padding:1rem; border-radius:8px;">
       <div class="spec-label" style="color:#666; font-size:0.9rem;">Luas Kamar</div>
       <div class="spec-value" style="font-weight:bold;"><?= $row['luas_m2'] ?> m²</div>
      </div>
      <div class="spec-item" style="background:#f8f9fa; padding:1rem; border-radius:8px;">
       <div class="spec-label" style="color:#666; font-size:0.9rem;">Status</div>
       <div class="<?= $statusClass ?>" style="font-weight:bold; margin-top:5px; display:inline-block;"><?= $statusText ?></div>
      </div>
     </div>

     <div style="margin-bottom:2rem;">
        <h4 style="margin-bottom:0.5rem;">Catatan / Deskripsi</h4>
        <p style="line-height:1.6; color:#444;">
            <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tidak ada catatan khusus.')) ?>
        </p>
     </div>

     <?php if($row['status_kamar']=='TERSEDIA'){ ?>
        <form method="post" action="goto_booking.php">
          <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">
          <button type="submit" class="btn-detail" style="font-size:1.1rem; padding:1rem;">📝 Booking Kamar Ini Sekarang</button>
        </form>
     <?php } else { ?>
        <button disabled style="background:#ccc; color:#666; cursor:not-allowed; width:100%; padding:1rem; border:none; border-radius:12px; font-weight:bold;">⛔ Kamar Sudah Terisi</button>
     <?php } ?>
    </div>
   </div>
</div>

<script>
    let currentIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;
    const wrapper = document.getElementById('sliderWrapper');

    function updateSlider() {
        if(wrapper) wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
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
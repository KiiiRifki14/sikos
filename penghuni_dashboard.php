<?php
session_start();
require 'inc/koneksi.php';
require 'inc/utils.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $db->get_user_by_id($id_pengguna);
$id_penghuni = $db->get_id_penghuni_by_user($id_pengguna);

$kontrak = null;
if($id_penghuni) {
if($id_penghuni) {
    $kontrak = $db->get_kamar_penghuni_detail($id_penghuni);
}
}

$tagihan_pending = 0;
if ($kontrak) {
    $tagihan_pending = $db->get_tagihan_pending_count($kontrak['id_kontrak']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="role-penghuni"> 
  <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content animate-fade-up">

      <!-- WELCOME BANNER -->
      <div style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); border-radius:24px; padding:40px; color:white; margin-bottom:30px; position:relative; overflow:hidden; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);">
          <i class="fa-solid fa-house-chimney" style="position:absolute; right:-20px; bottom:-30px; font-size:160px; color:rgba(255,255,255,0.1); transform:rotate(-15deg);"></i>
          <div style="position:relative; z-index:2;">
              <h2 style="font-size:28px; font-weight:800; margin-bottom:10px; letter-spacing:-0.5px;">
                  Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>! 👋
              </h2>
              <p style="opacity:0.9; font-size:16px; max-width:600px; line-height:1.6;">
                  Selamat datang kembali. Semoga hari Anda menyenangkan di SIKOS!
              </p>
              
              <?php if($tagihan_pending > 0): ?>
                  <div style="margin-top:24px; display:inline-flex; align-items:center; background:rgba(255,255,255,0.15); padding:12px 20px; border-radius:12px; backdrop-filter: blur(10px); border:1px solid rgba(255,255,255,0.2);">
                      <div style="width:32px; height:32px; background:#fcd34d; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:12px; color:#b45309;">
                         <i class="fa-solid fa-bell"></i>
                      </div>
                      <span style="font-size:14px; font-weight:500; margin-right: 20px;">
                          Ada <b><?= $tagihan_pending ?> Tagihan</b> belum dibayar.
                      </span>
                      <a href="tagihan_saya.php" class="btn btn-sm" style="background:white; color:var(--primary); font-weight:700; border:none; padding: 8px 18px; border-radius: 8px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                          Bayar Sekarang <i class="fa-solid fa-arrow-right ml-1"></i>
                      </a>
                  </div>
              <?php endif; ?>
          </div>
      </div>

      <!-- STATS GRID -->
      <div class="grid-stats">
          
          <!-- KAMAR ANDA -->
          <div class="card-white" style="display:flex; align-items:center; gap:20px;">
              <div style="width:60px; height:60px; background:#dcfce7; color:#166534; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                  <i class="fa-solid fa-door-open"></i>
              </div>
              <div>
                  <div class="text-sm text-muted font-bold uppercase tracking-wider mb-1">Kamar Anda</div>
                  <div class="text-xl font-bold text-main"><?= $kontrak['kode_kamar'] ?? '-' ?></div>
              </div>
          </div>

          <!-- MASA SEWA -->
          <div class="card-white" style="display:flex; align-items:center; gap:20px;">
              <div style="width:60px; height:60px; background:#e0f2fe; color:#0369a1; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                  <i class="fa-regular fa-calendar-check"></i>
              </div>
              <div>
                  <div class="text-sm text-muted font-bold uppercase tracking-wider mb-1">Berakhir Pada</div>
                  <div class="text-xl font-bold text-main">
                      <?= $kontrak ? date('d M Y', strtotime($kontrak['tanggal_selesai'])) : '-' ?>
                  </div>
              </div>
          </div>

          <!-- TAGIHAN -->
          <div class="card-white" style="display:flex; align-items:center; gap:20px;">
              <div style="width:60px; height:60px; background:#ffedd5; color:#c2410c; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                  <i class="fa-solid fa-file-invoice-dollar"></i>
              </div>
              <div>
                  <div class="text-sm text-muted font-bold uppercase tracking-wider mb-1">Tagihan Aktif</div>
                  <div class="text-xl font-bold text-main">
                      <?= $tagihan_pending > 0 ? 'Rp '.number_format($kontrak['harga'] ?? 0) : 'Lunas' ?>
                  </div>
              </div>
          </div>
      </div>

      <!-- PENGUMUMAN -->
      <div class="card-white">
          <div class="flex justify-between items-center mb-6 border-b pb-4">
              <h3 class="font-bold text-lg"><i class="fa-solid fa-bullhorn text-blue-500 mr-2"></i> Info Terbaru</h3>
              <a href="pengumuman.php" class="text-sm font-bold text-primary">Lihat Semua →</a>
          </div>

          <?php
            $res = $db->get_pengumuman_terbaru(2);
            if($res->num_rows > 0):
                while($r = $res->fetch_assoc()):
            ?>
              <div style="padding:20px; background:#f8fafc; border-radius:12px; border-left:5px solid #3b82f6; margin-bottom:16px; transition:transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                  <h4 class="font-bold text-main text-md mb-1"><?= htmlspecialchars($r['judul']) ?></h4>
                  <div class="text-xs text-muted mb-2"><i class="fa-regular fa-clock mr-1"></i> <?= date('d F Y', strtotime($r['aktif_mulai'])) ?></div>
                  <p class="text-sm text-muted leading-relaxed"><?= substr(strip_tags($r['isi']), 0, 150) ?>...</p>
              </div>
            <?php endwhile; else: ?>
                <div class="text-center py-8 text-muted italic">
                    <i class="fa-regular fa-folder-open text-2xl mb-2 block"></i>
                    Belum ada pengumuman minggu ini.
                </div>
            <?php endif; ?>
      </div>

  </main>
</body>
</html>
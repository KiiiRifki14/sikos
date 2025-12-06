<?php
session_start();
require 'inc/koneksi.php';
require 'inc/utils.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran']!='PENGHUNI') { header('Location: login.php'); exit; }

$id_pengguna = $_SESSION['id_pengguna'];
$user = $mysqli->query("SELECT * FROM pengguna WHERE id_pengguna=$id_pengguna")->fetch_assoc();
$id_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna")->fetch_object()->id_penghuni ?? 0;

$kontrak = null;
if($id_penghuni) {
    $kontrak = $mysqli->query("SELECT k.*, km.kode_kamar, km.harga FROM kontrak k JOIN kamar km ON k.id_kamar=km.id_kamar WHERE k.id_penghuni=$id_penghuni AND k.status='AKTIF'")->fetch_assoc();
}

$tagihan_pending = 0;
if ($kontrak) {
    $tagihan_pending = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} AND status='BELUM'")->fetch_row()[0];
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
<body class="role-penghuni"> <?php include 'components/sidebar_penghuni.php'; ?>

  <main class="main-content fade-in">
      
      <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius:16px; padding:30px; color:white; margin-bottom:30px; position:relative; overflow:hidden; box-shadow: var(--shadow-lg);">
          <i class="fa-solid fa-house-chimney" style="position:absolute; right:-20px; bottom:-30px; font-size:140px; color:rgba(255,255,255,0.1); transform:rotate(-15deg);"></i>
          <div style="position:relative; z-index:2;">
              <h2 style="font-size:24px; font-weight:700; margin-bottom:8px;">Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>! 👋</h2>
              <p style="opacity:0.9; font-size:15px; max-width:550px;">Selamat datang di Dashboard. Akses semua kebutuhan kost Anda di sini dengan mudah dan cepat.</p>
              
              <?php if($tagihan_pending > 0): ?>
                  <div style="margin-top:24px; display:inline-flex; align-items:center; background:rgba(0,0,0,0.2); padding:10px 20px; border-radius:12px; backdrop-filter: blur(5px);">
                      <i class="fa-solid fa-bell" style="color:#fcd34d; margin-right:12px; font-size: 18px;"></i>
                      <span style="font-size:14px; font-weight:500; margin-right: 15px;">Kamu punya <b><?= $tagihan_pending ?> Tagihan</b> belum lunas.</span>
                      <a href="tagihan_saya.php" class="btn btn-sm" style="background:white; color:var(--primary); font-weight:700; border:none; padding: 6px 16px; border-radius: 8px;">Bayar Sekarang</a>
                  </div>
              <?php endif; ?>
          </div>
      </div>

      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin-bottom:30px;">
          
          <div class="card-white" style="display:flex; align-items:center; gap:15px; padding:20px;">
              <div style="width:50px; height:50px; background:#dcfce7; color:#166534; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="fa-solid fa-door-open"></i>
              </div>
              <div>
                  <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Kamar Anda</div>
                  <div style="font-size:18px; font-weight:700; color:#1e293b;"><?= $kontrak['kode_kamar'] ?? '-' ?></div>
              </div>
          </div>

          <div class="card-white" style="display:flex; align-items:center; gap:15px; padding:20px;">
              <div style="width:50px; height:50px; background:#dbeafe; color:#1e40af; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="fa-regular fa-calendar-check"></i>
              </div>
              <div>
                  <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Akhir Sewa</div>
                  <div style="font-size:18px; font-weight:700; color:#1e293b;">
                      <?= $kontrak ? date('d M Y', strtotime($kontrak['tanggal_selesai'])) : '-' ?>
                  </div>
              </div>
          </div>

          <div class="card-white" style="display:flex; align-items:center; gap:15px; padding:20px;">
              <div style="width:50px; height:50px; background:#ffedd5; color:#9a3412; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="fa-solid fa-file-invoice-dollar"></i>
              </div>
              <div>
                  <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Tagihan Aktif</div>
                  <div style="font-size:18px; font-weight:700; color:#1e293b;">
                      <?= $tagihan_pending > 0 ? 'Rp '.number_format($kontrak['harga'] ?? 0) : 'Lunas' ?>
                  </div>
              </div>
          </div>
      </div>

      <div class="card-white">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #f1f5f9;">
              <h3 style="font-size:16px; font-weight:700; color:#1e293b;"><i class="fa-solid fa-bullhorn text-blue-500 mr-2"></i> Pengumuman Terbaru</h3>
              <a href="pengumuman.php" style="font-size:13px; color:#2563eb; text-decoration:none; font-weight:500;">Lihat Semua &rarr;</a>
          </div>

          <?php
            $res = $mysqli->query("SELECT * FROM pengumuman WHERE is_aktif=1 ORDER BY aktif_mulai DESC LIMIT 2");
            if($res->num_rows > 0):
                while($r = $res->fetch_assoc()):
            ?>
              <div style="padding:15px; background:#f8fafc; border-radius:10px; border-left:4px solid #3b82f6; margin-bottom:15px;">
                  <h4 style="font-size:15px; font-weight:700; color:#1e293b; margin-bottom:5px;"><?= htmlspecialchars($r['judul']) ?></h4>
                  <div style="font-size:12px; color:#64748b; margin-bottom:8px;"><i class="fa-regular fa-clock mr-1"></i> <?= date('d M Y', strtotime($r['aktif_mulai'])) ?></div>
                  <p style="font-size:13px; color:#475569; line-height:1.5;"><?= substr(strip_tags($r['isi']), 0, 100) ?>...</p>
              </div>
            <?php endwhile; else: ?>
                <p style="text-align:center; color:#94a3b8; font-style:italic; padding:20px;">Tidak ada pengumuman baru.</p>
            <?php endif; ?>
      </div>

  </main>
</body>
</html>
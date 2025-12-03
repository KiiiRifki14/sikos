<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// PAGINATION
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;
$total_data = $mysqli->query("SELECT COUNT(*) FROM booking")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

$sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar 
        FROM booking b 
        JOIN pengguna g ON b.id_pengguna=g.id_pengguna 
        JOIN kamar k ON b.id_kamar=k.id_kamar 
        ORDER BY b.tanggal_booking DESC 
        LIMIT $halaman_awal, $batas";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Data Booking</h1>
    </div>

    <div class="grid-stats" style="grid-template-columns: 1fr; gap: 20px;">
      <?php
      if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()){
            $statusBg = 'background:#f3f4f6; color:#4b5563;';
            if($row['status'] == 'PENDING') $statusBg = 'background:#fef3c7; color:#d97706;';
            if($row['status'] == 'SELESAI') $statusBg = 'background:#dcfce7; color:#166534;';
            if($row['status'] == 'BATAL') $statusBg = 'background:#fee2e2; color:#dc2626;';
      ?>
        <div class="card-white">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="font-bold text-lg"><?= htmlspecialchars($row['nama']) ?></h4>
                    <div class="text-xs" style="color:var(--text-muted);"><?= date('d M Y H:i', strtotime($row['tanggal_booking'])) ?></div>
                </div>
                <span style="<?= $statusBg ?> padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;"><?= $row['status'] ?></span>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; font-size:13px; color:var(--text-muted); margin-bottom:20px; border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:12px 0;">
                <div>
                    <span class="font-bold block text-xs uppercase mb-1">Kamar</span>
                    <span style="color:var(--text-main);"><?= $row['kode_kamar'] ?></span>
                </div>
                <div>
                    <span class="font-bold block text-xs uppercase mb-1">Durasi</span>
                    <span style="color:var(--text-main);"><?= $row['durasi_bulan_rencana'] ?> Bulan</span>
                </div>
                <div>
                    <span class="font-bold block text-xs uppercase mb-1">Kontak</span>
                    <span style="color:var(--text-main);"><?= $row['no_hp'] ?></span>
                </div>
                <div>
                    <span class="font-bold block text-xs uppercase mb-1">KTP</span>
                    <?php if($row['ktp_path_opt']): ?>
                        <a href="../assets/uploads/ktp/<?= $row['ktp_path_opt'] ?>" target="_blank" style="color:var(--primary);">Lihat</a>
                    <?php else: ?> - <?php endif; ?>
                </div>
            </div>

            <?php if($row['status'] == 'PENDING'): ?>
            <div class="flex gap-2">
                <a href="booking_proses.php?act=approve&id=<?= $row['id_booking'] ?>" class="btn btn-primary w-full text-center" onclick="return confirm('Terima Booking ini?')">✓ Terima</a>
                <a href="booking_proses.php?act=reject&id=<?= $row['id_booking'] ?>" class="btn btn-danger w-full text-center" onclick="return confirm('Tolak Booking ini?')">✕ Tolak</a>
            </div>
            <?php else: ?>
                <div class="text-center text-xs italic" style="color:var(--text-muted);">Booking selesai diproses.</div>
            <?php endif; ?>
        </div>
      <?php 
        } 
      } else {
          echo "<p class='text-center p-8' style='color:var(--text-muted);'>Belum ada data booking.</p>";
      }
      ?>
    </div>

    <div class="flex justify-center mt-6 gap-2">
        <?php for($x = 1; $x <= $total_halaman; $x++): ?>
            <a href="?halaman=<?= $x ?>" class="btn btn-secondary text-xs <?= ($halaman == $x) ? 'btn-primary' : '' ?>" style="padding: 6px 12px;"><?= $x ?></a>
        <?php endfor; ?>
    </div>
  </main>
</body>
</html>
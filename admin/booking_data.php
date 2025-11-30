<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Booking</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Data Booking</h1>

    <div class="grid-stats" style="grid-template-columns: 1fr; gap: 20px;">
      <?php
      $res = $mysqli->query("SELECT b.*, g.nama, g.no_hp, k.kode_kamar FROM booking b JOIN pengguna g ON b.id_pengguna=g.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar ORDER BY b.tanggal_booking DESC");
      
      while($row = $res->fetch_assoc()){
        $statusBg = 'bg-gray-100 text-gray-600';
        if($row['status'] == 'PENDING') $statusBg = 'bg-amber-100 text-amber-700';
        if($row['status'] == 'SELESAI') $statusBg = 'bg-green-100 text-green-700';
        if($row['status'] == 'BATAL') $statusBg = 'bg-red-100 text-red-700';
      ?>
      <div class="card-white">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div>
                <h4 style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama']) ?></h4>
                <div style="font-size:12px; color:#64748b;"><?= date('d M Y', strtotime($row['tanggal_booking'])) ?></div>
            </div>
            <span class="<?= $statusBg ?>" style="padding:4px 12px; border-radius:8px; font-size:11px; font-weight:700;"><?= $row['status'] ?></span>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; font-size:13px; color:#64748b; margin-bottom:20px;">
            <div>
                <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Kamar</span>
                <span style="font-weight:600; color:#1e293b;"><?= $row['kode_kamar'] ?></span>
            </div>
            <div>
                <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Durasi</span>
                <span style="font-weight:600; color:#1e293b;"><?= $row['durasi_bulan_rencana'] ?> Bulan</span>
            </div>
            <div>
                <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Kontak</span>
                <span style="font-weight:600; color:#1e293b;"><?= $row['no_hp'] ?></span>
            </div>
            <div>
                <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">KTP</span>
                <?php if($row['ktp_path_opt']): ?>
                    <a href="../assets/uploads/ktp/<?= $row['ktp_path_opt'] ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                <?php else: ?> - <?php endif; ?>
            </div>
        </div>

        <?php if($row['status'] == 'PENDING'): ?>
        <div style="display:flex; gap:10px;">
            <a href="booking_proses.php?act=approve&id=<?= $row['id_booking'] ?>" class="btn-primary" style="text-decoration:none; width:100%; text-align:center;" onclick="return confirm('Terima?')">✓ Terima</a>
            <a href="booking_proses.php?act=batal&id=<?= $row['id_booking'] ?>" class="btn-secondary" style="text-decoration:none; width:100%; text-align:center; border-color:#fecaca; color:#dc2626;" onclick="return confirm('Tolak?')">✕ Tolak</a>
        </div>
        <?php endif; ?>
      </div>
      <?php } ?>
    </div>
  </main>
</body>
</html>
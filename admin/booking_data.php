<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Booking - SIKOS Admin</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🏠</div>
    <div class="brand-text"><h1>SIKOS</h1><p>ADMIN PANEL</p></div>
  </div>
  <ul class="nav-links">
    <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
    <li><a href="kamar_data.php"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
    <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
    <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
    <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
    <li><a href="laporan.php"><span class="nav-icon">📈</span> Laporan</a></li>
    <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
    <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
  </ul>
</nav>

  <main class="main-content">
    <header class="admin-header">
      <h2>Verifikasi Booking</h2>
    </header>

    <div class="booking-grid">
      <?php
      $res = $mysqli->query("SELECT b.*, g.nama, g.no_hp, k.kode_kamar, k.harga FROM booking b 
        JOIN pengguna g ON b.id_pengguna=g.id_pengguna
        JOIN kamar k ON b.id_kamar=k.id_kamar
        ORDER BY b.tanggal_booking DESC");
      
      while($row = $res->fetch_assoc()){
        $statusClass = 'badge-pending';
        if($row['status'] == 'SELESAI') $statusClass = 'badge-active';
        if($row['status'] == 'BATAL') $statusClass = 'badge-filled';
      ?>
      <div class="booking-card">
        <div class="booking-header">
          <div class="booking-info">
            <h4><?= htmlspecialchars($row['nama']) ?></h4>
            <div class="booking-date">📅 <?= date('d M Y', strtotime($row['tanggal_booking'])) ?></div>
          </div>
          <span class="status-badge <?= $statusClass ?>"><?= $row['status'] ?></span>
        </div>
        
        <div class="booking-details">
          <div class="detail-row">
            <span class="detail-label">Kamar</span>
            <span class="detail-value"><?= $row['kode_kamar'] ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Check-in</span>
            <span class="detail-value"><?= $row['checkin_rencana'] ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Durasi</span>
            <span class="detail-value"><?= $row['durasi_bulan_rencana'] ?> Bulan</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Kontak</span>
            <span class="detail-value"><?= $row['no_hp'] ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-label">KTP</span>
            <span class="detail-value">
                <?php if($row['ktp_path_opt']): ?>
                    <a href="../assets/uploads/ktp/<?= $row['ktp_path_opt'] ?>" target="_blank" style="color:var(--primary);">Lihat</a>
                <?php else: ?> - <?php endif; ?>
            </span>
          </div>
        </div>

        <?php if($row['status'] == 'PENDING'): ?>
        <div class="booking-actions">
  <a href="booking_proses.php?act=approve&id_booking=<?= $row['id_booking'] ?>" class="btn-approve" onclick="return confirm('Terima booking ini?')">
    ✓ Approve
  </a>
  <a href="booking_proses.php?act=batal&id_booking=<?= $row['id_booking'] ?>" class="btn-reject" onclick="return confirm('Tolak booking ini?')">
    ✕ Reject
  </a>
</div>
        <?php endif; ?>
      </div>
      <?php } ?>
    </div>
  </main>
</body>
</html>
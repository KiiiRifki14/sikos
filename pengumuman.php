<?php
session_start();
require 'inc/koneksi.php';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengumuman - SIKOS</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏠</div>
      <div class="brand-text"><h1>SIKOS</h1><p>TENANT AREA</p></div>
    </div>
    <ul class="nav-links">
      <li><a href="penghuni_dashboard.php"><span class="nav-icon">📊</span> Dashboard</a></li>
      <li><a href="kamar_saya.php"><span class="nav-icon">🛏️</span> Kamar Saya</a></li>
      <li><a href="tagihan_saya.php"><span class="nav-icon">💳</span> Tagihan & Bayar</a></li>
      <li><a href="keluhan.php"><span class="nav-icon">🔧</span> Keluhan</a></li>
      <li><a href="pengumuman.php" class="active"><span class="nav-icon">📢</span> Pengumuman</a></li>
      <li style="margin-top: 2rem;"><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
        <h2>Papan Pengumuman</h2>
    </header>

    <div class="booking-grid">
        <?php
        $stmt = $mysqli->prepare("SELECT * FROM pengumuman WHERE is_aktif=1 AND aktif_mulai<=? AND aktif_selesai>=? ORDER BY aktif_mulai DESC");
        $stmt->bind_param('ss', $today, $today);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
        ?>
            <div class="booking-card" style="border-left: 5px solid var(--secondary);">
                <div class="booking-header">
                    <div class="booking-info">
                        <h4 style="color:var(--primary-dark);"><?= htmlspecialchars($row['judul']) ?></h4>
                        <div class="booking-date">Diposting: <?= date('d M Y', strtotime($row['aktif_mulai'])) ?></div>
                    </div>
                    <span class="status-badge badge-active">INFO</span>
                </div>
                <p style="color:var(--text-dark); line-height:1.6;">
                    <?= nl2br(htmlspecialchars($row['isi'])) ?>
                </p>
            </div>
        <?php 
            }
        } else {
            echo '<div class="card-box" style="text-align:center; color:var(--text-muted);">Tidak ada pengumuman aktif saat ini.</div>';
        }
        ?>
    </div>
  </main>
</body>
</html>
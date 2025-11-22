<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Keluhan - SIKOS Admin</title>
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
      <li><a href="keluhan_data.php" class="active"><span class="nav-icon">🔧</span> Komplain</a></li>
      <li><a href="laporan.php"><span class="nav-icon">📈</span> Laporan</a></li>
      <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
      <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <h2 class="page-header-title">Laporan Keluhan</h2>

    <div class="card-box">
      <div class="toolbar">
        <h3>Daftar Masalah & Kerusakan</h3>
        <div class="toolbar-actions">
            <input type="text" placeholder="🔍 Cari keluhan..." class="search-input">
        </div>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>TANGGAL</th>
              <th>PELAPOR</th>
              <th>MASALAH</th>
              <th>PRIORITAS</th>
              <th>STATUS</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Query join ke penghuni & pengguna untuk dapat nama
            $q = "SELECT k.*, p.nama AS nama_penghuni, km.kode_kamar 
                  FROM keluhan k
                  JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
                  JOIN pengguna p ON ph.id_pengguna = p.id_pengguna
                  LEFT JOIN kontrak ko ON ph.id_penghuni = ko.id_penghuni AND ko.status='AKTIF'
                  LEFT JOIN kamar km ON ko.id_kamar = km.id_kamar
                  ORDER BY FIELD(k.status, 'BARU', 'PROSES', 'SELESAI'), k.dibuat_at DESC";
            
            $res = $mysqli->query($q);
            
            while ($row = $res->fetch_assoc()) {
                // Badge Status
                $badge = 'badge-pending'; // Default kuning (BARU)
                if($row['status'] == 'PROSES') $badge = 'bg-light-green'; // Hijau muda
                if($row['status'] == 'SELESAI') $badge = 'badge-active'; // Hijau tua

                // Badge Prioritas
                $prioColor = 'gray';
                if($row['prioritas'] == 'HIGH') $prioColor = 'red';
                if($row['prioritas'] == 'MEDIUM') $prioColor = 'orange';
            ?>
            <tr>
              <td>
                  <div style="font-weight:600;"><?= date('d M Y', strtotime($row['dibuat_at'])) ?></div>
                  <div style="font-size:0.8rem; color:var(--text-muted);"><?= date('H:i', strtotime($row['dibuat_at'])) ?></div>
              </td>
              <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($row['nama_penghuni']) ?></div>
                  <div style="font-size:0.8rem; color:var(--primary);">Kamar <?= $row['kode_kamar'] ?? '-' ?></div>
              </td>
              <td style="max-width: 300px;">
                  <div style="font-weight:600;"><?= htmlspecialchars($row['judul']) ?></div>
                  <div style="font-size:0.85rem; color:var(--text-muted);"><?= htmlspecialchars($row['deskripsi']) ?></div>
              </td>
              <td>
                  <span style="color:<?= $prioColor ?>; font-weight:bold; font-size:0.8rem;">
                    <?= $row['prioritas'] ?>
                  </span>
              </td>
              <td><span class="badge <?= $badge ?>"><?= $row['status'] ?></span></td>
              <td>
                <?php if($row['status'] == 'BARU'): ?>
                    <a href="keluhan_proses.php?act=proses&id=<?= $row['id_keluhan'] ?>" class="btn-solid btn-blue btn-sm">Proses</a>
                <?php elseif($row['status'] == 'PROSES'): ?>
                    <a href="keluhan_proses.php?act=selesai&id=<?= $row['id_keluhan'] ?>" class="btn-solid btn-green btn-sm">Selesai</a>
                <?php else: ?>
                    <span style="color:var(--success);">✔ Tuntas</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
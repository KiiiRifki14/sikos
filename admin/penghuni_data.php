<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Penghuni - SIKOS Admin</title>
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
    <h2 class="page-header-title">Data Penghuni</h2>

    <div class="card-box">
      <div class="toolbar">
        <h3>Data Penghuni</h3>
        <div class="toolbar-actions">
            <input type="text" placeholder="🔍 Cari penghuni..." class="search-input">
        </div>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th width="5%">NO</th>
              <th>NAMA</th>
              <th>KAMAR</th>
              <th>CHECK-IN</th>
              <th>CHECK-OUT</th>
              <th>STATUS</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Query Kompleks untuk mendapatkan detail kamar & kontrak
            $sql = "SELECT p.id_penghuni, u.nama, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
                    FROM penghuni p
                    JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                    LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                    LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
                    ORDER BY u.nama ASC";
            
            $res = $mysqli->query($sql);
            $no = 1;
            while ($row = $res->fetch_assoc()) {
                // Logika Status Badge
                $status = 'INACTIVE';
                $badge = 'bg-light-red';
                
                if ($row['status'] == 'AKTIF') {
                    $status = 'ACTIVE';
                    $badge = 'bg-light-green';
                } elseif ($row['status'] == 'SELESAI') {
                    $status = 'SELESAI';
                    $badge = 'bg-light-red';
                }
            ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($row['nama']) ?></div>
                  <div style="font-size:0.8rem; color:var(--text-muted);">Penghuni</div>
              </td>
              <td><strong><?= $row['kode_kamar'] ?? '-' ?></strong></td>
              <td><?= $row['tanggal_mulai'] ? date('d M Y', strtotime($row['tanggal_mulai'])) : '-' ?></td>
              <td><?= $row['tanggal_selesai'] ? date('d M Y', strtotime($row['tanggal_selesai'])) : '-' ?></td>
              <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
              <td>
                <a href="penghuni_edit.php?id=<?= $row['id_penghuni'] ?>" class="btn-solid btn-sm btn-blue">Edit</a>
                <a href="#" class="btn-solid btn-sm btn-red">Delete</a>
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
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Kamar - SIKOS Admin</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏠</div>
      <div class="brand-text">
        <h1>SIKOS</h1>
        <p>ADMIN PANEL</p>
      </div>
    </div>
    
    <ul class="nav-links">
      <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
      <li><a href="kamar_data.php" class="active"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
      <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
      <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
      <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
      <li><a href="laporan.php"><span class="nav-icon">📈</span> Laporan</a></li>
      <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
    </ul>

    <div class="user-profile">
        <div class="avatar">AD</div>
        <div style="font-size:0.85rem;">
            <div style="font-weight:600;">Admin User</div>
            <div style="opacity:0.7; font-size:0.75rem;">Administrator</div>
        </div>
    </div>
    
    <div style="padding: 0 1.5rem 2rem 1.5rem;">
        <a href="../logout.php" style="color: #cbd5e1; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
  </nav>

  <main class="main-content">
    <h2 class="page-header-title">Data Kamar</h2>

    <div class="card-box">
        <div class="toolbar">
            <h3>Daftar Kamar</h3>
            <div class="toolbar-actions">
                <input type="text" placeholder="🔍 Cari kamar..." class="search-input">
                <a href="kamar_tambah.php" class="btn-solid btn-green">+ Tambah Data Baru</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                  <tr>
                    <th width="5%">NO</th>
                    <th>KODE KAMAR</th>
                    <th>TIPE</th>
                    <th>HARGA</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $res = $mysqli->query("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC");
                while($row = $res->fetch_assoc()){
                    $badgeClass = ($row['status_kamar'] == 'TERSEDIA') ? 'bg-light-green' : 'bg-light-red';
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($row['kode_kamar']) ?></div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">Lantai <?= $row['lantai'] ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['nama_tipe']) ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $row['status_kamar'] ?></span></td>
                    <td>
                      <a href="kamar_edit.php?id=<?= $row['id_kamar'] ?>" class="btn-solid btn-sm btn-blue">Edit</a>
                      <a href="kamar_proses.php?act=hapus&id=<?= $row['id_kamar'] ?>" class="btn-solid btn-sm btn-red" onclick="return confirm('Hapus kamar ini?')">Delete</a>
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
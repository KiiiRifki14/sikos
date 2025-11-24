<?php
session_start();
require '../inc/koneksi.php'; // Didalamnya sudah ada class Database
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

// INSTANSIASI OBJEK (PBO)
$db = new Database();
$data_kamar = $db->tampil_kamar(); // Memanggil method tampil_kamar
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
      <div class="brand-text"><h1>SIKOS</h1><p>ADMIN PANEL</p></div>
    </div>
    <ul class="nav-links">
      <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
      
      <li><a href="pembayaran_data.php"><span class="nav-icon">💰</span> Pembayaran</a></li>
      <li><a href="kamar_data.php"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
      <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
      <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
      <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
      <li><a href="laporan.php" class="active"><span class="nav-icon">📈</span> Laporan</a></li>
      <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
      <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <h2 class="page-header-title">Data Kamar</h2>

    <div class="card-box">
        <div class="toolbar">
            <h3>Daftar Kamar</h3>
            <div class="toolbar-actions">
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
                // LOOPING DATA DARI ARRAY HASIL METHOD CLASS (Foreach)
                foreach($data_kamar as $row){
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
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Pembayaran - SIKOS Admin</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

  <nav class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-text"><h1>SIKOS</h1><p>ADMIN PANEL</p></div>
    </div>
    <ul class="nav-links">
        <li><a href="index.php"><span class="nav-icon">📊</span> Dashboard</a></li>
        <li><a href="pembayaran_data.php" class="active"><span class="nav-icon">💰</span> Pembayaran</a></li>
        <li><a href="kamar_data.php"><span class="nav-icon">🛏️</span> Data Kamar</a></li>
        <li><a href="booking_data.php"><span class="nav-icon">📝</span> Booking</a></li>
        <li><a href="penghuni_data.php"><span class="nav-icon">👥</span> Penghuni</a></li>
        <li><a href="keluhan_data.php"><span class="nav-icon">🔧</span> Komplain</a></li>
        <li><a href="laporan.php"><span class="nav-icon">📈</span> Laporan</a></li>
        <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
        <li><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
      <h2>Verifikasi Pembayaran</h2>
    </header>

    <div class="card-box">
      <h3>Daftar Pembayaran Masuk (Pending)</h3>
      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tipe</th>
              <th>Jumlah</th>
              <th>Bukti</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // PERBAIKAN: Menggunakan ORDER BY id_pembayaran karena created_at tidak ada
            $query = "SELECT p.* FROM pembayaran p WHERE p.status='PENDING' ORDER BY p.id_pembayaran DESC";
            $res = $mysqli->query($query);
            
            if ($res->num_rows > 0) {
                while($row = $res->fetch_assoc()){
            ?>
            <tr>
              <td>#<?= $row['id_pembayaran'] ?></td>
              <td>
                  <span class="badge bg-blue-light"><?= $row['ref_type'] ?></span>
                  <div style="font-size:0.8rem; margin-top:5px;">ID Ref: <?= $row['ref_id'] ?></div>
              </td>
              <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
              <td>
                  <?php if($row['bukti_path']): ?>
                    <a href="../assets/uploads/bukti_tf/<?= htmlspecialchars($row['bukti_path']) ?>" target="_blank" class="btn-solid btn-blue btn-sm">Lihat Bukti</a>
                  <?php else: ?>
                    <span style="color:red;">Tidak ada file</span>
                  <?php endif; ?>
              </td>
              <td><span class="badge badge-pending">PENDING</span></td>
              <td>
                <div style="display:flex; gap:5px;">
                    <a href="pembayaran_proses.php?act=terima&id=<?= $row['id_pembayaran'] ?>" 
                       class="btn-solid btn-green btn-sm" 
                       onclick="return confirm('Konfirmasi pembayaran ini sah?')">✔ Terima</a>
                    
                    <a href="pembayaran_proses.php?act=tolak&id=<?= $row['id_pembayaran'] ?>" 
                       class="btn-solid btn-red btn-sm"
                       onclick="return confirm('Tolak pembayaran ini?')">✕ Tolak</a>
                </div>
              </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada pembayaran baru yang menunggu verifikasi.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="card-box" style="margin-top:2rem;">
        <h3>Riwayat Verifikasi Terakhir</h3>
        <table class="custom-table">
            <thead><tr><th>Tanggal Verif</th><th>Tipe</th><th>Jumlah</th><th>Status</th></tr></thead>
            <tbody>
                <?php
                // Query Riwayat
                $hist = $mysqli->query("SELECT * FROM pembayaran WHERE status!='PENDING' ORDER BY waktu_verifikasi DESC LIMIT 5");
                while($h = $hist->fetch_assoc()){
                    $cls = ($h['status']=='DITERIMA') ? 'badge-active' : 'badge-filled';
                    $tgl = $h['waktu_verifikasi'] ? date('d M Y', strtotime($h['waktu_verifikasi'])) : '-';
                    echo "<tr>
                        <td>$tgl</td>
                        <td>{$h['ref_type']}</td>
                        <td>Rp ".number_format($h['jumlah'])."</td>
                        <td><span class='status-badge $cls'>{$h['status']}</span></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

  </main>
</body>
</html>
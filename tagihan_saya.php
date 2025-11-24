<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php'; // Token keamanan

// Cek Login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil data kontrak aktif untuk penghuni ini
$kontrak = $mysqli->query("SELECT id_kontrak FROM kontrak 
    WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) 
    AND status='AKTIF'")->fetch_assoc();

// Ambil notifikasi status jika ada
$status_msg = '';
if (isset($_GET['status']) && $_GET['status'] == 'sukses') {
    $status_msg = '<div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:1rem;">Bukti pembayaran berhasil dikirim! Menunggu verifikasi admin.</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tagihan Saya - SIKOS</title>
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
      <li><a href="tagihan_saya.php" class="active"><span class="nav-icon">💳</span> Tagihan & Bayar</a></li>
      <li><a href="keluhan.php"><span class="nav-icon">🔧</span> Keluhan</a></li>
      <li><a href="pengumuman.php"><span class="nav-icon">📢</span> Pengumuman</a></li>
      <li style="margin-top: 2rem;"><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
        <h2>Tagihan & Pembayaran</h2>
    </header>

    <div class="data-section">
        <div class="section-header"><h3>Daftar Tagihan Bulanan</h3></div>
        
        <?= $status_msg ?>
        
        <?php if(!$kontrak){ ?>
            <p style='color:var(--text-muted); text-align:center; padding:20px;'>Belum ada kontrak aktif.</p>
        <?php } else { ?>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Nominal</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $mysqli->query("SELECT * FROM tagihan WHERE id_kontrak={$kontrak['id_kontrak']} ORDER BY bulan_tagih DESC");
                
                if ($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()){
                        // Tentukan warna badge status
                        $badgeClass = 'badge-filled'; // Merah (Default)
                        if ($row['status'] == 'LUNAS') $badgeClass = 'badge-active'; // Hijau
                        if ($row['status'] == 'BELUM') $badgeClass = 'badge-filled'; // Merah
                ?>
                    <tr>
                        <td><strong><?= date('F Y', strtotime($row['bulan_tagih'])) ?></strong></td>
                        <td>Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                        <td><span class="status-badge <?= $badgeClass ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <?php if($row['status'] == 'BELUM'): ?>
                                <form method="post" action="pembayaran_tagihan.php" enctype="multipart/form-data" style="display:flex; align-items:center; gap:10px;">
                                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                    
                                    <input type="hidden" name="id_tagihan" value="<?= $row['id_tagihan'] ?>">
                                    <input type="hidden" name="jumlah" value="<?= $row['nominal'] ?>">
                                    
                                    <input type="file" name="bukti" required style="font-size:0.8rem; width:180px;">
                                    <button type="submit" class="btn-solid btn-green" style="padding:6px 12px; font-size:0.8rem;">Upload</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--success); font-weight:bold; font-size:0.9rem;">✔ Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada riwayat tagihan.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
  </main>
</body>
</html>
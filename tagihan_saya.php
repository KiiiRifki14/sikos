<?php
require 'inc/koneksi.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');

$id_pengguna = $_SESSION['id_pengguna'];
$kontrak = $mysqli->query("SELECT id_kontrak FROM kontrak WHERE id_penghuni=(SELECT id_penghuni FROM penghuni WHERE id_pengguna=$id_pengguna) AND status='AKTIF'")->fetch_assoc();
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
        
        <?php if(!$kontrak){ echo "<p style='color:var(--text-muted);'>Belum ada kontrak aktif.</p>"; } else { ?>
        
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
                while($row=$res->fetch_assoc()){
                    $st = $row['status'] == 'LUNAS' ? 'badge-active' : 'badge-filled';
                ?>
                    <tr>
                        <td><strong><?= date('F Y', strtotime($row['bulan_tagih'])) ?></strong></td>
                        <td>Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                        <td><span class="status-badge <?= $st ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <?php if($row['status'] == 'BELUM'): ?>
                                <form method="post" action="pembayaran_tagihan.php" enctype="multipart/form-data" style="display:flex; gap:5px;">
                                    <input type="hidden" name="id_tagihan" value="<?= $row['id_tagihan'] ?>">
                                    <input type="hidden" name="jumlah" value="<?= $row['nominal'] ?>">
                                    <input type="file" name="bukti" required style="font-size:0.8rem; width:180px;">
                                    <button type="submit" class="btn-solid btn-green" style="padding:5px 10px; font-size:0.8rem;">Upload</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--success); font-size:0.9rem;">✔ Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
  </main>
</body>
</html>
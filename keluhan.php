<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');

// Handle Submit Keluhan
$msg = '';
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['act']) && $_POST['act']=='tambah') {
    $judul = htmlspecialchars($_POST['judul']);
    $desk = htmlspecialchars($_POST['deskripsi']);
    $prioritas = $_POST['prioritas'];
    
    $row_penghuni = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna={$_SESSION['id_pengguna']}")->fetch_assoc();
    if ($row_penghuni) {
        $stmt = $mysqli->prepare("INSERT INTO keluhan (id_penghuni, judul, deskripsi, prioritas, status) VALUES (?, ?, ?, ?, 'BARU')");
        $stmt->bind_param('isss', $row_penghuni['id_penghuni'], $judul, $desk, $prioritas);
        $stmt->execute();
        $msg = '<div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:1rem;">Keluhan berhasil dikirim!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keluhan - SIKOS</title>
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
      <li><a href="keluhan.php" class="active"><span class="nav-icon">🔧</span> Keluhan</a></li>
      <li><a href="pengumuman.php"><span class="nav-icon">📢</span> Pengumuman</a></li>
      <li style="margin-top: 2rem;"><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
        <h2>Layanan Keluhan</h2>
    </header>

    <div class="data-section">
        <div class="section-header"><h3>Ajukan Keluhan Baru</h3></div>
        <?= $msg ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="act" value="tambah">
            
            <div class="form-group">
                <label class="form-label">Judul Masalah</label>
                <input type="text" name="judul" class="form-input" placeholder="Contoh: AC Bocor, Lampu Mati" required>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Prioritas</label>
                    <select name="prioritas" class="form-input">
                        <option value="LOW">Rendah (Bisa ditunda)</option>
                        <option value="MEDIUM" selected>Sedang</option>
                        <option value="HIGH">Tinggi (Urgent)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Detail</label>
                    <input type="text" name="deskripsi" class="form-input" placeholder="Jelaskan masalahnya...">
                </div>
            </div>
            
            <button type="submit" class="btn-solid btn-blue">Kirim Keluhan</button>
        </form>
    </div>

    <div class="data-section">
        <div class="section-header"><h3>Riwayat Keluhan</h3></div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Masalah</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $idp = $mysqli->query("SELECT id_penghuni FROM penghuni WHERE id_pengguna={$_SESSION['id_pengguna']}")->fetch_object()->id_penghuni ?? 0;
            $res = $mysqli->query("SELECT * FROM keluhan WHERE id_penghuni=$idp ORDER BY dibuat_at DESC");
            while($row=$res->fetch_assoc()){
                $badge = 'badge-pending';
                if($row['status']=='SELESAI') $badge='badge-active';
                if($row['status']=='PROSES') $badge='bg-light-green'; // Custom color logic
            ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['dibuat_at'])) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['judul']) ?></strong><br>
                        <span style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($row['deskripsi']) ?></span>
                    </td>
                    <td><?= $row['prioritas'] ?></td>
                    <td><span class="status-badge <?= $badge ?>"><?= $row['status'] ?></span></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>
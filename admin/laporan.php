<?php
session_start();
require '../inc/koneksi.php'; // Class Database
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- PROSES GENERATE TAGIHAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] == 'generate') {
    $status = $db->generate_tagihan($_POST['id_kontrak'], $_POST['bulan_tagih']);
    
    if ($status === "DUPLIKAT") {
        echo "<script>alert('Tagihan untuk bulan tersebut sudah ada!'); window.location='laporan.php';</script>";
    } elseif ($status) {
        echo "<script>alert('Berhasil membuat tagihan!'); window.location='laporan.php';</script>";
    } else {
        echo "<script>alert('Gagal membuat tagihan.'); window.location='laporan.php';</script>";
    }
    exit;
}

// --- DATA UNTUK VIEW ---
// Hitung Ringkasan (Bisa dipindah ke Class Database jika mau full OOP, tapi di sini query langsung juga oke untuk report)
$total_masuk = $mysqli->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'")->fetch_row()[0] ?? 0;
$total_tagihan_lunas = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE status='LUNAS'")->fetch_row()[0] ?? 0;
$pending_verif = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0] ?? 0;

// Ambil Data Kontrak untuk Dropdown
$list_kontrak = $db->get_list_kontrak_aktif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Laporan Keuangan - SIKOS Admin</title>
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
      <li><a href="laporan.php" class="active"><span class="nav-icon">📈</span> Laporan</a></li>
      <li><a href="settings.php"><span class="nav-icon">⚙️</span> Settings</a></li>
      <li style="margin-top: 2rem;"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header class="admin-header">
      <h2>Laporan & Statistik</h2>
    </header>

    <div class="report-summary">
        <div class="summary-card">
            <span class="summary-label">Total Pemasukan</span>
            <div class="summary-value text-success">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Tagihan Lunas</span>
            <div class="summary-value text-primary"><?= $total_tagihan_lunas ?></div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Menunggu Verifikasi</span>
            <div class="summary-value text-danger"><?= $pending_verif ?></div>
        </div>
    </div>

    <div class="data-section">
        <div class="section-header">
            <h3>Generate Tagihan Bulanan</h3>
        </div>
        <form method="post" style="display:flex; gap:10px; align-items:end; background:#f8fafc; padding:1.5rem; border-radius:8px;">
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.9rem;">Pilih Kontrak Aktif</label>
                <select name="id_kontrak" class="form-input" required>
                    <option value="">-- Pilih Penghuni & Kamar --</option>
                    <?php foreach($list_kontrak as $k): ?>
                        <option value="<?= $k['id_kontrak'] ?>">
                            <?= $k['kode_kamar'] ?> - <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.9rem;">Untuk Bulan</label>
                <input type="month" name="bulan_tagih" class="form-input" required>
            </div>
            <input type="hidden" name="act" value="generate">
            <button type="submit" class="btn-solid btn-green" style="height:48px;">+ Buat Tagihan</button>
        </form>
    </div>

    <div class="data-section" style="margin-top:2rem;">
        <div class="section-header">
            <h3>Riwayat Pembayaran Terakhir</h3>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pay = $mysqli->query("SELECT * FROM pembayaran ORDER BY id_pembayaran DESC LIMIT 5");
                    while($p = $pay->fetch_assoc()){
                        $st = 'badge-pending';
                        if($p['status']=='DITERIMA') $st = 'badge-active';
                        if($p['status']=='DITOLAK') $st = 'badge-filled';
                    ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($p['created_at'] ?? 'now')) ?></td>
                        <td><?= $p['ref_type'] ?> #<?= $p['ref_id'] ?></td>
                        <td>Rp <?= number_format($p['jumlah'],0,',','.') ?></td>
                        <td><?= $p['metode'] ?></td>
                        <td><span class="status-badge <?= $st ?>"><?= $p['status'] ?></span></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

  </main>
</body>
</html>
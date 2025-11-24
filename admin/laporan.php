<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();
$total_masuk = $mysqli->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'")->fetch_row()[0] ?? 0;
$total_tagihan_lunas = $mysqli->query("SELECT COUNT(*) FROM tagihan WHERE status='LUNAS'")->fetch_row()[0] ?? 0;
$pending_verif = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0] ?? 0;
$list_kontrak = $db->get_list_kontrak_aktif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Laporan Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <aside class="sidebar">
    <div class="mb-8 px-2 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">A</div>
        <div>
            <h1 class="font-bold text-slate-800 text-lg">SIKOS Admin</h1>
            <p class="text-xs text-slate-400">Management Panel</p>
        </div>
    </div>

    <nav style="flex:1; overflow-y:auto;">
        <a href="index.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-blue-500"></i> Dashboard</a>
        <a href="kamar_data.php" class="sidebar-link"><i class="fa-solid fa-house-chimney w-6 text-orange-500"></i> Kelola Kamar</a>
        <a href="booking_data.php" class="sidebar-link"><i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> Booking</a>
        <a href="pembayaran_data.php" class="sidebar-link"><i class="fa-solid fa-sack-dollar w-6 text-yellow-500"></i> Pembayaran</a>
        <a href="penghuni_data.php" class="sidebar-link"><i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni</a>
        <a href="keluhan_data.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain</a>
        <a href="laporan.php" class="sidebar-link active"><i class="fa-solid fa-chart-line w-6 text-teal-500"></i> Laporan</a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan</a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Laporan Keuangan</h1>

    <div class="grid-stats">
        <div class="card-white p-6">
            <div style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-bottom:8px;">Total Pemasukan</div>
            <div style="font-size:28px; font-weight:700; color:#10b981;">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
        </div>
        <div class="card-white p-6">
            <div style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-bottom:8px;">Tagihan Lunas</div>
            <div style="font-size:28px; font-weight:700; color:#2563eb;"><?= $total_tagihan_lunas ?></div>
        </div>
        <div class="card-white p-6">
            <div style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-bottom:8px;">Menunggu Verifikasi</div>
            <div style="font-size:28px; font-weight:700; color:#ef4444;"><?= $pending_verif ?></div>
        </div>
    </div>

    <div class="card-white" style="margin-bottom:32px;">
        <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Generate Tagihan Bulanan</h3>
        <form method="post" style="display:grid; grid-template-columns: 1fr 1fr auto; gap:16px; align-items:end;">
            <div>
                <label class="form-label">Pilih Kontrak Aktif</label>
                <select name="id_kontrak" class="form-input" required>
                    <option value="">-- Pilih Penghuni & Kamar --</option>
                    <?php foreach($list_kontrak as $k): ?>
                        <option value="<?= $k['id_kontrak'] ?>">
                            <?= $k['kode_kamar'] ?> - <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Untuk Bulan</label>
                <input type="month" name="bulan_tagih" class="form-input" required>
            </div>
            <input type="hidden" name="act" value="generate">
            <button type="submit" class="btn-primary" style="height:46px;">+ Buat Tagihan</button>
        </form>
    </div>

  </main>
</body>
</html>
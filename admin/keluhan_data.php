<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Komplain</title>
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
        <a href="index.php" class="sidebar-link"><i class="fa-solid fa-chart-pie w-6 text-blue-500"></i> Dashboard</a>
        <a href="kamar_data.php" class="sidebar-link"><i class="fa-solid fa-house-chimney w-6 text-orange-500"></i> Kelola Kamar</a>
        <a href="booking_data.php" class="sidebar-link"><i class="fa-solid fa-clipboard-list w-6 text-pink-500"></i> Booking</a>
        <a href="pembayaran_data.php" class="sidebar-link"><i class="fa-solid fa-sack-dollar w-6 text-yellow-500"></i> Pembayaran</a>
        <a href="penghuni_data.php" class="sidebar-link"><i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni</a>
        <a href="keluhan_data.php" class="sidebar-link active"><i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain</a>
        <a href="laporan.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-teal-500"></i> Laporan</a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan</a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Laporan Keluhan</h1>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; font-size:12px;">TANGGAL</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">PELAPOR</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">MASALAH</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">PRIORITAS</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">STATUS</th>
                    <th style="padding:16px; color:#64748b; font-size:12px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $q = "SELECT k.*, p.nama AS nama_penghuni, km.kode_kamar 
                  FROM keluhan k
                  JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
                  JOIN pengguna p ON ph.id_pengguna = p.id_pengguna
                  LEFT JOIN kontrak ko ON ph.id_penghuni = ko.id_penghuni AND ko.status='AKTIF'
                  LEFT JOIN kamar km ON ko.id_kamar = km.id_kamar
                  ORDER BY FIELD(k.status, 'BARU', 'PROSES', 'SELESAI'), k.dibuat_at DESC";
            $res = $mysqli->query($q);
            
            while ($row = $res->fetch_assoc()) {
                $badge = 'background:#fef3c7; color:#d97706;'; // Baru
                if($row['status']=='PROSES') $badge = 'background:#dbeafe; color:#2563eb;';
                if($row['status']=='SELESAI') $badge = 'background:#dcfce7; color:#166534;';
                
                $prioColor = ($row['prioritas']=='HIGH') ? '#dc2626' : (($row['prioritas']=='MEDIUM') ? '#d97706' : '#64748b');
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px;">
                    <div style="font-weight:600;"><?= date('d M Y', strtotime($row['dibuat_at'])) ?></div>
                    <div style="font-size:12px; color:#94a3b8;"><?= date('H:i', strtotime($row['dibuat_at'])) ?></div>
                </td>
                <td style="padding:16px;">
                    <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama_penghuni']) ?></div>
                    <div style="font-size:12px; color:#2563eb;">Kamar <?= $row['kode_kamar'] ?? '-' ?></div>
                </td>
                <td style="padding:16px;">
                    <div style="font-weight:600;"><?= htmlspecialchars($row['judul']) ?></div>
                    <div style="font-size:12px; color:#64748b; max-width:250px;"><?= htmlspecialchars($row['deskripsi']) ?></div>
                </td>
                <td style="padding:16px; font-weight:700; color:<?= $prioColor ?>; font-size:12px;"><?= $row['prioritas'] ?></td>
                <td style="padding:16px;">
                    <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; <?= $badge ?>"><?= $row['status'] ?></span>
                </td>
                <td style="padding:16px;">
                    <?php if($row['status'] == 'BARU'): ?>
                        <a href="keluhan_proses.php?act=proses&id=<?= $row['id_keluhan'] ?>" class="btn-primary" style="padding:6px 12px; font-size:11px; text-decoration:none;">Proses</a>
                    <?php elseif($row['status'] == 'PROSES'): ?>
                        <a href="keluhan_proses.php?act=selesai&id=<?= $row['id_keluhan'] ?>" class="btn-primary" style="background:#16a34a; padding:6px 12px; font-size:11px; text-decoration:none;">Selesai</a>
                    <?php else: ?>
                        <span style="font-size:12px; color:#16a34a;">✔ Tuntas</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>
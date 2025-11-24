<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Penghuni</title>
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
        <a href="penghuni_data.php" class="sidebar-link active"><i class="fa-solid fa-users w-6 text-purple-500"></i> Penghuni</a>
        <a href="keluhan_data.php" class="sidebar-link"><i class="fa-solid fa-triangle-exclamation w-6 text-red-500"></i> Komplain</a>
        <a href="laporan.php" class="sidebar-link"><i class="fa-solid fa-chart-line w-6 text-teal-500"></i> Laporan</a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-sliders w-6 text-slate-500"></i> Pengaturan</a>
    </nav>

    <a href="../logout.php" class="sidebar-link text-red-600 hover:bg-red-50 mt-4">
        <i class="fa-solid fa-right-from-bracket w-6"></i> Logout
    </a>
  </aside>

  <main class="main-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Data Penghuni</h1>
        <div class="relative">
            <input type="text" placeholder="Cari penghuni..." style="padding:10px 16px; padding-left:40px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; width:250px;">
            <i class="fa-solid fa-search" style="position:absolute; left:14px; top:13px; color:#94a3b8;"></i>
        </div>
    </div>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Nama</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Kamar</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Periode Sewa</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Status</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
                    FROM penghuni p
                    JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                    LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
                    LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
                    ORDER BY u.nama ASC";
            $res = $mysqli->query($sql);
            
            while ($row = $res->fetch_assoc()) {
                $statusBadge = ($row['status'] == 'AKTIF') 
                    ? '<span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">AKTIF</span>' 
                    : '<span style="background:#f1f5f9; color:#64748b; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">NON-AKTIF</span>';
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px;">
                    <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama']) ?></div>
                    <div style="font-size:12px; color:#64748b;">📞 <?= htmlspecialchars($row['no_hp']) ?></div>
                </td>
                <td style="padding:16px; font-weight:600; color:#1e293b;"><?= $row['kode_kamar'] ?? '-' ?></td>
                <td style="padding:16px;">
                    <?php if($row['tanggal_mulai']): ?>
                        <div style="font-size:13px;"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></div>
                        <div style="font-size:12px; color:#94a3b8;">s/d <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></div>
                    <?php else: ?> - <?php endif; ?>
                </td>
                <td style="padding:16px;"><?= $statusBadge ?></td>
                <td style="padding:16px;">
                    <a href="penghuni_edit.php?id=<?= $row['id_penghuni'] ?>" class="btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none;">Edit</a>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>
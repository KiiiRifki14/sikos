<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// --- LOGIKA PAGINATION ---
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// 1. Hitung Total Data
$total_data = $mysqli->query("SELECT COUNT(*) FROM keluhan")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

// 2. Query Data dengan Limit
$q = "SELECT k.*, p.nama AS nama_penghuni, km.kode_kamar 
      FROM keluhan k
      JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
      JOIN pengguna p ON ph.id_pengguna = p.id_pengguna
      LEFT JOIN kontrak ko ON ph.id_penghuni = ko.id_penghuni AND ko.status='AKTIF'
      LEFT JOIN kamar km ON ko.id_kamar = km.id_kamar
      ORDER BY FIELD(k.status, 'BARU', 'PROSES', 'SELESAI'), k.dibuat_at DESC
      LIMIT $halaman_awal, $batas";

$res = $mysqli->query($q);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Keluhan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      .pagination { display: flex; list-style: none; gap: 5px; margin-top: 20px; justify-content: center; }
      .page-link { 
          padding: 8px 14px; border: 1px solid #e2e8f0; background: white; 
          color: #64748b; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; transition: 0.2s;
      }
      .page-link:hover { background: #f1f5f9; color: #1e293b; }
      .page-item.active .page-link { background: #2563eb; color: white; border-color: #2563eb; }
      .page-item.disabled .page-link { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; }
  </style>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Laporan Keluhan</h1>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; font-size:12px;">NO</th>
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
            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $badge = 'background:#fef3c7; color:#d97706;'; // Baru
                    if($row['status']=='PROSES') $badge = 'background:#dbeafe; color:#2563eb;';
                    if($row['status']=='SELESAI') $badge = 'background:#dcfce7; color:#166534;';
                    
                    $prioColor = ($row['prioritas']=='HIGH') ? '#dc2626' : (($row['prioritas']=='MEDIUM') ? '#d97706' : '#64748b');
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px; color:#64748b; text-align:center; width:50px;"><?= $nomor++ ?></td>
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
                        <span style="font-size:12px; color:#16a34a; font-weight:bold;">✔ Tuntas</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='7' class='text-center py-8 text-slate-400'>Belum ada keluhan masuk.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman > 1) ? "?halaman=".($halaman-1) : '#' ?>">Previous</a>
                </li>
                <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                    <li class="page-item <?= ($halaman == $x) ? 'active' : '' ?>">
                        <a class="page-link" href="?halaman=<?= $x ?>"><?= $x ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman < $total_halaman) ? "?halaman=".($halaman+1) : '#' ?>">Next</a>
                </li>
            </ul>
        </nav>
        
        <div style="text-align:center; margin-top:10px; font-size:12px; color:#94a3b8;">
            Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> keluhan)
        </div>
    </div>
  </main>
</body>
</html>
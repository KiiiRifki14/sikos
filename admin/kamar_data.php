<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();
$data_kamar = $db->tampil_kamar();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Kelola Kamar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Kelola Kamar</h1>
        <a href="kamar_tambah.php" class="btn-primary" style="text-decoration:none; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-plus"></i> Tambah Kamar
        </a>
    </div>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Kode</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Tipe</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Harga</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Status</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($data_kamar as $row){ ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px; font-weight:700; color:#1e293b;">
                        <?= htmlspecialchars($row['kode_kamar']) ?>
                        <div style="font-size:12px; font-weight:400; color:#64748b;">Lantai <?= $row['lantai'] ?></div>
                    </td>
                    <td style="padding:16px;"><?= htmlspecialchars($row['nama_tipe']) ?></td>
                    <td style="padding:16px; font-weight:600;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td style="padding:16px;">
                        <?php if($row['status_kamar']=='TERSEDIA'): ?>
                            <span class="badge-available status-badge">Tersedia</span>
                        <?php else: ?>
                            <span class="badge-occupied status-badge">Terisi</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:16px;">
                        <div style="display:flex; gap:8px;">
                            <a href="kamar_edit.php?id=<?= $row['id_kamar'] ?>" class="btn-secondary" style="padding:6px 12px; font-size:12px; text-decoration:none;">Edit</a>
                            <a href="kamar_proses.php?act=hapus&id=<?= $row['id_kamar'] ?>" class="btn-danger" style="padding:6px 12px; font-size:12px; text-decoration:none;" onclick="return confirm('Hapus?')">Hapus</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
  </main>
</body>
</html>
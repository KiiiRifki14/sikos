<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin
if (!is_admin()) { die('Forbidden'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Kelola Pengumuman - SIKOS Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-bold text-xl">Kelola Pengumuman</h1>
        <a href="pengumuman_proses.php?act=tambah" class="btn btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Tambah Pengumuman
        </a>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Isi Ringkas</th>
                        <th>Periode Tayang</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $mysqli->query("SELECT * FROM pengumuman ORDER BY aktif_mulai DESC");
                $no = 1;
                
                if($res->num_rows > 0) {
                    while($row=$res->fetch_assoc()){
                        // Logic Badge Status
                        $statusBadge = $row['is_aktif'] 
                            ? '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">AKTIF</span>'
                            : '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">NON-AKTIF</span>';
                ?>
                    <tr>
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($row['judul']) ?></div>
                        </td>
                        <td class="text-sm text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars(substr($row['isi'], 0, 60)) ?>...
                        </td>
                        <td class="text-sm">
                            <?= date('d/m/y', strtotime($row['aktif_mulai'])) ?> s/d <?= date('d/m/y', strtotime($row['aktif_selesai'])) ?>
                        </td>
                        <td>
                            <span style="font-size:12px; font-weight:600; color:var(--primary);">
                                <?= $row['audiens'] == 'ALL' ? 'Semua' : 'Penghuni' ?>
                            </span>
                        </td>
                        <td><?= $statusBadge ?></td>
                        <td>
                            <div class="flex gap-2">
                                <a href="pengumuman_proses.php?act=edit&id=<?= $row['id_pengumuman'] ?>" class="btn btn-secondary text-xs" style="padding: 6px 10px;">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="pengumuman_proses.php?act=hapus&id=<?= $row['id_pengumuman'] ?>" class="btn btn-danger text-xs" style="padding: 6px 10px;" onclick="return confirm('Yakin hapus pengumuman ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="7" class="text-center p-8 text-muted">Belum ada data pengumuman.</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
  </main>

</body>
</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin()) { die('Forbidden'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Master Fasilitas - SIKOS Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content animate-fade-up">
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-bold text-xl">Master Fasilitas</h1>
        <a href="fasilitas_form.php" class="btn btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Tambah Baru
        </a>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">ICON</th>
                        <th>NAMA FASILITAS</th>
                        <th>KODE ICON (FONTAWESOME)</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $q = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                if ($q->num_rows > 0) {
                    while ($row = $q->fetch_assoc()) {
                ?>
                    <tr>
                        <td class="text-center" style="font-size: 24px; color: var(--primary);">
                            <i class="fa-solid <?= htmlspecialchars($row['icon']) ?>"></i>
                        </td>
                        <td class="font-bold">
                            <?= htmlspecialchars($row['nama_fasilitas']) ?>
                        </td>
                        <td style="font-family: monospace; color: var(--text-muted);">
                            <?= htmlspecialchars($row['icon']) ?>
                        </td>
                        <td class="text-center">
                            <div class="flex justify-center gap-2">
                                <a href="fasilitas_form.php?id=<?= $row['id_fasilitas'] ?>" 
                                   class="btn btn-secondary text-xs" style="padding: 6px 10px;">Edit</a>
                                
                                <a href="fasilitas_proses.php?act=hapus&id=<?= $row['id_fasilitas'] ?>" 
                                   class="btn btn-danger text-xs" 
                                   style="padding: 6px 10px;"
                                   onclick="return confirm('Yakin hapus fasilitas ini? Semua kamar yang punya fasilitas ini akan kehilangan data fasilitas ini.')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center p-8 text-muted'>Belum ada data fasilitas.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
  </main>
</body>
</html>
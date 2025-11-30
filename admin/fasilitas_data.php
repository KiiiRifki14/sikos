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
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Master Fasilitas</h1>
        <a href="fasilitas_form.php" class="btn-primary">
            <i class="fa-solid fa-plus mr-2"></i> Tambah Baru
        </a>
    </div>

    <div class="card-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-3">Icon</th>
                        <th class="px-6 py-3">Nama Fasilitas</th>
                        <th class="px-6 py-3">Kode Icon (FontAwesome)</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $q = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                if ($q->num_rows > 0) {
                    while ($row = $q->fetch_assoc()) {
                ?>
                    <tr class="bg-white border-b hover:bg-slate-50">
                        <td class="px-6 py-4 text-center">
                            <i class="fa-solid <?= $row['icon'] ?> text-2xl text-blue-600"></i>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-800">
                            <?= htmlspecialchars($row['nama_fasilitas']) ?>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">
                            <?= $row['icon'] ?>
                        </td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <a href="fasilitas_form.php?id=<?= $row['id_fasilitas'] ?>" 
                               class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                            
                            <a href="fasilitas_proses.php?act=hapus&id=<?= $row['id_fasilitas'] ?>" 
                               class="text-red-600 hover:text-red-800 font-medium ml-2"
                               onclick="return confirm('Yakin hapus fasilitas ini? Semua kamar yang punya fasilitas ini akan kehilangan data fasilitas ini.')">Hapus</a>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center py-4'>Belum ada data fasilitas.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
  </main>
</body>
</html>
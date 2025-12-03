<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// PAGINATION
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;
$total_data = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

$sql = "SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe ORDER BY k.kode_kamar ASC LIMIT $halaman_awal, $batas";
$res = $mysqli->query($sql);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Kelola Kamar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-bold text-xl">Kelola Kamar</h1>
        <a href="kamar_tambah.php" class="btn btn-primary text-xs">
            <i class="fa-solid fa-plus"></i> Tambah Kamar
        </a>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $res->fetch_assoc()){ ?>
                    <tr>
                        <td class="text-center" style="color:var(--text-muted);"><?= $nomor++ ?></td>
                        <td>
                            <span class="font-bold"><?= htmlspecialchars($row['kode_kamar']) ?></span>
                            <div class="text-xs" style="color:var(--text-muted);">Lantai <?= $row['lantai'] ?></div>
                        </td>
                        <td><?= htmlspecialchars($row['nama_tipe']) ?></td>
                        <td class="font-bold">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if($row['status_kamar']=='TERSEDIA'): ?>
                                <span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">Tersedia</span>
                            <?php else: ?>
                                <span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">Terisi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="kamar_edit.php?id=<?= $row['id_kamar'] ?>" class="btn btn-secondary text-xs" style="padding: 6px 10px;">Edit</a>
                            <a href="kamar_proses.php?act=hapus&id=<?= $row['id_kamar'] ?>" class="btn btn-danger text-xs" style="padding: 6px 10px;" onclick="return confirm('Hapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-6 gap-2">
            <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                <a href="?halaman=<?= $x ?>" class="btn btn-secondary text-xs <?= ($halaman == $x) ? 'btn-primary' : '' ?>" style="padding: 6px 12px;"><?= $x ?></a>
            <?php endfor; ?>
        </div>
    </div>
  </main>
</body>
</html>
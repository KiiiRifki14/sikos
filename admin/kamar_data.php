<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- LOGIKA PAGINATION ---
$batas = 5; // Jumlah data per halaman (Bisa diganti 10 atau 20)
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// 1. Hitung Total Data (Untuk tahu ada berapa halaman)
$total_data = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

// 2. Ambil Data Sesuai Halaman (LIMIT & OFFSET)
// Kita modifikasi query 'tampil_kamar' manual disini agar bisa dipaginate
$sql = "SELECT k.*, t.nama_tipe 
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe 
        ORDER BY k.kode_kamar ASC 
        LIMIT $halaman_awal, $batas";

$res = $mysqli->query($sql);
$data_kamar = [];
while ($row = $res->fetch_assoc()) {
    $data_kamar[] = $row;
}

$nomor = $halaman_awal + 1; // Untuk penomoran tabel
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Kelola Kamar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* Style untuk Pagination */
      .pagination { display: flex; list-style: none; gap: 5px; margin-top: 20px; justify-content: center; }
      .page-link { 
          padding: 8px 14px; border: 1px solid #e2e8f0; background: white; 
          color: #64748b; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; transition: 0.2s;
      }
      .page-link:hover { background: #f1f5f9; color: #1e293b; }
      .page-item.active .page-link { 
          background: #2563eb; color: white; border-color: #2563eb; 
      }
      .page-item.disabled .page-link { 
          background: #f8fafc; color: #cbd5e1; cursor: not-allowed; 
      }
  </style>
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
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">No</th>
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
                    <td style="padding:16px; width:50px; text-align:center; color:#64748b;"><?= $nomor++ ?></td>
                    <td style="padding:16px; font-weight:700; color:#1e293b;">
                        <?= htmlspecialchars($row['kode_kamar']) ?>
                        <div style="font-size:12px; font-weight:400; color:#64748b;">Lantai <?= $row['lantai'] ?></div>
                    </td>
                    <td style="padding:16px;"><?= htmlspecialchars($row['nama_tipe']) ?></td>
                    <td style="padding:16px; font-weight:600;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td style="padding:16px;">
                        <?php if($row['status_kamar']=='TERSEDIA'): ?>
                            <span class="badge-available status-badge" style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">Tersedia</span>
                        <?php else: ?>
                            <span class="badge-occupied status-badge" style="background:#fee2e2; color:#991b1b; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">Terisi</span>
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
            Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> kamar)
        </div>

    </div>
  </main>
</body>
</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- LOGIKA PAGINATION ---
$batas = 10; // 10 Data per halaman
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// 1. Hitung Total Data
$total_data = $mysqli->query("SELECT COUNT(*) FROM booking")->fetch_row()[0];
$total_halaman = ceil($total_data / $batas);

// 2. Query Data dengan Limit
$sql = "SELECT b.*, g.nama, g.no_hp, k.kode_kamar 
        FROM booking b 
        JOIN pengguna g ON b.id_pengguna=g.id_pengguna 
        JOIN kamar k ON b.id_kamar=k.id_kamar 
        ORDER BY b.tanggal_booking DESC 
        LIMIT $halaman_awal, $batas";

$res = $mysqli->query($sql);
$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Booking</title>
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
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Data Booking</h1>

    <div class="card-white">
      <div class="grid-stats" style="grid-template-columns: 1fr; gap: 20px;">
      <?php
      if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()){
            $statusBg = 'bg-gray-100 text-gray-600';
            if($row['status'] == 'PENDING') $statusBg = 'bg-amber-100 text-amber-700';
            if($row['status'] == 'SELESAI') $statusBg = 'bg-green-100 text-green-700';
            if($row['status'] == 'BATAL') $statusBg = 'bg-red-100 text-red-700';
      ?>
        <div class="border border-slate-100 rounded-xl p-6 hover:shadow-sm transition bg-white">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:#eff6ff; color:#2563eb; font-weight:bold; font-size:12px; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:50%;"><?= $nomor++ ?></span>
                    <div>
                        <h4 style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama']) ?></h4>
                        <div style="font-size:12px; color:#64748b;"><?= date('d M Y H:i', strtotime($row['tanggal_booking'])) ?></div>
                    </div>
                </div>
                <span class="<?= $statusBg ?>" style="padding:4px 12px; border-radius:8px; font-size:11px; font-weight:700;"><?= $row['status'] ?></span>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; font-size:13px; color:#64748b; margin-bottom:20px; border-top:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9; padding:12px 0;">
                <div>
                    <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Kamar</span>
                    <span style="font-weight:600; color:#1e293b;"><?= $row['kode_kamar'] ?></span>
                </div>
                <div>
                    <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Durasi</span>
                    <span style="font-weight:600; color:#1e293b;"><?= $row['durasi_bulan_rencana'] ?> Bulan</span>
                </div>
                <div>
                    <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Kontak</span>
                    <span style="font-weight:600; color:#1e293b;"><?= $row['no_hp'] ?></span>
                </div>
                <div>
                    <span style="display:block; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">KTP</span>
                    <?php if($row['ktp_path_opt']): ?>
                        <a href="../assets/uploads/ktp/<?= $row['ktp_path_opt'] ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1"><i class="fa-solid fa-image"></i> Lihat</a>
                    <?php else: ?> - <?php endif; ?>
                </div>
            </div>

            <?php if($row['status'] == 'PENDING'): ?>
            <div style="display:flex; gap:10px;">
                <a href="booking_proses.php?act=approve&id=<?= $row['id_booking'] ?>" class="btn-primary" style="text-decoration:none; width:100%; text-align:center;" onclick="return confirm('Terima Booking ini?')">✓ Terima</a>
                <a href="booking_proses.php?act=reject&id=<?= $row['id_booking'] ?>" class="btn-secondary" style="text-decoration:none; width:100%; text-align:center; border-color:#fecaca; color:#dc2626;" onclick="return confirm('Tolak Booking ini?')">✕ Tolak</a>
            </div>
            <?php else: ?>
                <div style="text-align:center; font-size:12px; color:#94a3b8; font-style:italic;">
                    Booking selesai diproses.
                </div>
            <?php endif; ?>
        </div>
      <?php 
        } 
      } else {
          echo "<p class='text-center text-slate-400 py-10'>Belum ada data booking.</p>";
      }
      ?>
      </div>

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
            Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> booking)
        </div>
    </div>
  </main>
</body>
</html>
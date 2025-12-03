<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

// --- 1. LOGIKA STATISTIK ---
$total_kamar = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$terisi = $mysqli->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
$occupancy_rate = ($total_kamar > 0) ? round(($terisi / $total_kamar) * 100) : 0;


// --- 2. LOGIKA KEUANGAN ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

// Pemasukan, Pengeluaran, Profit
$omset_raw = $mysqli->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = '$bulan_ini' AND YEAR(waktu_verifikasi) = '$tahun_ini'")->fetch_row()[0] ?? 0;
$keluar_raw = $mysqli->query("SELECT SUM(biaya) FROM pengeluaran WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'")->fetch_row()[0] ?? 0;
$profit_raw = $omset_raw - $keluar_raw;

function format_uang_singkat($angka) {
    if ($angka >= 1000000) return number_format($angka / 1000000, 1) . " Jt";
    return number_format($angka);
}

// --- 3. DATA PENDING ---
$booking = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];
$tagihan_pending = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* Styling Tambahan Khusus Dashboard (Inline karena spesifik) */
      .stat-value { font-size: 2.5rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
      .text-blue { color: var(--primary); }
      .text-green { color: var(--success); }
      .text-red { color: var(--danger); }
      .text-orange { color: var(--warning); }
      
      .alert-box {
          padding: 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
      }
      .alert-orange { background-color: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
      .alert-blue { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="mb-8">
            <h1 class="font-bold" style="font-size: 1.5rem; color: var(--text-main);">Dashboard Owner</h1>
            <p class="text-muted text-sm">Ringkasan performa bisnis kos Anda bulan ini.</p>
        </div>

        <div class="grid-stats">
            <div class="card-white text-center">
                <div class="text-xs font-bold text-muted mb-4 uppercase">Occupancy Rate</div>
                <div class="stat-value text-main"><?= $occupancy_rate ?>%</div>
                <div class="text-xs text-muted"><?= $terisi ?> dari <?= $total_kamar ?> kamar</div>
            </div>

            <div class="card-white text-center">
                <div class="text-xs font-bold text-muted mb-4 uppercase">Pemasukan</div>
                <div class="stat-value text-blue"><?= format_uang_singkat($omset_raw) ?></div>
                <div class="text-xs text-muted">Omset Bulan Ini</div>
            </div>

            <div class="card-white text-center">
                <div class="text-xs font-bold text-muted mb-4 uppercase">Pengeluaran</div>
                <div class="stat-value text-red"><?= format_uang_singkat($keluar_raw) ?></div>
                <div class="text-xs text-muted">Biaya Operasional</div>
            </div>

            <div class="card-white text-center" style="border-left: 4px solid var(--success);">
                <div class="text-xs font-bold text-muted mb-4 uppercase">Laba Bersih</div>
                <div class="stat-value text-green"><?= format_uang_singkat($profit_raw) ?></div>
                <div class="text-xs text-muted">(Masuk - Keluar)</div>
            </div>
        </div> 

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="alert-box alert-orange">
                <div>
                    <div class="font-bold text-lg"><?= $booking ?> Booking Baru</div>
                    <div class="text-sm">Menunggu persetujuan Anda</div>
                </div>
                <a href="booking_data.php" class="btn btn-secondary text-xs">Cek</a>
            </div>

            <div class="alert-box alert-blue">
                <div>
                    <div class="font-bold text-lg"><?= $tagihan_pending ?> Bukti Bayar</div>
                    <div class="text-sm">Perlu verifikasi pembayaran</div>
                </div>
                <a href="keuangan_index.php?tab=verifikasi" class="btn btn-secondary text-xs">Cek</a>
            </div>
        </div>

        <div class="card-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">📝 Booking Terbaru</h3>
                <a href="booking_data.php" class="text-sm" style="color: var(--primary);">Lihat Semua</a>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th>KAMAR</th>
                            <th>TANGGAL</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $bk = $mysqli->query("SELECT b.*, u.nama, u.no_hp, k.kode_kamar, t.nama_tipe FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE b.status='PENDING' ORDER BY b.tanggal_booking DESC LIMIT 5");
                        if($bk->num_rows > 0) {
                            while($b = $bk->fetch_assoc()){
                        ?>
                        <tr>
                            <td>
                                <div class="font-bold"><?= htmlspecialchars($b['nama']) ?></div>
                                <div class="text-xs text-muted">📞 <?= htmlspecialchars($b['no_hp']) ?></div>
                            </td>
                            <td>
                                <div><?= $b['kode_kamar'] ?></div>
                                <div class="text-xs text-muted"><?= $b['nama_tipe'] ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td>
                                <span style="background:#fef3c7; color:#d97706; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:bold;">PENDING</span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="booking_proses.php?act=approve&id=<?= $b['id_booking'] ?>" class="text-xs font-bold text-green" style="text-decoration:none;">✓ Terima</a>
                                    <a href="booking_proses.php?act=reject&id=<?= $b['id_booking'] ?>" class="text-xs font-bold text-red" style="text-decoration:none;">✕ Tolak</a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted p-4'>Tidak ada booking baru.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</body>
</html>
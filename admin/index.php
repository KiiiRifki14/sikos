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

// --- 4. DATA GRAFIK ---
$data_grafik = [];
for ($i = 1; $i <= 12; $i++) {
    $sql_chart = "SELECT SUM(jumlah) FROM pembayaran 
                  WHERE status='DITERIMA' 
                  AND MONTH(waktu_verifikasi) = '$i' 
                  AND YEAR(waktu_verifikasi) = '$tahun_ini'";
    $val = $mysqli->query($sql_chart)->fetch_row()[0] ?? 0;
    $data_grafik[] = $val;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
      .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
      .text-blue { color: var(--primary); }
      .text-green { color: var(--success); }
      .text-red { color: var(--danger); }
      
      .alert-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
      @media (max-width: 768px) { .alert-grid { grid-template-columns: 1fr; } }
      
      .alert-box { padding: 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; }
      .alert-orange { background-color: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
      .alert-blue { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
  </style>
</head>
<body class="dashboard-body">

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content animate-fade-up">
        <div class="mb-8">
            <h1 class="font-bold" style="font-size: 1.5rem;">Dashboard Owner</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Ringkasan performa bisnis kos Anda bulan ini.</p>
        </div>

        <div class="grid-stats">
            <!-- Occupancy Rate -->
            <div class="card-white card-gradient-purple text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Occupancy Rate</div>
                <div class="stat-value" style="font-size: 2.5rem;"><?= $occupancy_rate ?>%</div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);"><?= $terisi ?> dari <?= $total_kamar ?> kamar terisi</div>
                <i class="fa-solid fa-bed" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Pemasukan -->
            <div class="card-white card-gradient-blue text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Pemasukan</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($omset_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Omset Bulan Ini</div>
                <i class="fa-solid fa-wallet" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Pengeluaran -->
            <div class="card-white card-gradient-orange text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Pengeluaran</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($keluar_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Operasional</div>
                <i class="fa-solid fa-money-bill-transfer" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Laba Bersih -->
            <div class="card-white card-gradient-emerald text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Laba Bersih</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($profit_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Profit Bersih</div>
                <i class="fa-solid fa-chart-line" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>
        </div> 

        <div class="alert-grid">
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

        <div class="card-white mb-8">
            <h3 class="font-bold text-lg mb-4">📈 Tren Pendapatan Tahun <?= $tahun_ini ?></h3>
            <div style="position: relative; height:300px; width:100%">
                <canvas id="myChart"></canvas>
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
                                <div class="text-xs" style="color: var(--text-muted);">📞 <?= htmlspecialchars($b['no_hp']) ?></div>
                            </td>
                            <td>
                                <div><?= $b['kode_kamar'] ?></div>
                                <div class="text-xs" style="color: var(--text-muted);"><?= $b['nama_tipe'] ?></div>
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
                            echo "<tr><td colspan='5' class='text-center p-4' style='color:var(--text-muted);'>Tidak ada booking baru.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
      const ctx = document.getElementById('myChart');
      const dataPendapatan = <?= json_encode($data_grafik) ?>; 
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
          datasets: [{
            label: 'Pendapatan (Rp)',
            data: dataPendapatan,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
    </script>
</body>
</html>
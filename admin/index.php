<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) { header('Location: ../login.php'); exit; }

// --- 1. LOGIKA STATISTIK KAMAR ---
$total_kamar = $mysqli->query("SELECT COUNT(*) FROM kamar")->fetch_row()[0];
$terisi = $mysqli->query("SELECT COUNT(*) FROM kamar WHERE status_kamar='TERISI'")->fetch_row()[0];
$occupancy_rate = ($total_kamar > 0) ? round(($terisi / $total_kamar) * 100) : 0;

// --- 2. LOGIKA KEUANGAN (PEMASUKAN & PENGELUARAN) ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

// A. Hitung Pemasukan (Omset)
$query_omset = "SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND MONTH(waktu_verifikasi) = '$bulan_ini' AND YEAR(waktu_verifikasi) = '$tahun_ini'";
$omset_raw = $mysqli->query($query_omset)->fetch_row()[0] ?? 0;

// B. Hitung Pengeluaran (Baru Ditambahkan)
$query_keluar = "SELECT SUM(biaya) FROM pengeluaran WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'";
$keluar_raw = $mysqli->query($query_keluar)->fetch_row()[0] ?? 0;

// C. Hitung Profit (Bersih)
$profit_raw = $omset_raw - $keluar_raw;

// --- 3. FORMAT ANGKA UNTUK TAMPILAN ---
function format_uang_singkat($angka) {
    if ($angka >= 1000000) {
        return number_format($angka / 1000000, 1) . " Jt";
    }
    return number_format($angka);
}

$omset_display = format_uang_singkat($omset_raw);
$keluar_display = format_uang_singkat($keluar_raw);
$profit_display = format_uang_singkat($profit_raw);

// --- 4. DATA GRAFIK (Pendapatan per Bulan tahun ini) ---
$data_grafik = [];
for ($i = 1; $i <= 12; $i++) {
    $sql_chart = "SELECT SUM(jumlah) FROM pembayaran 
                  WHERE status='DITERIMA' 
                  AND MONTH(waktu_verifikasi) = '$i' 
                  AND YEAR(waktu_verifikasi) = '$tahun_ini'";
    $val = $mysqli->query($sql_chart)->fetch_row()[0] ?? 0;
    $data_grafik[] = $val;
}
$json_grafik = json_encode($data_grafik);

// --- 5. DATA PENDING (NOTIFIKASI) ---
$booking = $mysqli->query("SELECT COUNT(*) FROM booking WHERE status='PENDING'")->fetch_row()[0];
$tagihan_pending = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
    <div style="margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Dashboard Owner</h1>
        <p class="text-slate-500 text-sm">Ringkasan performa bisnis kos Anda bulan ini.</p>
    </div>

    <div class="grid-stats mb-8">
        <div class="card-white p-6">
            <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Occupancy Rate</div>
            <div style="font-size:36px; font-weight:700; color:#1e293b; line-height:1; margin-bottom:4px;"><?= $occupancy_rate ?>%</div>
            <div style="font-size:13px; color:#64748b;"><?= $terisi ?> dari <?= $total_kamar ?> kamar</div>
        </div>

        <div class="card-white p-6">
            <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Pemasukan</div>
            <div style="font-size:36px; font-weight:700; color:#2563eb; line-height:1; margin-bottom:4px;"><?= $omset_display ?></div>
            <div style="font-size:13px; color:#64748b;">Omset Bulan ini</div>
        </div>

        <div class="card-white p-6">
            <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Pengeluaran</div>
            <div style="font-size:36px; font-weight:700; color:#ef4444; line-height:1; margin-bottom:4px;"><?= $keluar_display ?></div>
            <div style="font-size:13px; color:#64748b;">Biaya Operasional</div>
        </div>

        <div class="card-white p-6 border-l-4 border-green-500">
            <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Laba Bersih</div>
            <div style="font-size:36px; font-weight:700; color:#16a34a; line-height:1; margin-bottom:4px;"><?= $profit_display ?></div>
            <div style="font-size:13px; color:#64748b;">(Masuk - Keluar)</div>
        </div>
    </div> 

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-amber-800 font-bold text-lg"><?= $booking ?> Booking Baru</div>
                <div class="text-amber-600 text-sm">Menunggu persetujuan Anda</div>
            </div>
            <a href="booking_data.php" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-amber-700">Cek</a>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
            <div>
                <div class="text-blue-800 font-bold text-lg"><?= $tagihan_pending ?> Bukti Bayar</div>
                <div class="text-blue-600 text-sm">Perlu verifikasi pembayaran</div>
            </div>
            <a href="keuangan_index.php?tab=verifikasi" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700">Cek</a>
        </div>
    </div>

    <div class="card-white" style="margin-bottom: 32px;">
        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin-bottom:20px;">
            📈 Tren Pendapatan Tahun <?= $tahun_ini ?>
        </h3>
        <div style="position: relative; height:300px; width:100%">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <script>
      const ctx = document.getElementById('myChart');
      const dataPendapatan = <?= $json_grafik ?>; 

      new Chart(ctx, {
        type: 'line', // Ganti jadi line biar lebih kelihatan tren-nya
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
          datasets: [{
            label: 'Pendapatan (Rp)',
            data: dataPendapatan,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 2,
            tension: 0.4, // Garis melengkung halus
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { borderDash: [2, 2] }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });
    </script>

    <div class="card-white">
        <div class="flex justify-between items-center mb-4">
            <h3 style="font-size:16px; font-weight:700; color:#1e293b;">📝 Booking Terbaru</h3>
            <a href="booking_data.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px; color:#64748b;">NAMA</th>
                    <th style="padding:12px; color:#64748b;">KAMAR</th>
                    <th style="padding:12px; color:#64748b;">TANGGAL</th>
                    <th style="padding:12px; color:#64748b;">STATUS</th>
                    <th style="padding:12px; color:#64748b;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $bk = $mysqli->query("SELECT b.*, u.nama, u.no_hp, k.kode_kamar, t.nama_tipe FROM booking b JOIN pengguna u ON b.id_pengguna=u.id_pengguna JOIN kamar k ON b.id_kamar=k.id_kamar JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE b.status='PENDING' ORDER BY b.tanggal_booking DESC LIMIT 5");
                if($bk->num_rows > 0) {
                    while($b = $bk->fetch_assoc()){
                ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px 12px;">
                        <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($b['nama']) ?></div>
                        <div style="font-size:12px; color:#64748b;">📞 <?= htmlspecialchars($b['no_hp']) ?></div>
                    </td>
                    <td style="padding:16px 12px;">
                        <div><?= $b['kode_kamar'] ?></div>
                        <div style="font-size:12px; color:#64748b;"><?= $b['nama_tipe'] ?></div>
                    </td>
                    <td style="padding:16px 12px;"><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                    <td style="padding:16px 12px;">
                        <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">PENDING</span>
                    </td>
                    <td style="padding:16px 12px;">
                        <div style="display:flex; gap:8px;">
                            <a href="booking_proses.php?act=approve&id=<?= $b['id_booking'] ?>" class="text-green-600 hover:text-green-800 font-bold text-xs border border-green-200 px-2 py-1 rounded">✓ Terima</a>
                            <a href="booking_proses.php?act=reject&id=<?= $b['id_booking'] ?>" class="text-red-600 hover:text-red-800 font-bold text-xs border border-red-200 px-2 py-1 rounded">✕ Tolak</a>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5' style='padding:20px; text-align:center; color:#94a3b8;'>Tidak ada booking baru.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
  </main>

</body>
</html>
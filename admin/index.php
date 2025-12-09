<?php
// [OOP: Session Management] Memulai sesi untuk menyimpan state login pengguna antar halaman
session_start();

// [OOP: Modularization] Mengimpor file koneksi database yang berisi Class Database
require '../inc/koneksi.php';

// [OOP: Modularization] Mengimpor fungsi keamanan untuk mengecek status login & role
require '../inc/guard.php';

// [Security: RBAC] Memastikan hanya pengguna dengan role ADMIN atau OWNER yang bisa mengakses halaman ini
// Jika bukan, paksa redirect ke halaman login
if (!is_admin() && !is_owner()) {
    header('Location: ../login.php');
    exit;
}

// ==========================================================================
// BAGIAN 1: PENGAMBILAN DATA STATISTIK UTAMA (DASHBOARD)
// ==========================================================================
// [OOP: Method Call] Memanggil method khusus untuk menghitung okupansi kamar
// Tujuannya agar logika hitung-hitungan tidak menumpuk di file tampilan (View)
$stats_kamar = $db->get_statistik_kamar();
$occupancy_rate = $stats_kamar['rate']; // Persentase kamar terisi
$terisi = $stats_kamar['terisi'];       // Jumlah kamar ada penghuninya
$total_kamar = $stats_kamar['total'];   // Total seluruh kamar

// ==========================================================================
// BAGIAN 2: LOGIKA KEUANGAN & FILTER TAHUNAN
// ==========================================================================
// Mengambil waktu saat ini sebagai default filter
$bulan_ini = date('m');
$tahun_ini = date('Y');
// Menangkap input filter tahun dari URL (GET request), jika tidak ada gunakan tahun ini
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : $tahun_ini;

// [OOP: Business Logic] Mengambil ringkasan keuangan (Omset, Pengeluaran, Profit)
// Data ini dihitung realtime berdasarkan transaksi di database untuk bulan & tahun terpilih
$stats_uang = $db->get_statistik_keuangan($bulan_ini, $tahun_ini);
$omset_raw = $stats_uang['omset'];
$keluar_raw = $stats_uang['keluar'];
$profit_raw = $stats_uang['profit'];

// Helper Function: Memformat angka jutaan menjadi "X.X Jt" agar lebih rapi di UI card kecil
function format_uang_singkat($angka)
{
    if ($angka >= 1000000) return number_format($angka / 1000000, 1) . " Jt";
    return number_format($angka);
}

// ==========================================================================
// BAGIAN 3: NOTIFIKASI & TUGAS PENDING (TODO LIST ADMIN)
// ==========================================================================
// [OOP: Method Call] Mengambil jumlah tugas yang belum diselesaikan admin
// Contoh: Booking baru yang belum diapprove, atau Tagihan yang belum dibayar
$data_pending = $db->get_pending_counts();
$booking = $data_pending['booking'];
$tagihan_pending = $data_pending['tagihan'];

// ==========================================================================
// BAGIAN 4: PERSIAPAN DATA UNTUK GRAFIK (CHART.JS)
// ==========================================================================
// Menentukan mode grafik: apakah tampil per 'tahunan' (Jan-Des) atau 'bulanan' (tgl 1-31)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'tahunan';
$bulan_pilihan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)$date_now_m = date('m');

if ($mode == 'bulanan') {
    // [Logic] Jika mode bulanan, ambil data pendapatan harian
    // Digunakan untuk melihat performa pendapatan per tanggal spesifik
    $data_grafik = $db->get_chart_pendapatan_harian($bulan_pilihan, $tahun_pilihan);
    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan_pilihan, $tahun_pilihan);
    $labels_grafik = range(1, $jumlah_hari); // Label sumbu X: 1, 2, 3 ... 31
    $chart_label = "Pendapatan Bulan " . date('F', mktime(0, 0, 0, $bulan_pilihan, 10)) . " $tahun_pilihan";
} else {
    // [Logic] Jika mode tahunan, ambil data akumulasi per bulan
    // Digunakan untuk melihat trend bisnis sepanjang tahun
    $data_grafik = $db->get_chart_pendapatan($tahun_pilihan);
    $labels_grafik = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $chart_label = "Pendapatan Tahun $tahun_pilihan";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }

        .text-blue {
            color: var(--primary);
        }

        .text-green {
            color: var(--success);
        }

        .text-red {
            color: var(--danger);
        }

        .alert-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .alert-grid {
                grid-template-columns: 1fr;
            }
        }

        .alert-box {
            padding: 15px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-orange {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            color: #c2410c;
        }

        .alert-blue {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
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

        <!-- GRID STATISTIK UTAMA -->
        <div class="grid-stats">
            <!-- Kartu 1: Occupancy Rate (Keterisian Kamar) -->
            <!-- Memberikan insight cepat seberapa penuh kos saat ini -->
            <div class="card-white card-gradient-purple text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Occupancy Rate</div>
                <div class="stat-value" style="font-size: 2.5rem;"><?= $occupancy_rate ?>%</div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);"><?= $terisi ?> dari <?= $total_kamar ?> kamar terisi</div>
                <i class="fa-solid fa-bed" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Kartu 2: Pemasukan (Omset) -->
            <!-- Total uang masuk dari pembayaran tagihan/DP yang SUDAH diverifikasi -->
            <div class="card-white card-gradient-blue text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Pemasukan</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($omset_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Omset Bulan Ini</div>
                <i class="fa-solid fa-wallet" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Kartu 3: Pengeluaran -->
            <!-- Total biaya operasional (listrik, air, maintenance) yang dicatat bulan ini -->
            <div class="card-white card-gradient-orange text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Pengeluaran</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($keluar_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Operasional</div>
                <i class="fa-solid fa-money-bill-transfer" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>

            <!-- Kartu 4: Laba Bersih (Profit) -->
            <!-- Selisih Pemasukan - Pengeluaran. Indikator kesehatan bisnis. -->
            <div class="card-white card-gradient-emerald text-center" style="border:none;">
                <div class="text-xs font-bold mb-2 uppercase" style="color: rgba(255,255,255,0.8);">Laba Bersih</div>
                <div class="stat-value" style="font-size: 1.8rem;"><?= format_uang_singkat($profit_raw) ?></div>
                <div class="text-xs" style="color: rgba(255,255,255,0.8);">Profit Bersih</div>
                <i class="fa-solid fa-chart-line" style="position:absolute; right:10px; bottom:10px; font-size:40px; opacity:0.2;"></i>
            </div>
        </div>

        <!-- ALERT / NOTIFIKASI PENDING -->
        <!-- Bagian ini muncul untuk mengingatkan admin ada tugas yang harus segera diproses -->
        <div class="alert-grid">
            <!-- Notifikasi Booking Baru -->
            <div class="alert-box alert-orange">
                <div>
                    <div class="font-bold text-lg"><?= $booking ?> Booking Baru</div>
                    <div class="text-sm">Menunggu persetujuan Anda</div>
                </div>
                <!-- Tombol aksi langsung ke halaman booking -->
                <a href="booking_data.php" class="btn btn-secondary text-xs">Cek</a>
            </div>

            <!-- Notifikasi Pembayaran Masuk -->
            <div class="alert-box alert-blue">
                <div>
                    <div class="font-bold text-lg"><?= $tagihan_pending ?> Bukti Bayar</div>
                    <div class="text-sm">Perlu verifikasi pembayaran</div>
                </div>
                <!-- Tombol aksi langsung ke halaman verifikasi keuangan -->
                <a href="keuangan_index.php?tab=verifikasi" class="btn btn-secondary text-xs">Cek</a>
            </div>
        </div>

        <!-- GRAFIK PENDAPATAN (CANVAS CHART.JS) -->
        <div class="card-white mb-8">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                <div>
                    <h3 class="font-bold text-lg">📈 Tren Pendapatan</h3>
                    <div class="flex gap-2 mt-2">
                        <!-- Toggle Mode Grafik: Tahunan vs Bulanan -->
                        <a href="?mode=tahunan&tahun=<?= $tahun_pilihan ?>" class="btn text-xs <?= $mode == 'tahunan' ? 'btn-primary' : 'btn-secondary' ?>">Tahunan</a>
                        <a href="?mode=bulanan&bulan=<?= $bulan_ini ?>&tahun=<?= $tahun_pilihan ?>" class="btn text-xs <?= $mode == 'bulanan' ? 'btn-primary' : 'btn-secondary' ?>">Bulanan</a>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($mode == 'bulanan'): ?>
                        <!-- Navigasi Prev/Next Bulan untuk Grafik Bulanan -->
                        <?php
                        $prev_m = $bulan_pilihan - 1;
                        $prev_y = $tahun_pilihan;
                        if ($prev_m < 1) { // Mundur tahun jika < Januari
                            $prev_m = 12;
                            $prev_y--;
                        }

                        $next_m = $bulan_pilihan + 1;
                        $next_y = $tahun_pilihan;
                        if ($next_m > 12) { // Maju tahun jika > Desember
                            $next_m = 1;
                            $next_y++;
                        }
                        ?>
                        <div class="flex items-center bg-slate-100 rounded p-1 gap-2">
                            <a href="?mode=bulanan&bulan=<?= $prev_m ?>&tahun=<?= $prev_y ?>" class="btn btn-secondary text-xs p-1 px-2"><i class="fa-solid fa-chevron-left"></i></a>
                            <span class="font-bold text-sm px-4"><?= date('F', mktime(0, 0, 0, $bulan_pilihan, 10)) ?> <?= $tahun_pilihan ?></span>
                            <a href="?mode=bulanan&bulan=<?= $next_m ?>&tahun=<?= $next_y ?>" class="btn btn-secondary text-xs p-1 px-2"><i class="fa-solid fa-chevron-right"></i></a>
                        </div>
                    <?php else: ?>
                        <!-- Navigasi Prev/Next Tahun untuk Grafik Tahunan -->
                        <div class="flex items-center bg-slate-100 rounded p-1 gap-2">
                            <a href="?mode=tahunan&tahun=<?= $tahun_pilihan - 1 ?>" class="btn btn-secondary text-xs p-1 px-2" title="Tahun Lalu">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                            <span class="font-bold text-sm px-4"><?= $tahun_pilihan ?></span>
                            <a href="?mode=tahunan&tahun=<?= $tahun_pilihan + 1 ?>" class="btn btn-secondary text-xs p-1 px-2" title="Tahun Depan">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div style="position: relative; height:300px; width:100%">
                <!-- Canvas tempat Chart.js dirender -->
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <!-- TABEL LIST BOOKING TERBARU -->
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
                        // [OOP: Method Call] Mengambil data booking terbaru limit 5
                        $bk = $db->get_booking_terbaru(5);
                        if (count($bk) > 0) {
                            foreach ($bk as $b) {
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
                                            <!-- Link Aksi: Approve/Reject -->
                                            <!-- Menggunakan fungsi JS konfirmasiAksi() untuk mencegah salah klik -->
                                            <a href="booking_proses.php?act=approve&id=<?= $b['id_booking'] ?>" class="text-xs font-bold text-green" style="text-decoration:none;" onclick="konfirmasiAksi(event, 'Terima booking dari <?= $b['nama'] ?>?', this.href)">✓ Terima</a>
                                            <a href="booking_proses.php?act=reject&id=<?= $b['id_booking'] ?>" class="text-xs font-bold text-red" style="text-decoration:none;" onclick="konfirmasiAksi(event, 'Tolak booking dari <?= $b['nama'] ?>?', this.href)">✕ Tolak</a>
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

    <!-- SCRIPT INFOGRAFIS (Chart.js) -->
    <script>
        const ctx = document.getElementById('myChart');
        // Mengambil data PHP ke JavaScript menggunakan json_encode
        const dataPendapatan = <?= json_encode($data_grafik) ?>;
        const labelsGrafik = <?= json_encode($labels_grafik) ?>;
        const labelDataset = "<?= $chart_label ?>";

        new Chart(ctx, {
            type: 'line', // Jenis grafik garis
            data: {
                labels: labelsGrafik,
                datasets: [{
                    label: labelDataset,
                    data: dataPendapatan,
                    borderColor: '#2563eb', // Warna garis biru 
                    backgroundColor: 'rgba(37, 99, 235, 0.1)', // Warna area bawah garis transparan
                    borderWidth: 2,
                    tension: 0.4, // Membuat garis melengkung (smooth)
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Sembunyikan legenda dataset
                    },
                    tooltip: {
                        callbacks: {
                            // Custom format mata uang IDR di tooltip saat hover
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true // Sumbu Y dimulai dari 0
                    }
                }
            }
        });
    </script>
</body>

</html>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// Ambil Tab Aktif (Default: verifikasi)
$tab = $_GET['tab'] ?? 'verifikasi';

// --- LOGIKA DATA LAPORAN ---
if ($tab == 'laporan') {
    $bulan_ini = date('Y-m');
    $total_masuk = $db->get_total_pembayaran_masuk();

    $bulan_lap = $_GET['bulan'] ?? date('Y-m');

    // Total Masuk Bulan Ini
    $total_masuk_bln = $db->get_total_pembayaran_masuk($bulan_lap);

    // Total Keluar Bulan Ini (Untuk Laba Bersih)
    $total_keluar_bln = $db->get_total_pengeluaran($bulan_lap);
}

// --- LOGIKA DATA TAGIHAN (PAGINATION) ---
if ($tab == 'tagihan') {
    $bulan_filter = $_GET['bulan'] ?? date('Y-m');

    $batas = 10;
    $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    if ($halaman < 1) $halaman = 1;
    $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

    $total_data = $db->count_tagihan_by_month($bulan_filter);
    $total_halaman = ceil($total_data / $batas);
    $nomor = $halaman_awal + 1;
}

// --- LOGIKA DATA PENGELUARAN (PAGINATION) ---
if ($tab == 'pengeluaran') {
    $batas = 10;
    $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

    $total_data = $db->count_pengeluaran();
    $total_halaman = ceil($total_data / $batas);

    // Query Data Pengeluaran
    $res_pengeluaran = $db->get_pengeluaran_paginated($halaman_awal, $batas);
    $nomor = $halaman_awal + 1;

    // Hitung Total Pengeluaran Bulan Ini (Statistik)
    $bulan_ini = date('Y-m');
    $total_keluar = $db->get_total_pengeluaran($bulan_ini);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Pusat Keuangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styling Tab Menu Navigasi */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            overflow-x: auto;
            white-space: nowrap;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1px;
        }

        .tab-btn {
            padding: 12px 20px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            text-decoration: none;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            background: none;
        }

        .tab-btn:hover {
            color: var(--primary);
            background: #f8fafc;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-btn span {
            display: inline-block;
        }

        /* Modal Style (Khusus Tab Pengeluaran) */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media print {

            .sidebar,
            .sidebar-toggle,
            .tabs,
            .no-print,
            form,
            .btn {
                display: none !important;
            }

            .dashboard-body {
                display: block !important;
                background: white !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .card-white {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="dashboard-body">
    <a href="?tab=tagihan" class="tab-btn <?= $tab == 'tagihan' ? 'active' : '' ?>">
        <i class="fa-solid fa-file-invoice-dollar"></i> <span>Kelola Tagihan</span>
    </a>

    <a href="?tab=pengeluaran" class="tab-btn <?= $tab == 'pengeluaran' ? 'active' : '' ?>">
        <i class="fa-solid fa-wallet"></i> <span>Pengeluaran Operasional</span>
    </a>

    <a href="?tab=laporan" class="tab-btn <?= $tab == 'laporan' ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i> <span>Laporan Keuangan</span>
    </a>
    </div>

    <?php if ($tab == 'verifikasi'): ?>
        <div class="card-white">
            <h3 class="font-bold text-lg mb-4">Menunggu Konfirmasi</h3>
            <div style="overflow-x: auto;">
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th>TIPE</th>
                            <th>JUMLAH</th>
                            <th>BUKTI</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $db->get_pembayaran_pending();
                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td>
                                        <div class="font-bold"><?= htmlspecialchars($row['nama'] ?? 'User') ?></div>
                                        <span class="text-xs" style="background:#eff6ff; color:var(--primary); padding:2px 6px; border-radius:4px;"><?= $row['ref_type'] ?></span>
                                    </td>
                                    <td class="font-bold">Rp <?= number_format($row['jumlah']) ?></td>
                                    <td>
                                        <?php if (!empty($row['bukti_path'])): ?>
                                            <a href="../view_file.php?type=bukti&file=<?= htmlspecialchars($row['bukti_path']) ?>" target="_blank" class="btn btn-sm btn-secondary text-xs">
                                                <i class="fa-solid fa-image"></i> Bukti
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($row['ktp_path_opt'])): ?>
                                            <a href="../view_file.php?type=ktp&file=<?= htmlspecialchars($row['ktp_path_opt']) ?>" target="_blank" class="btn btn-sm btn-secondary text-xs">
                                                <i class="fa-solid fa-id-card"></i> KTP
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($row['bukti_path']) && empty($row['ktp_path_opt'])) echo '<span class="text-muted">-</span>'; ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="pembayaran_proses.php?act=terima&id=<?= $row['id_pembayaran'] ?>" class="btn btn-primary text-xs" style="padding:6px 10px;" onclick="konfirmasiAksi(event, 'Terima dan verifikasi pembayaran sebesar Rp <?= number_format($row['jumlah']) ?>?', this.href)">✓ Terima</a>
                                            <a href="pembayaran_proses.php?act=tolak&id=<?= $row['id_pembayaran'] ?>" class="btn btn-danger text-xs" style="padding:6px 10px;" onclick="konfirmasiAksi(event, 'Yakin ingin menolak pembayaran ini?', this.href)">✕ Tolak</a>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='4' class='text-center p-8 text-muted'>Tidak ada antrian verifikasi.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($tab == 'tagihan'): ?>
        <?php $bulan_filter = $_GET['bulan'] ?? date('Y-m'); ?>
        <div>
            <div class="card-white mb-6">
                <h3 class="font-bold text-lg mb-4">Generate Tagihan Masal</h3>
                <form method="post" action="pembayaran_proses.php?act=generate_masal" class="flex gap-4 items-end flex-wrap">
                    <div style="flex:1; min-width: 200px;">
                        <label class="form-label">Untuk Bulan</label>
                        <input type="month" name="bulan_tagih" value="<?= $bulan_filter ?>" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="konfirmasiForm(event, 'Sistem akan membuat tagihan untuk SEMUA penghuni aktif pada bulan terpilih. Pastikan harga kamar sudah benar. Lanjutkan?')">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Tagihan
                    </button>
                </form>
                <p class="text-xs text-muted mt-2">*Harga diambil otomatis dari Data Kamar masing-masing penghuni.</p>
            </div>

            <div class="card-white">
                <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                    <h3 class="font-bold text-lg">Daftar Tagihan (<?= date('F Y', strtotime($bulan_filter)) ?>)</h3>
                    <?php
                    $prev_mo = date('Y-m', strtotime($bulan_filter . ' -1 month'));
                    $next_mo = date('Y-m', strtotime($bulan_filter . ' +1 month'));
                    ?>
                    <div class="flex items-center gap-2">
                        <a href="?tab=tagihan&bulan=<?= $prev_mo ?>" class="btn btn-secondary text-xs" style="padding: 8px 10px;" title="Bulan Sebelumnya">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>

                        <form method="get" class="flex gap-2">
                            <input type="hidden" name="tab" value="tagihan">
                            <input type="month" name="bulan" value="<?= $bulan_filter ?>" onchange="this.form.submit()" class="form-input text-sm" style="padding:6px;">
                        </form>

                        <a href="?tab=tagihan&bulan=<?= $next_mo ?>" class="btn btn-secondary text-xs" style="padding: 8px 10px;" title="Bulan Selanjutnya">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th>PENGHUNI</th>
                                <th>KAMAR</th>
                                <th>NOMINAL</th>
                                <th>STATUS</th>
                                <th>AKSI (MANUAL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res_tagihan = $db->get_tagihan_by_month_paginated($bulan_filter, $halaman_awal, $batas);

                            if ($res_tagihan->num_rows > 0) {
                                while ($t = $res_tagihan->fetch_assoc()) {
                                    $statusBadge = ($t['status'] == 'LUNAS')
                                        ? '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">LUNAS</span>'
                                        : '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">BELUM</span>';
                            ?>
                                    <tr>
                                        <td class="font-bold"><?= htmlspecialchars($t['nama']) ?></td>
                                        <td><?= $t['kode_kamar'] ?></td>
                                        <td>Rp <?= number_format($t['nominal']) ?></td>
                                        <td><?= $statusBadge ?></td>
                                        <td>
                                            <?php if ($t['status'] == 'BELUM'): ?>
                                                <div class="flex gap-2">
                                                    <a href="pembayaran_proses.php?act=bayar_cash&id=<?= $t['id_tagihan'] ?>" class="btn btn-secondary text-xs" style="padding:4px 8px;"
                                                        onclick="konfirmasiAksi(event, 'Konfirmasi pembayaran TUNAI dari <?= htmlspecialchars($t['nama']) ?> senilai Rp <?= number_format($t['nominal']) ?>?', this.href)">
                                                        💰 Terima Cash
                                                    </a>
                                                    <a href="pembayaran_proses.php?act=hapus_tagihan&id=<?= $t['id_tagihan'] ?>" class="btn btn-danger text-xs" style="padding:4px 8px;"
                                                        onclick="konfirmasiAksi(event, 'Yakin hapus tagihan ini? (Hanya bisa untuk yang belum lunas)', this.href)">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-xs text-muted">-</span>
                                                <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php }
                            } else {
                                echo "<tr><td colspan='5' class='text-center p-8 text-muted'>Belum ada tagihan. Silakan Generate.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
                <?php
                // Previous
                $qs = $_GET;
                $prev = max(1, $halaman - 1);
                $next = min($total_halaman, $halaman + 1);

                $qs['halaman'] = $prev;
                $href_prev = ($halaman > 1) ? '?' . http_build_query($qs) : '#';

                // Next
                $qs['halaman'] = $next;
                $href_next = ($halaman < $total_halaman) ? '?' . http_build_query($qs) : '#';
                ?>

                <a href="<?= $href_prev ?>"
                    class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>"
                    style="padding:6px 12px;">
                    <i class="fa-solid fa-chevron-left"></i> Prev
                </a>

                <?php for ($x = 1; $x <= $total_halaman; $x++):
                    $qs = $_GET;
                    $qs['halaman'] = $x;
                    $href_page = '?' . http_build_query($qs);
                ?>
                    <a href="<?= $href_page ?>"
                        class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>"
                        style="padding:6px 12px;"><?= $x ?></a>
                <?php endfor; ?>

                <a href="<?= $href_next ?>"
                    class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>"
                    style="padding:6px 12px;">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

            <div class="text-center mt-4 text-xs text-muted">
                Halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> data)
            </div>
        </div>
    <?php endif; ?>

    <?php if ($tab == 'pengeluaran'): ?>
        <div class="animate-fade-up">
            <div class="flex justify-between items-center mb-8 flex-wrap gap-4" style="margin-bottom: 32px;">
                <div>
                    <h3 class="font-bold text-lg">Daftar Pengeluaran</h3>
                    <p class="text-xs text-muted">Catat biaya listrik, air, perbaikan, dll.</p>
                </div>
                <button onclick="openModal()" class="btn btn-primary text-xs">
                    <i class="fa-solid fa-plus"></i> Catat Pengeluaran
                </button>
            </div>

            <div class="grid-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 32px;">

                <div class="card-white flex items-center gap-4" style="border-left: 4px solid var(--danger); padding: 24px;">
                    <div class="text-3xl text-red-500"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div>
                        <div class="text-xs font-bold text-muted uppercase">Total Keluar (Bulan Ini)</div>
                        <div class="text-xl font-bold text-main">Rp <?= number_format($total_keluar) ?></div>
                    </div>
                </div>
            </div>

            <div class="card-white">
                <div style="overflow-x: auto;">
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>TANGGAL</th>
                                <th>KEPERLUAN</th>
                                <th>BIAYA</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($res_pengeluaran->num_rows > 0) {
                                while ($row = $res_pengeluaran->fetch_assoc()):
                            ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $nomor++ ?></td>
                                        <td class="font-bold text-sm"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                        <td>
                                            <div class="font-bold"><?= htmlspecialchars($row['judul']) ?></div>
                                            <div class="text-xs text-muted"><?= htmlspecialchars($row['deskripsi']) ?></div>
                                        </td>
                                        <td class="font-bold" style="color:var(--danger);">Rp <?= number_format($row['biaya']) ?></td>
                                        <td class="text-center">
                                            <a href="pengeluaran_proses.php?act=hapus&id=<?= $row['id_pengeluaran'] ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-danger text-xs" style="padding: 6px 10px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                            <?php endwhile;
                            } else {
                                echo "<tr><td colspan='5' class='text-center p-8 text-muted'>Belum ada data pengeluaran.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
                    <a href="<?= ($halaman > 1) ? "?tab=pengeluaran&halaman=" . ($halaman - 1) : '#' ?>"
                        class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>" style="padding:6px 12px;">
                        <i class="fa-solid fa-chevron-left"></i> Prev
                    </a>

                    <?php for ($x = 1; $x <= $total_halaman; $x++): ?>
                        <a href="?tab=pengeluaran&halaman=<?= $x ?>"
                            class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>" style="padding:6px 12px;">
                            <?= $x ?>
                        </a>
                    <?php endfor; ?>

                    <a href="<?= ($halaman < $total_halaman) ? "?tab=pengeluaran&halaman=" . ($halaman + 1) : '#' ?>"
                        class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>" style="padding:6px 12px;">
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div id="modalAdd" class="modal">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4 border-b pb-2" style="border-color: var(--border);">
                        <h3 class="font-bold text-lg">Catat Pengeluaran Baru</h3>
                        <span onclick="closeModal()" style="cursor:pointer; font-size:24px; color:var(--text-muted);">&times;</span>
                    </div>
                    <form action="pengeluaran_proses.php" method="POST">
                        <input type="hidden" name="act" value="tambah">
                        <input type="hidden" name="redirect" value="keuangan_index.php?tab=pengeluaran">

                        <div class="form-group mb-4">
                            <label class="form-label block mb-1 text-sm font-bold">Judul Keperluan</label>
                            <input type="text" name="judul" class="form-input w-full" placeholder="Contoh: Bayar Listrik..." required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label class="form-label block mb-1 text-sm font-bold">Tanggal</label>
                                <input type="date" name="tanggal" class="form-input w-full" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div>
                                <label class="form-label block mb-1 text-sm font-bold">Biaya (Rp)</label>
                                <input type="number" name="biaya" class="form-input w-full" placeholder="0" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label block mb-1 text-sm font-bold">Catatan Detail (Opsional)</label>
                            <textarea name="deskripsi" class="form-input w-full" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">Simpan Data</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tab == 'laporan'): ?>
            <?php
            // Hitung Laba Bersih
            $laba_bersih = $total_masuk_bln - $total_keluar_bln;
            ?>

            <div id="area-print">
                <div class="card-white no-print mb-6 flex justify-between items-center flex-wrap gap-4">
                    <form method="get" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="laporan">
                        <label class="font-bold text-sm text-muted">Periode:</label>
                        <input type="month" name="bulan" value="<?= $bulan_lap ?>" class="form-input text-sm" style="padding: 8px;" onchange="this.form.submit()">
                    </form>

                    <div class="flex gap-2">
                        <a href="laporan_export.php?bulan=<?= $bulan_lap ?>" target="_blank" class="btn btn-success text-xs" style="background:#16a34a; color:white; border:none; padding: 8px 12px;">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>
                        <button onclick="window.print()" class="btn btn-secondary text-xs" style="padding: 8px 12px;">
                            <i class="fa-solid fa-print"></i> Print / PDF
                        </button>
                    </div>
                </div>

                <div class="grid-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 32px; gap: 20px;">
                    <div class="card-white text-center" style="border-bottom: 4px solid var(--success); padding: 24px;">
                        <div class="text-xs font-bold text-muted mb-2 uppercase">Total Pemasukan</div>
                        <div class="text-2xl font-bold text-green-600">
                            Rp <?= number_format($total_masuk_bln, 0, ',', '.') ?>
                        </div>
                        <div class="text-xs text-muted mt-1"><?= date('F Y', strtotime($bulan_lap)) ?></div>
                    </div>

                    <div class="card-white text-center" style="border-bottom: 4px solid var(--danger); padding: 24px;">
                        <div class="text-xs font-bold text-muted mb-2 uppercase">Total Pengeluaran</div>
                        <div class="text-2xl font-bold text-red-500">
                            Rp <?= number_format($total_keluar_bln, 0, ',', '.') ?>
                        </div>
                        <div class="text-xs text-muted mt-1"><?= date('F Y', strtotime($bulan_lap)) ?></div>
                    </div>

                    <div class="card-white text-center" style="border-bottom: 4px solid var(--primary); padding: 24px;">
                        <div class="text-xs font-bold text-muted mb-2 uppercase">Laba Bersih</div>
                        <div class="text-2xl font-bold <?= $laba_bersih >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                            Rp <?= number_format($laba_bersih, 0, ',', '.') ?>
                        </div>
                        <div class="text-xs text-muted mt-1">Cash Flow Bulan Ini</div>
                    </div>
                </div>

                <div class="card-white">
                    <h3 class="font-bold text-lg mb-6">Rincian Arus Kas (Cash Flow)</h3>
                    <div style="overflow-x: auto;">
                        <table style="width:100%;">
                            <thead>
                                <tr style="border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding: 12px;">TANGGAL</th>
                                    <th style="padding: 12px;">KETERANGAN</th>
                                    <th style="padding: 12px;">JENIS</th>
                                    <th style="padding: 12px; text-align:right;">NOMINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // QUERY COMPLEX: Menggabungkan (UNION) Pemasukan dan Pengeluaran
                                // Bagian 1: Ambil Pemasukan
                                $res_union = $db->get_cash_flow_report($bulan_lap);

                                if ($res_union && $res_union->num_rows > 0) {
                                    while ($d = $res_union->fetch_assoc()) {
                                        $is_masuk = ($d['tipe'] == 'MASUK');
                                        $color = $is_masuk ? 'text-green-600' : 'text-red-500';
                                        $sign = $is_masuk ? '+' : '-';
                                        $bg_badge = $is_masuk ? '#dcfce7' : '#fee2e2';
                                        $text_badge = $is_masuk ? '#166534' : '#991b1b';
                                ?>
                                        <tr style="border-bottom: 1px solid #f8fafc;">
                                            <td style="padding: 12px;"><?= date('d/m/Y', strtotime($d['tgl'])) ?></td>
                                            <td style="padding: 12px;">
                                                <div class="font-bold text-sm text-slate-700"><?= htmlspecialchars($d['deskripsi']) ?></div>
                                                <div class="text-xs text-muted"><?= $d['metode'] ?></div>
                                            </td>
                                            <td style="padding: 12px;">
                                                <span style="background:<?= $bg_badge ?>; color:<?= $text_badge ?>; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:bold;">
                                                    <?= $d['tipe'] ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; text-align:right;" class="font-bold <?= $color ?>">
                                                <?= $sign ?> Rp <?= number_format($d['nominal']) ?>
                                            </td>
                                        </tr>
                                <?php }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center p-8 text-muted'>Belum ada transaksi bulan ini.</td></tr>";
                                } ?>
                            </tbody>
                            <tfoot style="background-color: #f8fafc; font-weight: bold;">
                                <tr>
                                    <td colspan="3" style="padding: 16px; text-align: left;">LABA BERSIH :</td>
                                    <td style="padding: 16px; text-align: right; font-size: 1.1em;" class="<?= $laba_bersih >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                                        Rp <?= number_format($laba_bersih) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- ... kode PHP/HTML di atas ... -->

        <!-- TAMBAHKAN SCRIPT INI DI BAGIAN PALING BAWAH -->
        <script>
            function openModal() {
                var modal = document.getElementById('modalAdd');
                if (modal) {
                    modal.style.display = 'block';
                } else {
                    alert('Error: Modal tidak ditemukan di halaman ini.');
                }
            }

            function closeModal() {
                var modal = document.getElementById('modalAdd');
                if (modal) modal.style.display = 'none';
            }

            // Tutup modal jika user klik di luar area putih
            window.onclick = function(e) {
                var modal = document.getElementById('modalAdd');
                if (e.target == modal) {
                    closeModal();
                }
            }
        </script>
</body>

</html>
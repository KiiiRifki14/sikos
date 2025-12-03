<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// Ambil Tab Aktif (Default: verifikasi)
$tab = $_GET['tab'] ?? 'verifikasi';

// Data untuk Tab Laporan (Statistik Sederhana)
if ($tab == 'laporan') {
    $bulan_ini = date('Y-m');
    $total_masuk = $mysqli->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'")->fetch_row()[0] ?? 0;
    
    // Filter Bulan Laporan
    $bulan_lap = $_GET['bulan'] ?? date('Y-m');
    
    // Hitung Total Masuk Bulan Terpilih
    $q_total_bln = "SELECT SUM(jumlah) FROM pembayaran 
                    WHERE status='DITERIMA' 
                    AND DATE_FORMAT(waktu_verifikasi, '%Y-%m') = '$bulan_lap'";
    $total_masuk_bln = $mysqli->query($q_total_bln)->fetch_row()[0] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Keuangan & Tagihan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
  <style>
      /* Styling Tab Menu */
      .tabs { display: flex; gap: 10px; margin-bottom: 24px; overflow-x: auto; white-space: nowrap; border-bottom: 1px solid var(--border); padding-bottom: 1px; }
      .tab-btn { 
          padding: 10px 20px; 
          font-weight: 600; 
          color: var(--text-muted); 
          border-bottom: 2px solid transparent; 
          text-decoration: none; 
          transition: 0.2s;
      }
      .tab-btn:hover { color: var(--primary); background: #f8fafc; }
      .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

      /* CSS Khusus Print */
      @media print {
          .sidebar, .sidebar-toggle, .tabs, .no-print, form, .btn { display: none !important; }
          .dashboard-body { display: block !important; background: white !important; }
          .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
          .card-white { box-shadow: none !important; border: 1px solid #000 !important; }
          
          /* Pastikan background warna (seperti header tabel) ikut tercetak */
          body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          
          h1 { text-align: center; margin-bottom: 20px; font-size: 18pt; }
      }
  </style>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="mb-8 no-print">
        <h1 class="font-bold text-xl">Keuangan & Tagihan</h1>
    </div>

    <div class="tabs no-print">
        <a href="?tab=verifikasi" class="tab-btn <?= $tab=='verifikasi'?'active':'' ?>">
            <i class="fa-solid fa-magnifying-glass"></i> Verifikasi Pembayaran
        </a>
        <a href="?tab=tagihan" class="tab-btn <?= $tab=='tagihan'?'active':'' ?>">
            <i class="fa-solid fa-file-invoice-dollar"></i> Kelola Tagihan Bulanan
        </a>
        <a href="?tab=laporan" class="tab-btn <?= $tab=='laporan'?'active':'' ?>">
            <i class="fa-solid fa-chart-line"></i> Laporan Keuangan
        </a>
    </div>

    <?php if($tab == 'verifikasi'): ?>
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
                    $q = "SELECT p.*, u.nama FROM pembayaran p 
                          LEFT JOIN tagihan t ON p.ref_id = t.id_tagihan AND p.ref_type='TAGIHAN'
                          LEFT JOIN booking b ON p.ref_id = b.id_booking AND p.ref_type='BOOKING'
                          LEFT JOIN kontrak k ON t.id_kontrak = k.id_kontrak
                          LEFT JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
                          LEFT JOIN pengguna u ON (ph.id_pengguna = u.id_pengguna OR b.id_pengguna = u.id_pengguna)
                          WHERE p.status='PENDING' ORDER BY p.id_pembayaran DESC";
                    
                    $res = $mysqli->query($q);
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                    ?>
                    <tr>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($row['nama'] ?? 'User') ?></div>
                            <span class="text-xs" style="background:#eff6ff; color:var(--primary); padding:2px 6px; border-radius:4px;"><?= $row['ref_type'] ?></span>
                        </td>
                        <td class="font-bold">Rp <?= number_format($row['jumlah']) ?></td>
                        <td>
                            <?php if($row['bukti_path']): ?>
                                <a href="../assets/uploads/bukti_tf/<?= $row['bukti_path'] ?>" target="_blank" class="text-xs btn btn-secondary" style="padding:4px 8px;">
                                    <i class="fa-solid fa-image"></i> Lihat
                                </a>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="pembayaran_proses.php?act=terima&id=<?= $row['id_pembayaran'] ?>" class="btn btn-primary text-xs" style="padding:6px 10px;" onclick="return confirm('Terima pembayaran ini?')">✓ Terima</a>
                                <a href="pembayaran_proses.php?act=tolak&id=<?= $row['id_pembayaran'] ?>" class="btn btn-danger text-xs" style="padding:6px 10px;" onclick="return confirm('Tolak pembayaran ini?')">✕ Tolak</a>
                            </div>
                        </td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='4' class='text-center p-8 text-muted'>Tidak ada antrian verifikasi.</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if($tab == 'tagihan'): ?>
        <?php $bulan_filter = $_GET['bulan'] ?? date('Y-m'); ?>
        
        <div class="card-white mb-6">
            <h3 class="font-bold text-lg mb-4">Generate Tagihan Masal</h3>
            <form method="post" action="pembayaran_proses.php?act=generate_masal" class="flex gap-4 items-end flex-wrap">
                <div style="flex:1; min-width: 200px;">
                    <label class="form-label">Untuk Bulan</label>
                    <input type="month" name="bulan_tagih" value="<?= $bulan_filter ?>" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Sistem akan membuat tagihan untuk SEMUA penghuni aktif pada bulan terpilih. Lanjutkan?')">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Tagihan
                </button>
            </form>
            <p class="text-xs text-muted mt-2">*Harga diambil otomatis dari Data Kamar masing-masing penghuni.</p>
        </div>

        <div class="card-white">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                <h3 class="font-bold text-lg">Daftar Tagihan (<?= date('F Y', strtotime($bulan_filter)) ?>)</h3>
                <form method="get" class="flex gap-2">
                    <input type="hidden" name="tab" value="tagihan">
                    <input type="month" name="bulan" value="<?= $bulan_filter ?>" onchange="this.form.submit()" class="form-input text-sm" style="padding:6px;">
                </form>
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
                    $q_tagihan = "SELECT t.*, u.nama, k.kode_kamar 
                                  FROM tagihan t 
                                  JOIN kontrak ko ON t.id_kontrak = ko.id_kontrak
                                  JOIN penghuni p ON ko.id_penghuni = p.id_penghuni
                                  JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                                  JOIN kamar k ON ko.id_kamar = k.id_kamar
                                  WHERE t.bulan_tagih = '$bulan_filter'
                                  ORDER BY u.nama ASC";
                    $res_tagihan = $mysqli->query($q_tagihan);
                    
                    if($res_tagihan->num_rows > 0) {
                        while($t = $res_tagihan->fetch_assoc()){
                            $statusBadge = ($t['status']=='LUNAS') 
                                ? '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">LUNAS</span>'
                                : '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">BELUM</span>';
                    ?>
                    <tr>
                        <td class="font-bold"><?= htmlspecialchars($t['nama']) ?></td>
                        <td><?= $t['kode_kamar'] ?></td>
                        <td>Rp <?= number_format($t['nominal']) ?></td>
                        <td><?= $statusBadge ?></td>
                        <td>
                            <?php if($t['status'] == 'BELUM'): ?>
                                <a href="pembayaran_proses.php?act=bayar_cash&id=<?= $t['id_tagihan'] ?>" 
                                   class="btn btn-secondary text-xs" 
                                   style="padding:4px 8px;"
                                   onclick="return confirm('Konfirmasi pembayaran TUNAI dari <?= $t['nama'] ?> senilai Rp <?= number_format($t['nominal']) ?>?')">
                                   💰 Terima Cash
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='5' class='text-center p-8 text-muted'>Belum ada tagihan. Silakan Generate.</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if($tab == 'laporan'): ?>
        <div id="area-print"> 
            <div class="grid-stats">
                <div class="card-white text-center">
                    <div class="text-xs font-bold text-muted mb-2 uppercase">PEMASUKAN <?= date('F Y', strtotime($bulan_lap)) ?></div>
                    <div class="text-2xl font-bold" style="color:var(--success);">Rp <?= number_format($total_masuk_bln, 0, ',', '.') ?></div>
                </div>
                
                <div class="card-white text-center">
                    <div class="text-xs font-bold text-muted mb-2 uppercase">TOTAL PENDAPATAN (SEMUA WAKTU)</div>
                    <div class="text-2xl font-bold" style="color:var(--primary);">Rp <?= number_format($total_masuk, 0, ',', '.') ?></div>
                </div>

                <div class="card-white no-print flex flex-col justify-center gap-4">
                    <form method="get" class="flex justify-center">
                        <input type="hidden" name="tab" value="laporan">
                        <input type="month" name="bulan" value="<?= $bulan_lap ?>" class="form-input w-full text-center" onchange="this.form.submit()">
                    </form>
                    
                    <div class="flex gap-2">
                        <a href="laporan_export.php?bulan=<?= $bulan_lap ?>" target="_blank" class="btn btn-success w-full text-center" style="background:#16a34a; color:white; border:none;">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>
                        <button onclick="window.print()" class="btn btn-secondary w-full">
                            <i class="fa-solid fa-print"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-white">
                <h3 class="font-bold text-lg mb-4">Rincian Transaksi</h3>
                <div style="overflow-x: auto;">
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Pemasukan
                            $sql_prev = "SELECT p.id_pembayaran, p.waktu_verifikasi, p.jumlah, p.metode, p.ref_type, u.nama, km.kode_kamar 
                                         FROM pembayaran p
                                         LEFT JOIN booking b ON p.ref_id=b.id_booking AND p.ref_type='BOOKING'
                                         LEFT JOIN tagihan t ON p.ref_id=t.id_tagihan AND p.ref_type='TAGIHAN'
                                         LEFT JOIN kontrak k ON t.id_kontrak=k.id_kontrak
                                         LEFT JOIN kamar km ON (b.id_kamar = km.id_kamar OR k.id_kamar = km.id_kamar)
                                         LEFT JOIN penghuni ph ON k.id_penghuni=ph.id_penghuni
                                         LEFT JOIN pengguna u ON (ph.id_pengguna=u.id_pengguna OR b.id_pengguna=u.id_pengguna)
                                         WHERE p.status='DITERIMA' 
                                         AND DATE_FORMAT(p.waktu_verifikasi, '%Y-%m') = '$bulan_lap'
                                         ORDER BY p.waktu_verifikasi DESC";
                            $res_prev = $mysqli->query($sql_prev);
                            
                            if($res_prev->num_rows > 0){
                                while($d = $res_prev->fetch_assoc()){
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($d['waktu_verifikasi'])) ?></td>
                                <td>
                                    <div class="font-bold"><?= htmlspecialchars($d['nama'] ?? 'User') ?></div>
                                    <div class="text-xs text-muted">
                                        <?= $d['ref_type'] ?> - Kamar <?= $d['kode_kamar'] ?? '-' ?>
                                    </div>
                                </td>
                                <td><?= $d['metode'] ?></td>
                                <td class="font-bold" style="color:var(--success);">+ Rp <?= number_format($d['jumlah']) ?></td>
                            </tr>
                            <?php }} else { echo "<tr><td colspan='4' class='text-center p-4 text-muted'>Belum ada data pemasukan bulan ini.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

  </main>
</body>
</html>
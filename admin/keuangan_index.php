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
    $total_masuk = $mysqli->query("SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA' AND ref_type='TAGIHAN'")->fetch_row()[0] ?? 0;
    $total_pending = $mysqli->query("SELECT COUNT(*) FROM pembayaran WHERE status='PENDING'")->fetch_row()[0] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Keuangan & Tagihan</title>

  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      .tab-btn { padding: 10px 20px; font-weight: 600; border-bottom: 2px solid transparent; color: #64748b; }
      .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
      .tab-btn:hover { color: #1e293b; }

      /* === TAMBAHAN: CSS KHUSUS PRINT === */
      @media print {
          /* 1. Sembunyikan elemen yang tidak perlu dicetak */
          .sidebar, 
          .tab-btn, 
          .no-print, 
          button, 
          form,
          a.btn-primary { 
              display: none !important; 
          }

          /* 2. Atur ulang layout agar Full Width (memenuhi kertas) */
          .dashboard-body {
              display: block !important;
              background-color: white !important;
          }

          .main-content {
              margin-left: 0 !important; /* Hilangkan margin kiri bekas sidebar */
              width: 100% !important;
              padding: 0 !important;
          }

          /* 3. Rapikan tampilan kartu/tabel */
          .card-white {
              box-shadow: none !important;
              border: 1px solid #ccc !important;
              break-inside: avoid; /* Mencegah tabel terpotong di tengah halaman */
          }

          /* 4. Pastikan background warna (seperti header tabel) ikut tercetak */
          body {
              -webkit-print-color-adjust: exact; 
              print-color-adjust: exact;
          }
          
          /* Judul Halaman saat Print */
          h1 {
              text-align: center;
              margin-bottom: 20px;
          }
      }
  </style>
</head>
<body class="dashboard-body">
  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
     </main>
</body>
    <div style="margin-bottom:24px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Keuangan & Tagihan</h1>
    </div>

    <div style="border-bottom: 1px solid #e2e8f0; margin-bottom: 24px; display:flex; gap: 10px;">
        <a href="?tab=verifikasi" class="tab-btn <?= $tab=='verifikasi'?'active':'' ?>">🔍 Verifikasi Pembayaran</a>
        <a href="?tab=tagihan" class="tab-btn <?= $tab=='tagihan'?'active':'' ?>">📝 Kelola Tagihan Bulanan</a>
        <a href="?tab=laporan" class="tab-btn <?= $tab=='laporan'?'active':'' ?>">📊 Laporan Keuangan</a>
    </div>

    <?php if($tab == 'verifikasi'): ?>
        <div class="card-white">
            <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Menunggu Konfirmasi</h3>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc; text-align:left;">
                        <th style="padding:12px; color:#64748b;">Tipe</th>
                        <th style="padding:12px; color:#64748b;">Jumlah</th>
                        <th style="padding:12px; color:#64748b;">Bukti</th>
                        <th style="padding:12px; color:#64748b;">Aksi</th>
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
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px;">
                        <div style="font-weight:700;"><?= $row['nama'] ?? 'User' ?></div>
                        <span style="font-size:11px; background:#eff6ff; color:#2563eb; padding:2px 6px; rounded;"><?= $row['ref_type'] ?></span>
                    </td>
                    <td style="padding:16px;">Rp <?= number_format($row['jumlah']) ?></td>
                    <td style="padding:16px;">
                        <?php if($row['bukti_path']): ?>
                            <a href="../assets/uploads/bukti_tf/<?= $row['bukti_path'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td style="padding:16px; display:flex; gap:8px;">
                        <a href="pembayaran_proses.php?act=terima&id=<?= $row['id_pembayaran'] ?>" class="btn-primary" style="padding:6px 12px; font-size:12px; text-decoration:none;" onclick="return confirm('Terima pembayaran?')">✓</a>
                        <a href="pembayaran_proses.php?act=tolak&id=<?= $row['id_pembayaran'] ?>" class="btn-danger" style="padding:6px 12px; font-size:12px; text-decoration:none;" onclick="return confirm('Tolak pembayaran?')">✕</a>
                    </td>
                </tr>
                <?php }} else { echo "<tr><td colspan='4' class='p-4 text-center text-slate-400'>Tidak ada antrian verifikasi.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if($tab == 'tagihan'): ?>
        <?php 
            $bulan_filter = $_GET['bulan'] ?? date('Y-m'); 
        ?>
        <div class="card-white mb-6">
            <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Generate Tagihan Masal</h3>
            <form method="post" action="pembayaran_proses.php?act=generate_masal" style="display:flex; gap:16px; align-items:end;">
                <div>
                    <label class="form-label">Untuk Bulan</label>
                    <input type="month" name="bulan_tagih" value="<?= $bulan_filter ?>" class="form-input">
                </div>
                <button type="submit" class="btn-primary" onclick="return confirm('Sistem akan membuat tagihan untuk SEMUA penghuni aktif pada bulan terpilih. Lanjutkan?')">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Tagihan
                </button>
            </form>
            <p style="font-size:12px; color:#64748b; margin-top:8px;">*Harga diambil otomatis dari Data Kamar.</p>
        </div>

        <div class="card-white">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-weight:700; color:#1e293b;">Daftar Tagihan (<?= date('F Y', strtotime($bulan_filter)) ?>)</h3>
                <form method="get">
                    <input type="hidden" name="tab" value="tagihan">
                    <input type="month" name="bulan" value="<?= $bulan_filter ?>" onchange="this.form.submit()" class="border px-2 py-1 rounded text-sm">
                </form>
            </div>
            
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc; text-align:left;">
                        <th style="padding:12px; color:#64748b;">Penghuni</th>
                        <th style="padding:12px; color:#64748b;">Kamar</th>
                        <th style="padding:12px; color:#64748b;">Nominal</th>
                        <th style="padding:12px; color:#64748b;">Status</th>
                        <th style="padding:12px; color:#64748b;">Aksi (Manual)</th>
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
                            ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">LUNAS</span>'
                            : '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">BELUM</span>';
                ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px; font-weight:600;"><?= htmlspecialchars($t['nama']) ?></td>
                    <td style="padding:12px;"><?= $t['kode_kamar'] ?></td>
                    <td style="padding:12px;">Rp <?= number_format($t['nominal']) ?></td>
                    <td style="padding:12px;"><?= $statusBadge ?></td>
                    <td style="padding:12px;">
                        <?php if($t['status'] == 'BELUM'): ?>
                            <a href="pembayaran_proses.php?act=bayar_cash&id=<?= $t['id_tagihan'] ?>" 
                               class="btn-secondary" 
                               style="font-size:11px; padding:4px 8px; text-decoration:none;"
                               onclick="return confirm('Konfirmasi pembayaran TUNAI dari <?= $t['nama'] ?> senilai Rp <?= number_format($t['nominal']) ?>?')">
                               💰 Terima Cash
                            </a>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php }} else { echo "<tr><td colspan='5' class='p-4 text-center text-slate-400'>Belum ada tagihan untuk bulan ini. Silakan Generate.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if($tab == 'laporan'): ?>
        <?php 
            // PERBAIKAN LOGIKA: Hapus 'AND ref_type=TAGIHAN' agar booking fee ikut terhitung
            $q_total_semua = "SELECT SUM(jumlah) FROM pembayaran WHERE status='DITERIMA'";
            $total_masuk_semua = $mysqli->query($q_total_semua)->fetch_row()[0] ?? 0;

            // Filter Bulan Laporan
            $bulan_lap = $_GET['bulan'] ?? date('Y-m');
            
            // Hitung Total Masuk Bulan Terpilih
            $q_total_bln = "SELECT SUM(jumlah) FROM pembayaran 
                            WHERE status='DITERIMA' 
                            AND DATE_FORMAT(waktu_verifikasi, '%Y-%m') = '$bulan_lap'";
            $total_masuk_bln = $mysqli->query($q_total_bln)->fetch_row()[0] ?? 0;
        ?>

        <div id="area-print"> 
            <div class="grid-stats">
                <div class="card-white p-6">
                    <div style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                        Pemasukan <?= date('F Y', strtotime($bulan_lap)) ?>
                    </div>
                    <div style="font-size:28px; font-weight:700; color:#10b981;">
                        Rp <?= number_format($total_masuk_bln, 0, ',', '.') ?>
                    </div>
                </div>
                
                <div class="card-white p-6">
                    <div style="font-size:12px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                        Total Pendapatan (Semua Waktu)
                    </div>
                    <div style="font-size:28px; font-weight:700; color:#2563eb;">
                        Rp <?= number_format($total_masuk_semua, 0, ',', '.') ?>
                    </div>
                </div>

                <div class="card-white p-6 no-print" style="display:flex; flex-direction:column; justify-content:center; gap:10px;">
                    <form method="get" style="display:flex; gap:10px; align-items:center;">
                        <input type="hidden" name="tab" value="laporan">
                        <input type="month" name="bulan" value="<?= $bulan_lap ?>" class="form-input" onchange="this.form.submit()">
                    </form>
                    
                    <div style="display:flex; gap:8px;">
                        <a href="laporan_export.php?bulan=<?= $bulan_lap ?>" target="_blank" class="btn-primary" style="flex:1; text-align:center; text-decoration:none; background:#16a34a;">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>
                        <button onclick="window.print()" class="btn-secondary" style="flex:1;">
                            <i class="fa-solid fa-print"></i> PDF / Print
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-white">
                <h3 style="font-weight:700; color:#1e293b; margin-bottom:16px;">Rincian Transaksi</h3>
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#f8fafc; text-align:left; border-bottom:2px solid #e2e8f0;">
                            <th style="padding:12px;">Tanggal</th>
                            <th style="padding:12px;">Keterangan</th>
                            <th style="padding:12px;">Metode</th>
                            <th style="padding:12px;">Jumlah</th>
                            <th style="padding:12px;" class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // UPDATE QUERY: Menambahkan p.id_pembayaran agar bisa dicetak
                        $sql_prev = "SELECT p.id_pembayaran, p.waktu_verifikasi, p.jumlah, p.metode, p.ref_type, u.nama, km.kode_kamar 
                                     FROM pembayaran p
                                     LEFT JOIN booking b ON p.ref_id=b.id_booking AND p.ref_type='BOOKING'
                                     LEFT JOIN tagihan t ON p.ref_id=t.id_tagihan AND p.ref_type='TAGIHAN'
                                     LEFT JOIN kontrak k ON t.id_kontrak=k.id_kontrak
                                     -- Join Kamar: Ambil dari Booking (b) ATAU Kontrak (k)
                                     LEFT JOIN kamar km ON (b.id_kamar = km.id_kamar OR k.id_kamar = km.id_kamar)
                                     LEFT JOIN penghuni ph ON k.id_penghuni=ph.id_penghuni
                                     LEFT JOIN pengguna u ON (ph.id_pengguna=u.id_pengguna OR b.id_pengguna=u.id_pengguna)
                                     WHERE p.status='DITERIMA' 
                                     AND DATE_FORMAT(p.waktu_verifikasi, '%Y-%m') = '$bulan_lap'
                                     ORDER BY p.waktu_verifikasi DESC LIMIT 10";
                        $res_prev = $mysqli->query($sql_prev);
                        
                        if($res_prev->num_rows > 0){
                            while($d = $res_prev->fetch_assoc()){
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px;"><?= date('d/m/Y', strtotime($d['waktu_verifikasi'])) ?></td>
                            <td style="padding:12px;">
                                <div style="font-weight:600;"><?= $d['nama'] ?? 'User' ?></div>
                                <div style="font-size:11px; color:#64748b;">
                                    <?= $d['ref_type'] ?> - Kamar <?= $d['kode_kamar'] ?? '-' ?>
                                </div>
                            </td>
                            <td style="padding:12px;"><?= $d['metode'] ?></td>
                            <td style="padding:12px; font-weight:600; color:#10b981;">+ Rp <?= number_format($d['jumlah']) ?></td>
                            
                            <td style="padding:12px;" class="no-print">
                                <a href="../cetak_kuitansi.php?id=<?= $d['id_pembayaran'] ?>" 
                                   target="_blank" 
                                   style="color:#2563eb; font-size:16px;"
                                   title="Cetak Kuitansi">
                                   <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php }} else { echo "<tr><td colspan='5' class='p-4 text-center text-slate-400'>Belum ada data pemasukan bulan ini.</td></tr>"; } ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    <?php endif; ?>
    </div>

  </main>
</body>
</html>
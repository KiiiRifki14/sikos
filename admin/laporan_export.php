<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Akses
if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak");
}

// Ambil parameter bulan
$bulan = $_GET['bulan'] ?? date('Y-m');
$nama_bulan = date('F Y', strtotime($bulan));

// SET HEADER EXCEL
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Keuangan_SIKOS_$bulan.xls");
header("Pragma: no-cache");
header("Expires: 0");

// 1. Query Pemasukan
$sql_masuk = "SELECT p.waktu_verifikasi, p.jumlah, p.metode, 
               CASE 
                   WHEN p.ref_type = 'TAGIHAN' THEN CONCAT('Tagihan Bulanan')
                   WHEN p.ref_type = 'BOOKING' THEN CONCAT('Booking Fee')
                   ELSE 'Pemasukan Lain'
               END as keterangan,
               u.nama as nama_user
        FROM pembayaran p
        LEFT JOIN booking b ON p.ref_id = b.id_booking AND p.ref_type='BOOKING'
        LEFT JOIN tagihan t ON p.ref_id = t.id_tagihan AND p.ref_type='TAGIHAN'
        LEFT JOIN kontrak k ON t.id_kontrak = k.id_kontrak
        LEFT JOIN penghuni ph ON k.id_penghuni = ph.id_penghuni
        LEFT JOIN pengguna u ON (ph.id_pengguna = u.id_pengguna OR b.id_pengguna = u.id_pengguna)
        WHERE p.status = 'DITERIMA' 
        AND DATE_FORMAT(p.waktu_verifikasi, '%Y-%m') = '$bulan'
        ORDER BY p.waktu_verifikasi ASC";

$res_masuk = $mysqli->query($sql_masuk);

// 2. Query Pengeluaran
$sql_keluar = "SELECT * FROM pengeluaran 
               WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan'
               ORDER BY tanggal ASC";
$res_keluar = $mysqli->query($sql_keluar);
?>

<center>
    <h3>LAPORAN KEUANGAN SIKOS PAADAASIH</h3>
    <h4>Periode: <?= $nama_bulan ?></h4>
</center>

<br>
<h4>A. PEMASUKAN (INCOME)</h4>
<table border="1">
    <thead>
        <tr style="background-color: #dcfce7;">
            <th>No</th>
            <th>Tanggal</th>
            <th>User / Penghuni</th>
            <th>Keterangan</th>
            <th>Metode</th>
            <th>Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total_masuk = 0;
        if($res_masuk->num_rows > 0):
            while($row = $res_masuk->fetch_assoc()): 
                $total_masuk += $row['jumlah'];
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($row['waktu_verifikasi'])) ?></td>
                <td><?= htmlspecialchars($row['nama_user'] ?? 'Umum') ?></td>
                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                <td><?= $row['metode'] ?></td>
                <td align="right"><?= $row['jumlah'] ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" align="center">Tidak ada data pemasukan.</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #dcfce7; font-weight: bold;">
            <td colspan="5" align="right">TOTAL PEMASUKAN</td>
            <td align="right"><?= $total_masuk ?></td>
        </tr>
    </tfoot>
</table>

<br>

<h4>B. PENGELUARAN (EXPENSE)</h4>
<table border="1">
    <thead>
        <tr style="background-color: #fee2e2;">
            <th>No</th>
            <th>Tanggal</th>
            <th>Keperluan</th>
            <th>Deskripsi</th>
            <th>Biaya (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total_keluar = 0;
        if($res_keluar->num_rows > 0):
            while($row = $res_keluar->fetch_assoc()): 
                $total_keluar += $row['biaya'];
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                <td align="right"><?= $row['biaya'] ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="5" align="center">Tidak ada data pengeluaran.</td></tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #fee2e2; font-weight: bold;">
            <td colspan="4" align="right">TOTAL PENGELUARAN</td>
            <td align="right"><?= $total_keluar ?></td>
        </tr>
    </tfoot>
</table>

<br>

<h4>C. RINGKASAN LABA/RUGI</h4>
<table border="1" width="30%">
    <tr>
        <td style="font-weight: bold;">Total Pemasukan</td>
        <td align="right" style="color: green;"><?= $total_masuk ?></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Total Pengeluaran</td>
        <td align="right" style="color: red;">(<?= $total_keluar ?>)</td>
    </tr>
    <tr style="background-color: #fef9c3; font-weight: bold; font-size: 14px;">
        <td>LABA BERSIH</td>
        <td align="right"><?= ($total_masuk - $total_keluar) ?></td>
    </tr>
</table>
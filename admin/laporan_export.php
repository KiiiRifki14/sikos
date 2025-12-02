<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Akses DULUAN sebelum set header
if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak: Anda tidak memiliki izin export laporan.");
}

// Ambil parameter bulan dari URL (default ke bulan ini jika tidak ada)
$bulan = $_GET['bulan'] ?? date('Y-m');
$nama_bulan = date('F Y', strtotime($bulan));

// SET HEADER EXCEL (Hanya jika lolos validasi)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Keuangan_SIKOS_$bulan.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query Data Pemasukan (Hanya yang DITERIMA)
$sql = "SELECT p.waktu_verifikasi, p.jumlah, p.metode, 
               CASE 
                   WHEN p.ref_type = 'TAGIHAN' THEN CONCAT('Tagihan Bulanan - ', u.nama, ' (', k.kode_kamar, ')')
                   WHEN p.ref_type = 'BOOKING' THEN CONCAT('Booking Fee - ', u2.nama, ' (', k2.kode_kamar, ')')
                   ELSE 'Pemasukan Lain'
               END as keterangan
        FROM pembayaran p
        -- Join untuk Tagihan
        LEFT JOIN tagihan t ON p.ref_id = t.id_tagihan AND p.ref_type='TAGIHAN'
        LEFT JOIN kontrak ko ON t.id_kontrak = ko.id_kontrak
        LEFT JOIN penghuni ph ON ko.id_penghuni = ph.id_penghuni
        LEFT JOIN pengguna u ON ph.id_pengguna = u.id_pengguna
        LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
        -- Join untuk Booking
        LEFT JOIN booking b ON p.ref_id = b.id_booking AND p.ref_type='BOOKING'
        LEFT JOIN pengguna u2 ON b.id_pengguna = u2.id_pengguna
        LEFT JOIN kamar k2 ON b.id_kamar = k2.id_kamar
        
        WHERE p.status = 'DITERIMA' 
        AND DATE_FORMAT(p.waktu_verifikasi, '%Y-%m') = '$bulan'
        ORDER BY p.waktu_verifikasi DESC";

$res = $mysqli->query($sql);
?>

<center>
    <h3>LAPORAN KEUANGAN SIKOS PAADAASIH</h3>
    <h4>Periode: <?= $nama_bulan ?></h4>
</center>

<table border="1">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th>No</th>
            <th>Tanggal Terima</th>
            <th>Keterangan Transaksi</th>
            <th>Metode</th>
            <th>Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total = 0;
        if($res->num_rows > 0):
            while($row = $res->fetch_assoc()): 
                $total += $row['jumlah'];
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['waktu_verifikasi'])) ?></td>
                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                <td><?= $row['metode'] ?></td>
                <td align="right"><?= $row['jumlah'] ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="5" align="center">Belum ada data pemasukan bulan ini.</td>
            </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #ffff00; font-weight: bold;">
            <td colspan="4" align="right">TOTAL PEMASUKAN</td>
            <td align="right">Rp <?= $total ?></td>
        </tr>
    </tfoot>
</table>
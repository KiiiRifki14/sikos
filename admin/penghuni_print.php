<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Query Data Penghuni Lengkap
$sql = "SELECT p.id_penghuni, u.nama, u.no_hp, p.alamat, p.pekerjaan, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai
        FROM penghuni p
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
        LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar
        ORDER BY u.nama ASC";
$res = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Penghuni SIKOS</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; font-size: 12px; }
        th { background: #f0f0f0; }
        .header { text-align: center; margin-bottom: 30px; }
        .no-print { margin-bottom: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Data</button>
    </div>

    <div class="header">
        <h2>DATA PENGHUNI SIKOS PAADAASIH</h2>
        <p>Per Tanggal: <?= date('d F Y') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Penghuni</th>
                <th>No. HP</th>
                <th>Kamar</th>
                <th>Masa Sewa</th>
                <th>Pekerjaan</th>
                <th>Alamat Asal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = $res->fetch_assoc()): ?>
            <tr>
                <td align="center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                <td align="center"><?= $row['kode_kamar'] ?? '-' ?></td>
                <td>
                    <?php if($row['tanggal_mulai']): ?>
                        <?= date('d/m/y', strtotime($row['tanggal_mulai'])) ?> - <?= date('d/m/y', strtotime($row['tanggal_selesai'])) ?>
                    <?php else: ?> - <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['pekerjaan']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
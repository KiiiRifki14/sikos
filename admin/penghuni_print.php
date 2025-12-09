<?php
// [OOP: Session]
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security Checker] Hanya Admin
if (!is_admin() && !is_owner()) {
    pesan_error("../login.php", "Akses Ditolak.");
}

// ==========================================================================
// DATA RETRIEVAL (FULL DATA PENGHUNI)
// ==========================================================================
// [SQL Query logic]
// - Mengambil data penghuni, user, kamar, dan kontrak aktif
// - LEFT JOIN pada kontrak & kamar untuk mengantisipasi jika ada penghuni yang belum/sudah tidak punya kamar (history)
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
    <!-- [CSS Print-Friendly] -->
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        /* Tabel border collapse agar garis tabel menyatu rapi */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
        }

        th {
            background: #f0f0f0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Kelas .no-print akan disembunyikan saat command Print dijalankan oleh browser */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-print {
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Tombol Kontrol (Tidak akan tercetak) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Data</button>
        <button class="btn-print" style="background: #64748b; margin-left: 10px;" onclick="window.close()">Tutup</button>
    </div>

    <!-- Header Laporan -->
    <div class="header">
        <h2>DATA PENGHUNI SIKOS PAADAASIH</h2>
        <p>Per Tanggal: <?= date('d F Y') ?></p>
    </div>

    <!-- Tabel Data -->
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
            <?php
            $no = 1;
            while ($row = $res->fetch_assoc()):
            ?>
                <tr>
                    <td align="center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['no_hp']) ?></td>
                    <td align="center"><b><?= $row['kode_kamar'] ?? '-' ?></b></td>
                    <td>
                        <?php if ($row['tanggal_mulai']): ?>
                            <?= date('d/m/y', strtotime($row['tanggal_mulai'])) ?> s/d <?= date('d/m/y', strtotime($row['tanggal_selesai'])) ?>
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
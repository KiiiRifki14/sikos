<?php
// [OOP: Session]
session_start();
// [OOP: Dependencies]
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security] Hanya Admin
if (!is_admin()) {
    die('Forbidden');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Master Fasilitas - SIKOS Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
</head>

<body class="dashboard-body">

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content animate-fade-up">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="font-bold text-xl">Master Fasilitas</h1>
            <a href="fasilitas_form.php" class="btn btn-primary text-xs">
                <i class="fa-solid fa-plus"></i> Tambah Baru
            </a>
        </div>

        <!-- Tabel Data Fasilitas -->
        <div class="card-white">
            <div style="overflow-x: auto;">
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center">ICON</th>
                            <th>NAMA FASILITAS</th>
                            <th>KODE ICON (FONTAWESOME)</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // [OOP: Method Call] Mengambil semua data master fasilitas menggunakan Class Database
                        // Abstraksi logic DB agar view lebih bersih
                        $q = $db->get_all_fasilitas_master();

                        if ($q && $q->num_rows > 0) {
                            while ($row = $q->fetch_assoc()) {
                        ?>
                                <tr>
                                    <!-- Menampilkan visual icon -->
                                    <td class="text-center" style="font-size: 24px; color: var(--primary);">
                                        <i class="fa-solid <?= htmlspecialchars($row['icon']) ?>"></i>
                                    </td>
                                    <td class="font-bold">
                                        <?= htmlspecialchars($row['nama_fasilitas']) ?>
                                    </td>
                                    <!-- Menampilkan kode teknis icon untuk referensi -->
                                    <td style="font-family: monospace; color: var(--text-muted);">
                                        <?= htmlspecialchars($row['icon']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="fasilitas_form.php?id=<?= $row['id_fasilitas'] ?>"
                                                class="btn btn-secondary text-xs" style="padding: 6px 10px;">Edit</a>

                                            <!-- Alert Konfirmasi Hapus yang informatif -->
                                            <a href="fasilitas_proses.php?act=hapus&id=<?= $row['id_fasilitas'] ?>"
                                                class="btn btn-danger text-xs"
                                                style="padding: 6px 10px;"
                                                onclick="konfirmasiAksi(event, 'Yakin hapus fasilitas ini? Semua kamar yang punya fasilitas ini akan kehilangan data fasilitas ini.', this.href)">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center p-8 text-muted'>Belum ada data fasilitas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>
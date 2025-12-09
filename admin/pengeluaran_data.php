<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

// Pagination Logic
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;


$total_data = $db->fetch_single_value("SELECT COUNT(*) FROM pengeluaran");
$total_halaman = ceil($total_data / $batas);

// Query Data
$q = "SELECT * FROM pengeluaran ORDER BY tanggal DESC LIMIT $halaman_awal, $batas";
$res = $mysqli->query($q);
$nomor = $halaman_awal + 1;

// Hitung Total Pengeluaran Bulan Ini (Statistik)
$bulan_ini = date('Y-m');
$total_keluar = $db->fetch_single_value("SELECT SUM(biaya) FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Data Pengeluaran - SIKOS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
    <style>
        /* Modal Style (Native) */
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
    </style>
</head>

<body class="dashboard-body">

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
            <div>
                <h1 class="font-bold text-xl">Pengeluaran Operasional</h1>
                <p class="text-xs text-muted">Catat biaya listrik, air, perbaikan, dll.</p>
            </div>
            <button onclick="openModal()" class="btn btn-primary text-xs">
                <i class="fa-solid fa-plus"></i> Catat Pengeluaran
            </button>
        </div>

        <div class="grid-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
            <div class="card-white flex items-center gap-4" style="border-left: 4px solid var(--danger);">
                <div class="text-3xl text-red-500">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
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
                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()):
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

            <div class="pagination-container">
                <a href="<?= ($halaman > 1) ? "?halaman=" . ($halaman - 1) : '#' ?>"
                    class="pagination-btn <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                    <i class="fa-solid fa-chevron-left"></i> Prev
                </a>

                <?php for ($x = 1; $x <= $total_halaman; $x++): ?>
                    <a href="?halaman=<?= $x ?>"
                        class="pagination-btn <?= ($halaman == $x) ? 'active' : '' ?>">
                        <?= $x ?>
                    </a>
                <?php endfor; ?>

                <a href="<?= ($halaman < $total_halaman) ? "?halaman=" . ($halaman + 1) : '#' ?>"
                    class="pagination-btn <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
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

                    <div class="form-group">
                        <label class="form-label">Judul Keperluan</label>
                        <input type="text" name="judul" class="form-input" placeholder="Contoh: Bayar Listrik, Beli Sapu..." required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-input" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Biaya (Rp)</label>
                            <input type="number" name="biaya" class="form-input" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Detail (Opsional)</label>
                        <textarea name="deskripsi" class="form-input" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Simpan Data</button>
                </form>
            </div>
        </div>

    </main>

    <script>
        function openModal() {
            document.getElementById('modalAdd').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modalAdd').style.display = 'none';
        }
        window.onclick = function(e) {
            if (e.target == document.getElementById('modalAdd')) closeModal();
        }
    </script>
</body>

</html>
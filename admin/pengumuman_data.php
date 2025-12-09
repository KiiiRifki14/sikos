<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin
if (!is_admin()) {
    die('Forbidden');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Kelola Pengumuman - SIKOS Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            display: none;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal-overlay.open {
            display: flex;
            opacity: 1;
        }

        .modal-box {
            background: white;
            padding: 24px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            transform: scale(0.95);
            transition: transform 0.3s;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-overlay.open .modal-box {
            transform: scale(1);
        }
    </style>
</head>

<body class="dashboard-body">

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content animate-fade-up">
        <div class="flex justify-between items-center mb-8">
            <h1 class="font-bold text-xl">Kelola Pengumuman</h1>
            <!-- Tombol Tambah Trigger Modal -->
            <button onclick="openModalTambah()" class="btn btn-primary text-xs">
                <i class="fa-solid fa-plus"></i> Tambah Pengumuman
            </button>
        </div>

        <div class="card-white">
            <div style="overflow-x: auto;">
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Isi Ringkas</th>
                            <th>Periode Tayang</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $mysqli->query("SELECT * FROM pengumuman ORDER BY aktif_mulai DESC");
                        $no = 1;

                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                // Logic Badge Status
                                $today = date('Y-m-d');
                                if (!$row['is_aktif']) {
                                    $statusBadge = '<span style="background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">NON-AKTIF</span>';
                                } elseif ($today > $row['aktif_selesai']) {
                                    $statusBadge = '<span style="background:#f3f4f6; color:#374151; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">EXPIRED</span>';
                                } elseif ($today < $row['aktif_mulai']) {
                                    $statusBadge = '<span style="background:#fef3c7; color:#b45309; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">JADWAL</span>';
                                } else {
                                    $statusBadge = '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">AKTIF</span>';
                                }

                                // Prepare data for JS
                                $jsonRow = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                        ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="font-bold"><?= htmlspecialchars($row['judul']) ?></div>
                                    </td>
                                    <td class="text-sm text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars(substr($row['isi'], 0, 60)) ?>...
                                    </td>
                                    <td class="text-sm">
                                        <?= date('d/m/y', strtotime($row['aktif_mulai'])) ?> s/d <?= date('d/m/y', strtotime($row['aktif_selesai'])) ?>
                                    </td>
                                    <td>
                                        <span style="font-size:12px; font-weight:600; color:var(--primary);">
                                            <?= $row['audiens'] == 'ALL' ? 'Semua' : 'Penghuni' ?>
                                        </span>
                                    </td>
                                    <td><?= $statusBadge ?></td>
                                    <td>
                                        <div class="flex gap-2">
                                            <!-- Tombol Edit Trigger Modal -->
                                            <button onclick="openModalEdit(<?= $jsonRow ?>)" class="btn btn-secondary text-xs" style="padding: 6px 10px;">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="pengumuman_proses.php?act=hapus&id=<?= $row['id_pengumuman'] ?>" class="btn btn-danger text-xs" style="padding: 6px 10px;" onclick="konfirmasiAksi(event, 'Yakin hapus pengumuman ini?', this.href)">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center p-8 text-muted">Belum ada data pengumuman.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL FORM -->
    <div id="modalForm" class="modal-overlay">
        <div class="modal-box">
            <div class="flex justify-between items-center mb-4">
                <h2 id="modalTitle" class="font-bold text-lg">Tambah Pengumuman</h2>
                <button onclick="closeModal()" class="text-muted hover:text-danger"><i class="fa-solid fa-xmark fa-xl"></i></button>
            </div>

            <form id="formPengumuman" method="post" action="pengumuman_proses.php?act=simpan">
                <input type="hidden" name="id_pengumuman" id="inputId">

                <div class="mb-4">
                    <label class="form-label">Judul</label>
                    <input name="judul" id="inputJudul" class="form-input w-full" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Isi Pengumuman</label>
                    <textarea name="isi" id="inputIsi" class="form-input w-full" rows="4" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Target Audiens</label>
                    <select name="audiens" id="inputAudiens" class="form-input w-full">
                        <option value="ALL">Semua</option>
                        <option value="PENGHUNI">Penghuni</option>
                    </select>
                </div>

                <div class="flex gap-4 mb-4">
                    <div class="w-full">
                        <label class="form-label">Mulai Tayang</label>
                        <input type="date" name="aktif_mulai" id="inputMulai" class="form-input w-full" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="w-full">
                        <label class="form-label">Selesai Tayang</label>
                        <input type="date" name="aktif_selesai" id="inputSelesai" class="form-input w-full" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_aktif" id="inputAktif" value="1" checked>
                        <label for="inputAktif" class="cursor-pointer select-none">Aktifkan Sekarang?</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalForm');
        const form = document.getElementById('formPengumuman');
        const title = document.getElementById('modalTitle');

        // Elements
        const elId = document.getElementById('inputId');
        const elJudul = document.getElementById('inputJudul');
        const elIsi = document.getElementById('inputIsi');
        const elAudiens = document.getElementById('inputAudiens');
        const elMulai = document.getElementById('inputMulai');
        const elSelesai = document.getElementById('inputSelesai');
        const elAktif = document.getElementById('inputAktif');

        function openModalTambah() {
            // Reset Form
            form.reset();
            form.action = 'pengumuman_proses.php?act=simpan';
            title.innerText = 'Tambah Pengumuman';
            elId.value = '';

            // Default Date (Optional reset)
            elMulai.value = '<?= date("Y-m-d") ?>';
            // elSelesai handled by reset() usually via HTML value, but explicit set is safer

            modal.classList.add('open');
        }

        function openModalEdit(data) {
            // Populate Form
            form.action = 'pengumuman_proses.php?act=update';
            title.innerText = 'Edit Pengumuman';

            elId.value = data.id_pengumuman;
            elJudul.value = data.judul;
            elIsi.value = data.isi;
            elAudiens.value = data.audiens;
            elMulai.value = data.aktif_mulai;
            elSelesai.value = data.aktif_selesai;
            elAktif.checked = (data.is_aktif == 1);

            modal.classList.add('open');
        }

        function closeModal() {
            modal.classList.remove('open');
        }

        // Close on click outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    </script>

</body>

</html>
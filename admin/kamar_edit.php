<?php
// [OOP: Session]
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security] Cek Admin
if (!is_admin()) {
    die('Forbidden');
}

// [Validasi Input] Pastikan ada ID di URL dan valid angka
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: kamar_data.php');
    exit;
}

// [Database: Prepared Statement] Mengambil data kamar saat ini untuk ditampilkan di form
$stmt = $mysqli->prepare("SELECT * FROM kamar WHERE id_kamar=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// Jika ID tidak ditemukan di database
if (!$row) die('Data tidak ditemukan');

// [Logic] Loading data relasi (Dependensi)
// 1. Ambil fasilitas yang SUDAH dimiliki kamar ini (untuk status checked)
$current_fasilitas = [];
$q_cek_fas = $mysqli->query("SELECT id_fasilitas FROM kamar_fasilitas WHERE id_kamar = $id");
while ($cf = $q_cek_fas->fetch_assoc()) {
    $current_fasilitas[] = $cf['id_fasilitas'];
}

// 2. Ambil foto galeri tambahan
$res_foto = $mysqli->query("SELECT * FROM kamar_foto WHERE id_kamar=$id");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Edit Kamar</title>
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="../assets/js/main.js"></script>

    <style>
        /* Sama seperti kamar_tambah.php tapi dengan penyesuaian untuk galeri */
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }

        .layout-split {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
        }

        /* Galeri Mini untuk menghapus foto */
        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }

        .galeri-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .galeri-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Tombol hapus foto kecil di pojok */
        .del-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 20px;
            height: 20px;
            background: red;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .layout-split {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="dashboard-body">

    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">

        <div class="flex justify-between items-center mb-6" style="max-width: 900px; margin: 0 auto 20px;">
            <h1 class="font-bold text-xl text-slate-800">Edit Kamar: <span class="text-blue-600"><?= htmlspecialchars($row['kode_kamar']) ?></span></h1>
            <a href="kamar_data.php" class="btn btn-secondary text-xs" style="padding: 8px 16px;">Kembali</a>
        </div>

        <div class="form-container">
            <!-- Form mengirim ke kamar_proses.php?act=edit -->
            <form method="post" action="kamar_proses.php?act=edit" enctype="multipart/form-data">
                <!-- Hidden ID: Penting agar sistem tahu data mana yang diedit -->
                <input type="hidden" name="id_kamar" value="<?= $row['id_kamar'] ?>">

                <div class="layout-split">
                    <!-- KOLOM KIRI: Data Text -->
                    <div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Kode Kamar</label>
                                <input type="text" name="kode_kamar" value="<?= htmlspecialchars($row['kode_kamar']) ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Tipe</label>
                                <select name="id_tipe" class="form-input" required>
                                    <?php
                                    $r = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar");
                                    while ($t = $r->fetch_assoc()) {
                                        // Logic: Auto-select opsi yang sesuai database
                                        $sel = ($row['id_tipe'] == $t['id_tipe']) ? 'selected' : '';
                                        echo "<option value='{$t['id_tipe']}' $sel>{$t['nama_tipe']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Harga Sewa</label>
                            <div style="position: relative;">
                                <span style="position:absolute; left:10px; top:10px; color:#888;">Rp</span>
                                <input name="harga" type="number" value="<?= $row['harga'] ?>" class="form-input" style="padding-left: 35px; font-weight: bold;" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Lantai</label>
                                <input name="lantai" type="number" value="<?= $row['lantai'] ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Luas (m²)</label>
                                <input name="luas_m2" type="number" value="<?= $row['luas_m2'] ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" rows="3" class="form-input"><?= htmlspecialchars($row['catatan']) ?></textarea>
                        </div>

                        <!-- LOGIKA CHECKBOX FASILITAS -->
                        <div class="form-group" style="margin-top: 20px;">
                            <label>Fasilitas</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <?php
                                $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                                while ($f = $q_fas->fetch_assoc()) {
                                    // Cek apakah ID fasilitas ini ada di array $current_fasilitas milik kamar
                                    $checked = in_array($f['id_fasilitas'], $current_fasilitas) ? 'checked' : '';
                                    echo "<label style='display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;'>
                                        <input type='checkbox' name='fasilitas[]' value='{$f['id_fasilitas']}' $checked> 
                                        <i class='fa-solid {$f['icon']} text-slate-400'></i> {$f['nama_fasilitas']}
                                      </label>";
                                } ?>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: Manajemen Foto -->
                    <div style="border-left: 1px solid #eee; padding-left: 20px;">
                        <div class="form-group">
                            <label>Foto Utama</label>
                            <!-- Tampilkan foto lama jika ada -->
                            <?php if ($row['foto_cover']): ?>
                                <img src="../assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" style="width:100%; height:160px; object-fit:cover; border-radius:6px; margin-bottom:10px; border:1px solid #ddd;">
                            <?php endif; ?>
                            <input type="file" name="foto_cover" class="form-input" style="font-size:12px;">
                            <small style="color:#888;">Upload baru untuk mengganti.</small>
                        </div>

                        <div class="form-group">
                            <label>Galeri Tambahan</label>
                            <div class="galeri-grid">
                                <!-- Loop foto galeri existing -->
                                <?php while ($f = $res_foto->fetch_assoc()) { ?>
                                    <div class="galeri-item">
                                        <img src="../assets/uploads/kamar/<?= htmlspecialchars($f['file_nama']) ?>">
                                        <!-- Link hapus foto spesifik -->
                                        <a href="kamar_proses.php?act=hapus_foto&id_foto=<?= $f['id_foto'] ?>&id_kamar=<?= $id ?>" class="del-btn" onclick="return confirm('Hapus?')">×</a>
                                    </div>
                                <?php } ?>
                            </div>
                            <input type="file" name="foto_galeri[]" multiple class="form-input" style="font-size:12px;">
                        </div>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>
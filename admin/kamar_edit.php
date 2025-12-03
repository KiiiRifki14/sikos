<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Cek Admin
if (!is_admin()) { die('Forbidden'); }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: kamar_data.php'); exit; }

// 1. Ambil Data Kamar Utama
$stmt = $mysqli->prepare("SELECT * FROM kamar WHERE id_kamar=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) die('Data tidak ditemukan');

// 2. Ambil Fasilitas yang SUDAH dimiliki kamar ini (untuk checklist otomatis)
$current_fasilitas = [];
$q_cek_fas = $mysqli->query("SELECT id_fasilitas FROM kamar_fasilitas WHERE id_kamar = $id");
while($cf = $q_cek_fas->fetch_assoc()){
    $current_fasilitas[] = $cf['id_fasilitas'];
}

// 3. Ambil Data Galeri Foto
$res_foto = $mysqli->query("SELECT * FROM kamar_foto WHERE id_kamar=$id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Kamar [<?= htmlspecialchars($row['kode_kamar']) ?>]</title>

    <link rel="stylesheet" href="../assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styling khusus untuk Galeri Item */
        .galeri-item { position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        .galeri-item img { width: 100%; height: 100px; object-fit: cover; }
        .btn-hapus-foto {
            position: absolute; top: 4px; right: 4px; 
            background: rgba(220, 38, 38, 0.9); color: white;
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; text-decoration: none; transition: 0.2s;
        }
        .btn-hapus-foto:hover { background: #b91c1c; transform: scale(1.1); }
    </style>
</head>
<body class="dashboard-body">
  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
     </main>
</body>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Edit Kamar: <?= htmlspecialchars($row['kode_kamar']) ?></h1>
        <a href="kamar_data.php" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card-white">
        <form method="post" action="kamar_proses.php?act=edit" enctype="multipart/form-data">
            <input type="hidden" name="id_kamar" value="<?= $row['id_kamar'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-700 border-b pb-2 mb-4">Informasi Dasar</h3>
                    
                    <div>
                        <label class="form-label">Kode Kamar</label>
                        <input type="text" name="kode_kamar" value="<?= htmlspecialchars($row['kode_kamar']) ?>" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label">Tipe Kamar</label>
                        <select name="id_tipe" class="form-input" required>
                            <?php
                            $r = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar");
                            while($t=$r->fetch_assoc()){
                                $sel = ($row['id_tipe']==$t['id_tipe'])?'selected':'';
                                echo "<option value='{$t['id_tipe']}' $sel>{$t['nama_tipe']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Lantai</label>
                            <input name="lantai" type="number" min="1" value="<?= $row['lantai'] ?>" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Luas (m²)</label>
                            <input name="luas_m2" type="number" step="0.1" value="<?= $row['luas_m2'] ?>" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Harga per Bulan (Rp)</label>
                        <input name="harga" type="number" min="0" step="50000" value="<?= $row['harga'] ?>" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label">Catatan / Deskripsi</label>
                        <textarea name="catatan" rows="4" class="form-input"><?= htmlspecialchars($row['catatan']) ?></textarea>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="font-bold text-slate-700 border-b pb-2 mb-4">Media & Foto</h3>

                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                        <label class="form-label">Foto Cover (Utama)</label>
                        <div class="flex items-start gap-4 mt-2">
                            <?php if($row['foto_cover']): ?>
                                <img src="../assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" class="w-24 h-24 object-cover rounded shadow-sm border">
                            <?php endif; ?>
                            <div class="flex-1">
                                <input type="file" name="foto_cover" accept="image/*" class="form-input text-sm">
                                <p class="text-xs text-slate-400 mt-1">Upload baru untuk mengganti cover saat ini.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label mb-2 block">Galeri Foto Tambahan</label>
                        
                        <?php if($res_foto->num_rows > 0): ?>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
                                <?php while($f = $res_foto->fetch_assoc()) { ?>
                                    <div class="galeri-item group">
                                        <img src="../assets/uploads/kamar/<?= htmlspecialchars($f['file_nama']) ?>">
                                        <a href="kamar_proses.php?act=hapus_foto&id_foto=<?= $f['id_foto'] ?>&id_kamar=<?= $id ?>" 
                                           class="btn-hapus-foto" 
                                           onclick="return confirm('Yakin hapus foto ini?')"
                                           title="Hapus Foto">
                                           <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 italic mb-3">Belum ada foto galeri tambahan.</p>
                        <?php endif; ?>

                        <label class="form-label text-xs">Tambah Foto Galeri (Bisa Pilih Banyak)</label>
                        <input type="file" name="foto_galeri[]" multiple accept="image/*" class="form-input text-sm">
                    </div>
                </div>
            </div>

            <div class="mb-8 pt-6 border-t border-slate-100">
                <h3 class="font-bold text-slate-700 mb-4"><i class="fa-solid fa-list-check text-blue-600 mr-2"></i> Fasilitas Kamar</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php
                    $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                    while($f = $q_fas->fetch_assoc()){
                        // Logika Checklist: Jika ID ada di array $current_fasilitas, maka checked
                        $checked = in_array($f['id_fasilitas'], $current_fasilitas) ? 'checked' : '';
                    ?>
                        <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-blue-50 cursor-pointer transition">
                            <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>" <?= $checked ?> 
                                   class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 accent-blue-600">
                            <span class="text-sm text-slate-700 font-medium">
                                <i class="fa-solid <?= $f['icon'] ?> text-slate-400 mr-1 w-5 text-center"></i> 
                                <?= $f['nama_fasilitas'] ?>
                            </span>
                        </label>
                    <?php } ?>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="btn-primary px-8 py-2.5 text-base shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
  </main>
</body>
</html>
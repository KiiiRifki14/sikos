<?php
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) { header('Location: ../login.php'); exit; }

$db = new Database();
$data_tipe = $db->tampil_tipe_kamar(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Tambah Kamar Baru</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    
    <div class="flex justify-between items-center mb-6 max-w-3xl mx-auto">
        <h1 class="font-bold text-xl text-slate-800">Tambah Kamar</h1>
        <a href="kamar_data.php" class="btn btn-secondary text-xs px-3 py-2">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card-white max-w-3xl mx-auto p-6 shadow-sm border border-slate-200">
        <form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                <div class="md:col-span-8 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Kode Kamar</label>
                            <input name="kode_kamar" class="form-input w-full font-bold text-slate-700" placeholder="A01" required>
                        </div>
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Tipe Kamar</label>
                            <select name="id_tipe" class="form-input w-full" required>
                                <option value="">- Pilih -</option>
                                <?php 
                                if (!empty($data_tipe)) {
                                    foreach($data_tipe as $t) {
                                        echo '<option value="'.$t['id_tipe'].'">'.htmlspecialchars($t['nama_tipe']).'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs uppercase text-slate-500">Harga (Per Bulan)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-500 text-sm font-bold">Rp</span>
                            <input name="harga" type="number" class="form-input w-full pl-10 font-bold text-lg text-blue-600" placeholder="0" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Lantai</label>
                            <input name="lantai" type="number" class="form-input w-full" value="1" min="1" required>
                        </div>
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Luas (m²)</label>
                            <input name="luas_m2" type="number" step="0.1" class="form-input w-full" value="9" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs uppercase text-slate-500">Catatan</label>
                        <textarea name="catatan" class="form-input w-full text-sm" rows="2" placeholder="Keterangan tambahan..."></textarea>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="form-label text-xs uppercase text-slate-500 mb-2 block">Foto Cover</label>
                    <label class="block w-full aspect-square border-2 border-dashed border-slate-300 rounded-lg hover:bg-slate-50 transition cursor-pointer flex flex-col items-center justify-center text-center p-4 relative overflow-hidden group">
                        <input type="file" name="foto_cover" class="hidden" accept="image/*" onchange="previewImage(this)">
                        
                        <div id="upload-placeholder" class="space-y-2">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <span class="text-xs text-slate-500 font-medium">Upload Foto</span>
                        </div>

                        <img id="image-preview" src="#" alt="Preview" class="absolute inset-0 w-full h-full object-cover hidden">
                    </label>
                    <p class="text-[10px] text-slate-400 mt-2 text-center">*Format JPG/PNG, Max 2MB</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <label class="form-label text-xs uppercase text-slate-500 mb-3 block">Fasilitas</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    <?php
                    $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                    if ($q_fas && $q_fas->num_rows > 0) {
                        while($f = $q_fas->fetch_assoc()):
                    ?>
                        <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition select-none">
                            <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>" class="w-3.5 h-3.5 accent-blue-600">
                            <span class="text-xs text-slate-700 font-medium truncate">
                                <i class="fa-solid <?= $f['icon'] ?> text-slate-400 w-4 text-center mr-1"></i> 
                                <?= htmlspecialchars($f['nama_fasilitas']) ?>
                            </span>
                        </label>
                    <?php 
                        endwhile; 
                    }
                    ?>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="history.back()" class="btn btn-secondary text-sm px-6">Batal</button>
                <button type="submit" class="btn btn-primary text-sm px-8 shadow-md">Simpan Data</button>
            </div>
        </form>
    </div>

  </main>

  <script>
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('upload-placeholder');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
  </script>
</body>
</html>
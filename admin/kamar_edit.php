<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) { die('Forbidden'); }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: kamar_data.php'); exit; }

$stmt = $mysqli->prepare("SELECT * FROM kamar WHERE id_kamar=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) die('Data tidak ditemukan');

$current_fasilitas = [];
$q_cek_fas = $mysqli->query("SELECT id_fasilitas FROM kamar_fasilitas WHERE id_kamar = $id");
while($cf = $q_cek_fas->fetch_assoc()){ $current_fasilitas[] = $cf['id_fasilitas']; }

$res_foto = $mysqli->query("SELECT * FROM kamar_foto WHERE id_kamar=$id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Kamar - SIKOS Admin</title>
    <link rel="stylesheet" href="../assets/css/app.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 8px; }
        .foto-item { position: relative; aspect-ratio: 1; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; }
        .foto-item img { width: 100%; height: 100%; object-fit: cover; }
        .btn-del-foto {
            position: absolute; top: 2px; right: 2px; width: 20px; height: 20px;
            background: rgba(239,68,68,0.9); color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 10px;
            transition: 0.2s; text-decoration: none;
        }
        .btn-del-foto:hover { transform: scale(1.1); background: #dc2626; }
    </style>
</head>
<body class="dashboard-body">
  
  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>
  
  <main class="main-content">
    
    <div class="flex justify-between items-center mb-6 max-w-4xl mx-auto">
        <div>
            <h1 class="font-bold text-xl text-slate-800">Edit Kamar <span class="text-blue-600">#<?= htmlspecialchars($row['kode_kamar']) ?></span></h1>
        </div>
        <a href="kamar_data.php" class="btn btn-secondary text-xs px-3 py-2"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
    </div>

    <div class="card-white max-w-4xl mx-auto p-6 shadow-sm border border-slate-200">
        <form method="post" action="kamar_proses.php?act=edit" enctype="multipart/form-data">
            <input type="hidden" name="id_kamar" value="<?= $row['id_kamar'] ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-5">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Kode Kamar</label>
                            <input type="text" name="kode_kamar" value="<?= htmlspecialchars($row['kode_kamar']) ?>" class="form-input w-full font-bold" required>
                        </div>
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Tipe</label>
                            <select name="id_tipe" class="form-input w-full" required>
                                <?php
                                $r = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar");
                                while($t=$r->fetch_assoc()){
                                    $sel = ($row['id_tipe']==$t['id_tipe'])?'selected':'';
                                    echo "<option value='{$t['id_tipe']}' $sel>{$t['nama_tipe']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs uppercase text-slate-500">Harga Sewa</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-500 text-sm font-bold">Rp</span>
                            <input name="harga" type="number" value="<?= $row['harga'] ?>" class="form-input w-full pl-10 font-bold text-lg text-blue-600" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Lantai</label>
                            <input name="lantai" type="number" value="<?= $row['lantai'] ?>" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="form-label text-xs uppercase text-slate-500">Luas (m²)</label>
                            <input name="luas_m2" type="number" value="<?= $row['luas_m2'] ?>" class="form-input w-full" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-xs uppercase text-slate-500">Catatan</label>
                        <textarea name="catatan" rows="2" class="form-input w-full text-sm"><?= htmlspecialchars($row['catatan']) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <label class="form-label text-xs uppercase text-slate-500 mb-3 block">Fasilitas</label>
                        <div class="grid grid-cols-2 gap-2">
                            <?php
                            $q_fas = $mysqli->query("SELECT * FROM fasilitas_master ORDER BY nama_fasilitas ASC");
                            while($f = $q_fas->fetch_assoc()){
                                $checked = in_array($f['id_fasilitas'], $current_fasilitas) ? 'checked' : '';
                            ?>
                                <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded hover:bg-blue-50 cursor-pointer transition select-none">
                                    <input type="checkbox" name="fasilitas[]" value="<?= $f['id_fasilitas'] ?>" <?= $checked ?> class="w-3.5 h-3.5 accent-blue-600">
                                    <span class="text-xs text-slate-700 font-medium">
                                        <i class="fa-solid <?= $f['icon'] ?> text-slate-400 w-4 mr-1"></i> 
                                        <?= $f['nama_fasilitas'] ?>
                                    </span>
                                </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="border-l pl-0 lg:pl-8 border-slate-100 space-y-6">
                    
                    <div>
                        <label class="form-label text-xs uppercase text-slate-500 mb-2 block">Foto Utama</label>
                        <div class="relative group rounded-lg overflow-hidden border border-slate-200 aspect-video mb-2 bg-slate-50 flex items-center justify-center">
                            <?php if($row['foto_cover']): ?>
                                <img id="cover-preview" src="../assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img id="cover-preview" class="hidden w-full h-full object-cover">
                                <span id="cover-placeholder" class="text-slate-400 text-xs">Belum ada foto</span>
                            <?php endif; ?>
                            
                            <label class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer text-white text-xs font-bold">
                                <input type="file" name="foto_cover" accept="image/*" class="hidden" onchange="previewCover(this)">
                                <i class="fa-solid fa-pen mr-1"></i> Ganti
                            </label>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="form-label text-xs uppercase text-slate-500 block">Galeri</label>
                            <label class="text-[10px] text-blue-600 cursor-pointer hover:underline font-bold">
                                + Tambah
                                <input type="file" name="foto_galeri[]" multiple accept="image/*" class="hidden" onchange="alert(this.files.length + ' foto dipilih')">
                            </label>
                        </div>
                        
                        <div class="foto-grid">
                            <?php if($res_foto->num_rows > 0): ?>
                                <?php while($f = $res_foto->fetch_assoc()) { ?>
                                    <div class="foto-item group">
                                        <img src="../assets/uploads/kamar/<?= htmlspecialchars($f['file_nama']) ?>">
                                        <a href="kamar_proses.php?act=hapus_foto&id_foto=<?= $f['id_foto'] ?>&id_kamar=<?= $id ?>" 
                                           class="btn-del-foto" onclick="return confirm('Hapus foto ini?')">
                                           <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    </div>
                                <?php } ?>
                            <?php else: ?>
                                <div class="col-span-3 text-center py-4 text-xs text-slate-400 bg-slate-50 rounded border border-dashed border-slate-200">
                                    Galeri kosong
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                <button type="submit" class="btn btn-primary px-8 py-2 text-sm shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>

  </main>

  <script>
      function previewCover(input) {
          if (input.files && input.files[0]) {
              var reader = new FileReader();
              reader.onload = function(e) {
                  document.getElementById('cover-preview').src = e.target.result;
                  document.getElementById('cover-preview').classList.remove('hidden');
                  var ph = document.getElementById('cover-placeholder');
                  if(ph) ph.classList.add('hidden');
              }
              reader.readAsDataURL(input.files[0]);
          }
      }
  </script>
</body>
</html>
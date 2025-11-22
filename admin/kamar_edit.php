<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin()) { die('Forbidden'); }

$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM kamar WHERE id_kamar=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) die('Data tidak ditemukan');

// Ambil data foto galeri
$res_foto = $mysqli->query("SELECT * FROM kamar_foto WHERE id_kamar=$id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Kamar [<?= htmlspecialchars($row['kode_kamar']) ?>]</title>
    <link rel="stylesheet" href="../assets/css/app.css"/>
    <style>
        .galeri-container { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
        .galeri-item { position: relative; width: 100px; height: 100px; border: 1px solid #ddd; }
        .galeri-item img { width: 100%; height: 100%; object-fit: cover; }
        .btn-hapus-foto {
            position: absolute; top: 0; right: 0; background: red; color: white;
            font-size: 12px; padding: 2px 5px; text-decoration: none; cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h3>Edit Kamar [<?= htmlspecialchars($row['kode_kamar']) ?>]</h3>
    
    <form method="post" action="kamar_proses.php?act=edit" enctype="multipart/form-data">
      <input type="hidden" name="id_kamar" value="<?= $row['id_kamar'] ?>">
      
      Kode Kamar: <input name="kode_kamar" value="<?= htmlspecialchars($row['kode_kamar']) ?>" required><br>
      
      Tipe Kamar: <select name="id_tipe">
        <?php
        $r = $mysqli->query("SELECT id_tipe,nama_tipe FROM tipe_kamar");
        while($t=$r->fetch_assoc()){
          $sel = ($row['id_tipe']==$t['id_tipe'])?'selected':'';
          echo "<option value='{$t['id_tipe']}' $sel>{$t['nama_tipe']}</option>";
        }
        ?>
      </select><br>
      
      Lantai: <input name="lantai" type="number" min="1" value="<?= $row['lantai'] ?>"><br>
      Luas: <input name="luas_m2" type="number" min="1" value="<?= $row['luas_m2'] ?>"> m2<br>
      Harga: <input name="harga" type="number" min="0" step="50000" value="<?= $row['harga'] ?>"><br>
      
      Foto Cover (Utama): <input type="file" name="foto_cover" accept="image/jpeg,image/png,image/webp"><br>
      <?php if($row['foto_cover']){ ?>
        <small><img src="../assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" width="120" style="margin-bottom:10px;"></small><br>
      <?php } ?>

      <b>Galeri Foto Tambahan:</b><br>
      <div class="galeri-container">
          <?php while($f = $res_foto->fetch_assoc()) { ?>
            <div class="galeri-item">
                <img src="../assets/uploads/kamar/<?= htmlspecialchars($f['file_nama']) ?>">
                <a href="kamar_proses.php?act=hapus_foto&id_foto=<?= $f['id_foto'] ?>&id_kamar=<?= $id ?>" 
                   class="btn-hapus-foto" onclick="return confirm('Hapus foto ini?')">X</a>
            </div>
          <?php } ?>
      </div>
      Tambah Foto Galeri (Bisa pilih banyak): 
      <input type="file" name="foto_galeri[]" multiple accept="image/jpeg,image/png,image/webp"><br><br>

      Catatan: <textarea name="catatan"><?= htmlspecialchars($row['catatan']) ?></textarea><br>
      
      <button type="submit">Update Data</button>
    </form>
    
    <br>
    <a href="kamar_data.php">Kembali</a>
</div>
</body>
</html>
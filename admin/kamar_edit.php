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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Kamar [<?= htmlspecialchars($row['kode_kamar']) ?>]</title>
    <link rel="stylesheet" href="../assets/css/app.css"/>
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
      Foto Kamar: <input type="file" name="foto_cover" accept="image/jpeg,image/png,image/webp"><br>
      <?php if($row['foto_cover']){ ?>
        <small><img src="../assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" width="120"></small><br>
      <?php } ?>
      Catatan: <textarea name="catatan"><?= htmlspecialchars($row['catatan']) ?></textarea><br>
      <button type="submit">Update</button>
    </form>
    <br>
    <a href="kamar_data.php">Kembali</a>
</div>
</body>
</html>
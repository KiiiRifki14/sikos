<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) { die('Forbidden'); }
?>
<h3>Tambah Kamar</h3>
<form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
Kode Kamar: <input name="kode_kamar" required><br>
Tipe Kamar: <select name="id_tipe">
  <?php $r = $mysqli->query("SELECT id_tipe,nama_tipe FROM tipe_kamar"); while($t=$r->fetch_assoc()){ echo "<option value='{$t['id_tipe']}'>{$t['nama_tipe']}</option>"; } ?>
</select><br>
Lantai: <input name="lantai" type="number" min="1"><br>
Luas: <input name="luas_m2" type="number" min="1"> m2<br>
Foto Kamar: <input type="file" name="foto_cover" accept="image/jpeg,image/png,image/webp"><br>
Harga: <input name="harga" type="number" min="0" step="50000"><br>
Catatan: <textarea name="catatan"></textarea><br>
<button type="submit">Simpan</button>
</form>
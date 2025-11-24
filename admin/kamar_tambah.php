<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) { die('Forbidden'); }

// Gunakan Class untuk ambil tipe kamar
$db = new Database();
$data_tipe = $db->tampil_tipe_kamar();
?>
<h3>Tambah Kamar</h3>
<form method="post" action="kamar_proses.php?act=tambah" enctype="multipart/form-data">
    Kode Kamar: <input name="kode_kamar" required><br>
    
    Tipe Kamar: 
    <select name="id_tipe">
        <?php foreach($data_tipe as $t) { ?>
            <option value="<?= $t['id_tipe'] ?>"><?= $t['nama_tipe'] ?></option>
        <?php } ?>
    </select><br>

    Lantai: <input name="lantai" type="number" min="1"><br>
    Luas: <input name="luas_m2" type="number" min="1"> m2<br>
    Harga: <input name="harga" type="number" min="0" step="50000"><br>
    Foto: <input type="file" name="foto_cover"><br>
    Catatan: <textarea name="catatan"></textarea><br>
    <button type="submit">Simpan</button>
</form>
<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) { die('Forbidden'); }
$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM penghuni WHERE id_penghuni=?");
$stmt->bind_param('i',$id);
$stmt->execute(); $row=$stmt->get_result()->fetch_assoc();
?>
<h3>Edit Penghuni [ID: <?= $id ?>]</h3>
<form method="post" action="penghuni_proses.php?act=edit">
  <input type="hidden" name="id" value="<?= $id ?>">
  Alamat: <input name="alamat" value="<?= htmlspecialchars($row['alamat']); ?>"><br>
  Pekerjaan: <input name="pekerjaan" value="<?= htmlspecialchars($row['pekerjaan']); ?>"><br>
  Emergency CP: <input name="emergency_cp" value="<?= htmlspecialchars($row['emergency_cp']); ?>"><br>
  <button type="submit">Simpan</button>
</form>
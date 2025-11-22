<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) die('Forbidden');

if ($_GET['act']=='tambah') {
  // Form tambah
  ?>
<h2>Tambah Pengumuman</h2>
<form method="post" action="pengumuman_proses.php?act=simpan">
  Judul: <input name="judul"><br>
  Isi: <textarea name="isi"></textarea><br>
  Audiens: <select name="audiens"><option value="ALL">Semua</option><option value="PENGHUNI">Penghuni</option></select><br>
  Aktif Mulai: <input type="date" name="aktif_mulai"><br>
  Aktif Selesai: <input type="date" name="aktif_selesai"><br>
  Aktif ? <input type="checkbox" name="is_aktif" value="1"><br>
  <button type="submit">Simpan</button>
</form>
  <?php
  exit;
}
if ($_GET['act']=='simpan' && $_SERVER['REQUEST_METHOD']=='POST') {
  $stmt = $mysqli->prepare("INSERT INTO pengumuman (judul,isi,audiens,aktif_mulai,aktif_selesai,is_aktif,created_by) VALUES (?,?,?,?,?,?,?)");
  $aktif = isset($_POST['is_aktif'])?1:0;
  $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_SESSION['id_pengguna']);
  $stmt->execute();
  header('Location: pengumuman_data.php');
  exit;
}

if ($_GET['act']=='edit' && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $row = $mysqli->query("SELECT * FROM pengumuman WHERE id_pengumuman=$id")->fetch_assoc();
  ?>
<h2>Edit Pengumuman</h2>
<form method="post" action="pengumuman_proses.php?act=update">
  <input type="hidden" name="id_pengumuman" value="<?= $row['id_pengumuman'] ?>">
  Judul: <input name="judul" value="<?= htmlspecialchars($row['judul']) ?>"><br>
  Isi: <textarea name="isi"><?= htmlspecialchars($row['isi']) ?></textarea><br>
  Audiens: <select name="audiens">
    <option value="ALL" <?= $row['audiens']=='ALL'?'selected':'' ?>>Semua</option>
    <option value="PENGHUNI" <?= $row['audiens']=='PENGHUNI'?'selected':'' ?>>Penghuni</option>
  </select><br>
  Aktif Mulai: <input type="date" name="aktif_mulai" value="<?= $row['aktif_mulai'] ?>"><br>
  Aktif Selesai: <input type="date" name="aktif_selesai" value="<?= $row['aktif_selesai'] ?>"><br>
  Aktif ? <input type="checkbox" name="is_aktif" value="1" <?= $row['is_aktif']?'checked':'' ?>><br>
  <button type="submit">Update</button>
</form>
  <?php
  exit;
}
if ($_GET['act']=='update' && $_SERVER['REQUEST_METHOD']=='POST') {
  $stmt = $mysqli->prepare("UPDATE pengumuman SET judul=?,isi=?,audiens=?,aktif_mulai=?,aktif_selesai=?,is_aktif=? WHERE id_pengumuman=?");
  $aktif = isset($_POST['is_aktif'])?1:0;
  $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_POST['id_pengumuman']);
  $stmt->execute();
  header('Location: pengumuman_data.php');
  exit;
}

if ($_GET['act']=='hapus' && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $mysqli->query("DELETE FROM pengumuman WHERE id_pengumuman=$id");
  header('Location: pengumuman_data.php');
  exit;
}
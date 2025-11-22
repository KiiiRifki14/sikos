<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) die('Forbidden');
if ($_GET['act']=='edit') {
  $stmt = $mysqli->prepare("UPDATE penghuni SET alamat=?, pekerjaan=?, emergency_cp=? WHERE id_penghuni=?");
  $stmt->bind_param('sssi', $_POST['alamat'], $_POST['pekerjaan'], $_POST['emergency_cp'], $_POST['id']);
  $stmt->execute();
  header('Location: penghuni_data.php');
}
?>
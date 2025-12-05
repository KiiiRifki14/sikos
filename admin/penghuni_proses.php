<?php
require '../inc/koneksi.php';
require '../inc/guard.php';
session_start();
if (!is_admin()) die('Forbidden');
if ($_GET['act']=='edit') {
  $db = new Database(); // Init DB
  $id_p = $_POST['id'];
  $stmt = $mysqli->prepare("UPDATE penghuni SET alamat=?, pekerjaan=?, emergency_cp=? WHERE id_penghuni=?");
  $stmt->bind_param('sssi', $_POST['alamat'], $_POST['pekerjaan'], $_POST['emergency_cp'], $_POST['id']);
  $stmt->execute();
  $q_nama = $mysqli->query("SELECT u.nama FROM penghuni p JOIN pengguna u ON p.id_pengguna=u.id_pengguna WHERE p.id_penghuni=$id_p")->fetch_assoc();
  $nama_p = $q_nama['nama'] ?? 'Penghuni';
  
  $db->catat_log($_SESSION['id_pengguna'], 'EDIT PENGHUNI', "Mengubah data profil penghuni: $nama_p (ID: $id_p)");
  header('Location: penghuni_data.php');
}
?>
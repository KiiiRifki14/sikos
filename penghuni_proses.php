<?php
require 'inc/koneksi.php';
session_start();
$id_pengguna = $_SESSION['id_pengguna'] ?? 0;
if(!$id_pengguna) die('Login dulu!');
$stmt = $mysqli->prepare("UPDATE penghuni SET alamat=?, pekerjaan=?, emergency_cp=? WHERE id_pengguna=?");
$stmt->bind_param('sssi', $_POST['alamat'], $_POST['pekerjaan'], $_POST['emergency_cp'], $id_pengguna);
$stmt->execute();
// update nama/no_hp di pengguna
$stmt2 = $mysqli->prepare("UPDATE pengguna SET nama=?, no_hp=? WHERE id_pengguna=?");
$stmt2->bind_param('ssi', $_POST['nama'], $_POST['no_hp'], $id_pengguna);
$stmt2->execute();
header('Location: penghuni_dashboard.php?update=success');
?>
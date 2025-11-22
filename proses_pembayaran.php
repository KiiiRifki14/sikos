<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) die('CSRF!!');
$id_pembayaran = intval($_POST['id_pembayaran']);
$path = upload_process($_FILES['bukti_tf'], 'bukti_tf');

$stmt = $mysqli->prepare("UPDATE pembayaran SET bukti_tf=?, status='PROSES' WHERE id_pembayaran=?");
$stmt->bind_param('si', $path, $id_pembayaran);
$stmt->execute();
header('Location: penghuni_dashboard.php?msg=pembayaran');
?>
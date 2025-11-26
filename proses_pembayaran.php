<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) die('CSRF!!');

$id_pembayaran = intval($_POST['id_pembayaran']);
if ($id_pembayaran < 1) die('ID pembayaran tidak valid.');

// proses upload bukti transfer
$path = upload_process($_FILES['bukti_tf'], 'bukti_tf');

// update ke kolom yang benar di tabel pembayaran
$stmt = $mysqli->prepare("UPDATE pembayaran SET bukti_path = ?, status = 'PENDING' WHERE id_pembayaran = ?");
$stmt->bind_param('si', $path, $id_pembayaran);
$stmt->execute();

header('Location: penghuni_dashboard.php?msg=pembayaran');
exit;
?>
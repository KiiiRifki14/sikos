<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) die('Login dulu!');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) die('CSRF error!');
$id_tagihan = intval($_POST['id_tagihan']);
$metode = $_POST['metode'] ?? 'TRANSFER';
$jumlah = intval($_POST['jumlah']);
$fname = upload_process($_FILES['bukti'], 'bukti');
$stmt = $mysqli->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, bukti_path, status) VALUES ('TAGIHAN',?,?,?,?,'PENDING')");
$stmt->bind_param('iiis', $id_tagihan, $metode, $jumlah, $fname);
$stmt->execute();
header('Location: tagihan_saya.php');
?>
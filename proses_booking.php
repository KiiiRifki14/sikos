<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    die('Login dulu!');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('CSRF error!');
}

// Cek kamar tersedia
$id_kamar = intval($_POST['id_kamar']);
$res = $mysqli->prepare("SELECT status_kamar FROM kamar WHERE id_kamar=? FOR UPDATE");
$res->bind_param('i', $id_kamar);
$res->execute();
$status = $res->get_result()->fetch_assoc();
if (!$status || $status['status_kamar'] != 'TERSEDIA') {
    die('Kamar tidak tersedia untuk booking');
}

$ktp_path = null;
// KTP upload
if (!empty($_FILES['ktp_opt']['name'])) {
    $ktp_path = upload_process($_FILES['ktp_opt'], 'ktp');
}

$stmt = $mysqli->prepare("INSERT INTO booking (id_pengguna, id_kamar, checkin_rencana, durasi_bulan_rencana, status, ktp_path_opt) VALUES (?, ?, ?, ?, 'PENDING', ?)");
$stmt->bind_param('iisis',
    $_SESSION['id_pengguna'],
    $id_kamar,
    $_POST['checkin_rencana'],
    $_POST['durasi_bulan_rencana'],
    $ktp_path);
$stmt->execute();
$id_booking = $stmt->insert_id;

// Instruksi pembayaran booking
$harga = 100000; // ex: booking fee tetap
$stmt2 = $mysqli->prepare("INSERT INTO pembayaran (ref_type, ref_id, metode, jumlah, status) VALUES ('BOOKING', ?, 'TRANSFER', ?, 'PENDING')");
$stmt2->bind_param('ii', $id_booking, $harga);
$stmt2->execute();

header('Location: pembayaran.php?booking=' . $id_booking);
?>
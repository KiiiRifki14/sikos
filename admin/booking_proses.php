<?php
session_start();
require '../inc/koneksi.php';

// Cek Admin
if (!isset($_SESSION['peran']) || ($_SESSION['peran']!='ADMIN' && $_SESSION['peran']!='PEMILIK')) {
    header("Location: ../login.php"); exit;
}

$act = $_GET['act'] ?? '';
$id  = $_GET['id'] ?? 0;

if ($act == 'approve' && $id) {
    // Panggil fungsi sakti yang sama
    // Jadi mau lewat menu Booking atau Pembayaran, kontrak tetap terbuat!
    $sukses = $db->setujui_booking_dan_buat_kontrak($id);

    if ($sukses) {
        echo "<script>alert('Booking Diterima & Kontrak Aktif!'); window.location='booking_data.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses kontrak.'); window.location='booking_data.php';</script>";
    }
}
else if ($act == 'batal' && $id) {
    // Update jadi BATAL
    $mysqli->query("UPDATE booking SET status='BATAL' WHERE id_booking=$id");
    echo "<script>alert('Booking Dibatalkan.'); window.location='booking_data.php';</script>";
}
?>
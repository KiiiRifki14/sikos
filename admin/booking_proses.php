<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) die('Forbidden');

$db = new Database();
$id = intval($_GET['id']);
$act = $_GET['act'];

if ($act == 'approve') {
    // Logika approve (bisa dikembangkan lebih kompleks di dalam Class Database)
    $db->verifikasi_booking($id, 'SELESAI');
    // Tambahan: Update status kamar jadi TERISI (sebaiknya dibuat method juga)
    // $db->update_status_kamar_by_booking($id, 'TERISI'); 
} elseif ($act == 'reject') {
    $db->verifikasi_booking($id, 'BATAL');
}

header('Location: booking_data.php');
?>
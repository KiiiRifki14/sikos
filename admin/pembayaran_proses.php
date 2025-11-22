<?php
require '../inc/koneksi.php';
session_start();
if (!is_admin()) die('Forbidden');
$id_pembayaran = intval($_GET['id_pembayaran']);
$act = $_GET['act'];
if ($act=='terima') {
    $mysqli->begin_transaction();
    $pay = $mysqli->query("SELECT ref_type, ref_id FROM pembayaran WHERE id_pembayaran=$id_pembayaran")->fetch_assoc();
    if ($pay['ref_type']=='TAGIHAN') {
        $mysqli->query("UPDATE pembayaran SET status='DITERIMA', waktu_verifikasi=NOW(), verifikator_id={$_SESSION['id_pengguna']} WHERE id_pembayaran=$id_pembayaran");
        $mysqli->query("UPDATE tagihan SET status='LUNAS' WHERE id_tagihan={$pay['ref_id']}");
    }
    $mysqli->commit();
    header('Location: pembayaran_data.php?success=verif');
}
?>
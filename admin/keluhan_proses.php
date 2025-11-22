<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

$id = intval($_GET['id']);
$act = $_GET['act'];

if ($id > 0) {
    if ($act == 'proses') {
        // Ubah status jadi PROSES
        $stmt = $mysqli->prepare("UPDATE keluhan SET status='PROSES' WHERE id_keluhan=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    } 
    elseif ($act == 'selesai') {
        // Ubah status jadi SELESAI & set tanggal selesai
        $stmt = $mysqli->prepare("UPDATE keluhan SET status='SELESAI', diselesaikan_at=NOW() WHERE id_keluhan=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    elseif ($act == 'hapus') {
        $stmt = $mysqli->prepare("DELETE FROM keluhan WHERE id_keluhan=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

header('Location: keluhan_data.php');
?>
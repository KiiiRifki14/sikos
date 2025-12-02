<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

// 1. LOGIKA HAPUS (GET Request)
if (isset($_GET['act']) && $_GET['act'] == 'hapus') {
    $id = intval($_GET['id']);
    
    // Cek apakah ada foto? Hapus filenya dulu biar bersih
    $q = $mysqli->query("SELECT foto_path FROM keluhan WHERE id_keluhan=$id");
    if($r = $q->fetch_assoc()){
        if($r['foto_path'] && file_exists("../assets/uploads/keluhan/".$r['foto_path'])){
            unlink("../assets/uploads/keluhan/".$r['foto_path']);
        }
    }
    
    $stmt = $mysqli->prepare("DELETE FROM keluhan WHERE id_keluhan=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: keluhan_data.php');
    exit;
}

// 2. LOGIKA UPDATE STATUS & BALAS PESAN (POST Request dari Modal)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id_keluhan']);
    $status_baru = $_POST['status'];
    $pesan_baru = trim($_POST['tanggapan']);
    
    // Ambil history chat lama
    $old_data = $mysqli->query("SELECT tanggapan_admin FROM keluhan WHERE id_keluhan=$id")->fetch_assoc();
    $history_lama = $old_data['tanggapan_admin'];

    // Format Pesan Baru: "[TANGGAL JAM - STATUS] Pesan"
    if (!empty($pesan_baru)) {
        $timestamp = date('d/m/y H:i');
        // Tambahkan pesan baru di ATAS pesan lama (Append)
        $entry_baru = "<b>[$timestamp - $status_baru]</b> $pesan_baru";
        $tanggapan_final = $entry_baru . "<br><hr style='margin:4px 0; border-top:1px dashed #ccc;'>" . $history_lama;
    } else {
        $tanggapan_final = $history_lama; // Gak berubah kalau kosong
    }

    // Update ke Database
    if ($status_baru == 'SELESAI') {
        $stmt = $mysqli->prepare("UPDATE keluhan SET status=?, tanggapan_admin=?, diselesaikan_at=NOW() WHERE id_keluhan=?");
        $stmt->bind_param('ssi', $status_baru, $tanggapan_final, $id);
    } else {
        $stmt = $mysqli->prepare("UPDATE keluhan SET status=?, tanggapan_admin=? WHERE id_keluhan=?");
        $stmt->bind_param('ssi', $status_baru, $tanggapan_final, $id);
    }
    
    $stmt->execute();
    header('Location: keluhan_data.php');
    exit;
}
?>
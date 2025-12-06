<?php
session_start();
require '../inc/utils.php';
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) { die('Forbidden'); }

$db = new Database(); // Init object DB untuk logging
$act = $_GET['act'] ?? '';

// --- TAMBAH FASILITAS ---
if ($act == 'tambah') {
    $nama = bersihkan_input($_POST['nama']);
    $icon = bersihkan_input($_POST['icon']);

    $stmt = $mysqli->prepare("INSERT INTO fasilitas_master (nama_fasilitas, icon) VALUES (?, ?)");
    $stmt->bind_param('ss', $nama, $icon);
    
    if ($stmt->execute()) {
        $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH FASILITAS', "Menambah fasilitas: $nama");
        header('Location: fasilitas_data.php?msg=sukses');
    } else {
        echo "<script>alert('Gagal tambah fasilitas!'); window.history.back();</script>";
    }
}

// --- EDIT FASILITAS ---
elseif ($act == 'edit') {
    $id = intval($_POST['id_fasilitas']);
    $nama = bersihkan_input($_POST['nama']);
    $icon = bersihkan_input($_POST['icon']);

    $stmt = $mysqli->prepare("UPDATE fasilitas_master SET nama_fasilitas=?, icon=? WHERE id_fasilitas=?");
    $stmt->bind_param('ssi', $nama, $icon, $id);
    
    if ($stmt->execute()) {
        $db->catat_log($_SESSION['id_pengguna'], 'EDIT FASILITAS', "Update fasilitas ID $id menjadi: $nama");
        header('Location: fasilitas_data.php?msg=updated');
    } else {
        echo "<script>alert('Gagal update fasilitas!'); window.history.back();</script>";
    }
}

// --- HAPUS FASILITAS ---
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    
    // Ambil info sebelum hapus
    $q = $mysqli->query("SELECT nama_fasilitas FROM fasilitas_master WHERE id_fasilitas=$id");
    $d = $q->fetch_assoc();
    $nama_hapus = $d['nama_fasilitas'] ?? 'Unknown';

    // 1. Hapus dulu relasi di kamar_fasilitas (agar tidak error / data orphan)
    $mysqli->query("DELETE FROM kamar_fasilitas WHERE id_fasilitas=$id");

    // 2. Hapus master fasilitasnya
    $mysqli->query("DELETE FROM fasilitas_master WHERE id_fasilitas=$id");

    $db->catat_log($_SESSION['id_pengguna'], 'HAPUS FASILITAS', "Menghapus fasilitas: $nama_hapus");
    header('Location: fasilitas_data.php?msg=deleted');
}
?>
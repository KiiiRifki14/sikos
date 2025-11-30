<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin()) { die('Forbidden'); }

$act = $_GET['act'] ?? '';

// --- TAMBAH FASILITAS ---
if ($act == 'tambah') {
    $nama = $_POST['nama'];
    $icon = $_POST['icon']; // Contoh: fa-wifi

    $stmt = $mysqli->prepare("INSERT INTO fasilitas_master (nama_fasilitas, icon) VALUES (?, ?)");
    $stmt->bind_param('ss', $nama, $icon);
    
    if ($stmt->execute()) {
        header('Location: fasilitas_data.php?msg=sukses');
    } else {
        echo "<script>alert('Gagal tambah fasilitas!'); window.history.back();</script>";
    }
}

// --- EDIT FASILITAS ---
elseif ($act == 'edit') {
    $id = intval($_POST['id_fasilitas']);
    $nama = $_POST['nama'];
    $icon = $_POST['icon'];

    $stmt = $mysqli->prepare("UPDATE fasilitas_master SET nama_fasilitas=?, icon=? WHERE id_fasilitas=?");
    $stmt->bind_param('ssi', $nama, $icon, $id);
    
    if ($stmt->execute()) {
        header('Location: fasilitas_data.php?msg=updated');
    } else {
        echo "<script>alert('Gagal update fasilitas!'); window.history.back();</script>";
    }
}

// --- HAPUS FASILITAS ---
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);

    // 1. Hapus dulu relasi di kamar_fasilitas (agar tidak error / data orphan)
    $mysqli->query("DELETE FROM kamar_fasilitas WHERE id_fasilitas=$id");

    // 2. Hapus master fasilitasnya
    $mysqli->query("DELETE FROM fasilitas_master WHERE id_fasilitas=$id");

    header('Location: fasilitas_data.php?msg=deleted');
}
?>
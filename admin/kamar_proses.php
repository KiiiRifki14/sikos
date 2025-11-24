<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
require '../inc/upload.php';

if (!is_admin()) { die('Forbidden'); }

// INSTANSIASI OBJEK DATABASE
$db = new Database();

$act = $_GET['act'] ?? '';

// --- PROSES TAMBAH ---
if ($act == 'tambah') {
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // Panggil Method tambah_kamar dari Class Database
    $status = $db->tambah_kamar(
        $_POST['kode_kamar'],
        $_POST['id_tipe'],
        $_POST['lantai'],
        $_POST['luas_m2'],
        $_POST['harga'],
        $foto_cover,
        $_POST['catatan']
    );

    if ($status) {
        header('Location: kamar_data.php');
    } else {
        echo "<script>alert('Gagal tambah data! Kode kamar mungkin duplikat.'); window.history.back();</script>";
    }
}

// --- PROSES EDIT ---
elseif ($act == 'edit') {
    $id_kamar = intval($_POST['id_kamar']);
    $foto_cover = null;
    if (!empty($_FILES['foto_cover']['name'])) {
        $foto_cover = upload_process($_FILES['foto_cover'], 'kamar');
    }

    // Panggil Method edit_kamar dari Class Database
    $status = $db->edit_kamar(
        $id_kamar,
        $_POST['kode_kamar'],
        $_POST['id_tipe'],
        $_POST['lantai'],
        $_POST['luas_m2'],
        $_POST['harga'],
        $foto_cover,
        $_POST['catatan']
    );

    if ($status) {
        header('Location: kamar_data.php');
    } else {
        echo "<script>alert('Gagal edit data! Kode kamar mungkin duplikat.'); window.history.back();</script>";
    }
}

// --- PROSES HAPUS ---
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    
    // Panggil Method hapus_kamar dari Class Database
    $db->hapus_kamar($id);
    
    header('Location: kamar_data.php');
}
?>
<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php'; // Pastikan guard di-require di sini

if (!is_admin()) die('Forbidden');

$db = new Database(); // Init DB

if ($_GET['act'] == 'simpan' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $mysqli->prepare("INSERT INTO pengumuman (judul,isi,audiens,aktif_mulai,aktif_selesai,is_aktif,created_by) VALUES (?,?,?,?,?,?,?)");
    $aktif = isset($_POST['is_aktif']) ? 1 : 0;
    $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_SESSION['id_pengguna']);
    $stmt->execute();

    // LOG
    $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH PENGUMUMAN', "Menambah pengumuman: " . $_POST['judul']);

    header('Location: pengumuman_data.php');
    exit;
}

if ($_GET['act'] == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $mysqli->prepare("UPDATE pengumuman SET judul=?,isi=?,audiens=?,aktif_mulai=?,aktif_selesai=?,is_aktif=? WHERE id_pengumuman=?");
    $aktif = isset($_POST['is_aktif']) ? 1 : 0;
    $stmt->bind_param('ssssssi', $_POST['judul'], $_POST['isi'], $_POST['audiens'], $_POST['aktif_mulai'], $_POST['aktif_selesai'], $aktif, $_POST['id_pengumuman']);
    $stmt->execute();

    // LOG
    $db->catat_log($_SESSION['id_pengguna'], 'EDIT PENGUMUMAN', "Update pengumuman ID: " . $_POST['id_pengumuman']);

    header('Location: pengumuman_data.php');
    exit;
}

if ($_GET['act'] == 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $row = $mysqli->query("SELECT judul FROM pengumuman WHERE id_pengumuman=$id")->fetch_assoc();
    $judul = $row['judul'] ?? 'Unknown';

    $mysqli->query("DELETE FROM pengumuman WHERE id_pengumuman=$id");

    // LOG
    $db->catat_log($_SESSION['id_pengguna'], 'HAPUS PENGUMUMAN', "Menghapus pengumuman: $judul");

    header('Location: pengumuman_data.php');
    exit;
}

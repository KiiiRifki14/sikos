<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$act = $_POST['act'] ?? $_GET['act'] ?? '';

if ($act == 'tambah') {
    $judul = htmlspecialchars($_POST['judul']);
    $desk  = htmlspecialchars($_POST['deskripsi']);
    $biaya = intval($_POST['biaya']);
    $tgl   = $_POST['tanggal'];

    $stmt = $mysqli->prepare("INSERT INTO pengeluaran (judul, deskripsi, biaya, tanggal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssis', $judul, $desk, $biaya, $tgl);
    
    if ($stmt->execute()) {
        // Log Aktivitas (Opsional tapi bagus)
        // $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH PENGELUARAN', "Rp $biaya untuk $judul");
        header('Location: pengeluaran_data.php?msg=sukses');
    } else {
        echo "<script>alert('Gagal menyimpan data!'); window.history.back();</script>";
    }
} 
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    $mysqli->query("DELETE FROM pengeluaran WHERE id_pengeluaran=$id");
    header('Location: pengeluaran_data.php?msg=hapus');
}
?>
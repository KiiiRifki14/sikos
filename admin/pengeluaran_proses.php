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
        // [TAMBAHAN LOG]
        $db = new Database(); // Pastikan inisialisasi objek DB jika belum ada di file ini
        $db->catat_log($_SESSION['id_pengguna'], 'TAMBAH PENGELUARAN', "Mencatat pengeluaran: $judul (Rp " . number_format($biaya) . ")");
        
        header('Location: pengeluaran_data.php?msg=sukses');
    }else {
        echo "<script>alert('Gagal menyimpan data!'); window.history.back();</script>";
    }
} 
elseif ($act == 'hapus') {
    $id = intval($_GET['id']);
    
    // Ambil info dulu sebelum dihapus buat log
    $cek = $mysqli->query("SELECT judul, biaya FROM pengeluaran WHERE id_pengeluaran=$id")->fetch_assoc();
    $judul_hapus = $cek['judul'] ?? 'Unknown';
    $biaya_hapus = number_format($cek['biaya'] ?? 0);

    $mysqli->query("DELETE FROM pengeluaran WHERE id_pengeluaran=$id");
    
    // [TAMBAHAN LOG]
    $db = new Database();
    $db->catat_log($_SESSION['id_pengguna'], 'HAPUS PENGELUARAN', "Menghapus data pengeluaran: $judul_hapus (Rp $biaya_hapus)");

    header('Location: pengeluaran_data.php?msg=hapus');
}
?>
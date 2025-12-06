<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

// Validasi Akses POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pesan_error("index.php", "Akses ilegal.");
}

// Cek CSRF
if (!csrf_check($_POST['csrf'])) {
    pesan_error("penghuni_dashboard.php", "Sesi kadaluarsa. Silakan ulangi.");
}

$id_pembayaran = intval($_POST['id_pembayaran']);
if ($id_pembayaran < 1) {
    pesan_error("penghuni_dashboard.php", "ID Pembayaran tidak valid.");
}

// Validasi File
if (empty($_FILES['bukti_tf']['name'])) {
    pesan_error("pembayaran.php?booking=$id_pembayaran", "Anda belum memilih file bukti transfer.");
}

// Proses Upload
$path = upload_process($_FILES['bukti_tf'], 'bukti_tf');

if ($path) {
    // --- PERBAIKAN UTAMA: PANGGIL METHOD DARI CLASS DATABASE ---
    $db = new Database();
    
    // Method ini harus sudah ada di inc/koneksi.php (Lihat langkah 3)
    $sukses = $db->update_bukti_pembayaran($id_pembayaran, $path);
    
    if ($sukses) {
        header('Location: penghuni_dashboard.php?msg=pembayaran_berhasil');
        exit;
    } else {
        pesan_error("pembayaran.php?booking=$id_pembayaran", "Gagal update database. Hubungi Admin.");
    }
}
?>
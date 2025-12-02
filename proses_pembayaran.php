<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/upload.php';

// Validasi Akses
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pesan_error("index.php", "Akses ilegal.");
}

// Cek CSRF
if (!csrf_check($_POST['csrf'])) {
    pesan_error("penghuni_dashboard.php", "Sesi kadaluarsa (CSRF Error). Silakan ulangi.");
}

$id_pembayaran = intval($_POST['id_pembayaran']);
if ($id_pembayaran < 1) {
    pesan_error("penghuni_dashboard.php", "ID Pembayaran tidak valid.");
}

// Cek apakah file diupload
if (empty($_FILES['bukti_tf']['name'])) {
    pesan_error("pembayaran.php?booking=$id_pembayaran", "Anda belum memilih file bukti transfer.");
}

// Proses Upload (akan muncul alert otomatis dari fungsi upload jika gagal)
$path = upload_process($_FILES['bukti_tf'], 'bukti_tf');

if ($path) {
    // Update Database
    $stmt = $mysqli->prepare("UPDATE pembayaran SET bukti_path = ?, status = 'PENDING', waktu_verifikasi = NOW() WHERE id_pembayaran = ?");
    $stmt->bind_param('si', $path, $id_pembayaran);
    
    if ($stmt->execute()) {
        header('Location: penghuni_dashboard.php?msg=pembayaran_berhasil');
        exit;
    } else {
        pesan_error("pembayaran.php?booking=$id_pembayaran", "Gagal menyimpan data ke database.");
    }
}
?>
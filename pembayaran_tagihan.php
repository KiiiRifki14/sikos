<?php
session_start(); // Start session di paling atas
require 'inc/koneksi.php'; // koneksi sudah load config.php & fungsi pesan_error
require 'inc/csrf.php';
require 'inc/upload.php';

// 1. Cek Login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

// 2. Ambil Token CSRF
$token = $_POST['csrf'] ?? ''; 

// 3. Validasi Request & Token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pesan_error("tagihan_saya.php", "Akses Ditolak: Harus melalui form resmi.");
}

if (!csrf_check($token)) {
    pesan_error("tagihan_saya.php", "Token keamanan tidak valid (CSRF). Silakan refresh halaman dan coba lagi.");
}

// 4. Inisialisasi Objek Database
$db = new Database();

// 5. Ambil Data Form
$id_tagihan = intval($_POST['id_tagihan']);
$jumlah     = intval($_POST['jumlah']);
$metode     = $_POST['metode'] ?? 'TRANSFER';

// Validasi Data Input
if ($id_tagihan <= 0 || $jumlah <= 0) {
    pesan_error("tagihan_saya.php", "Data tagihan tidak valid.");
}

// 6. Proses Upload File
$bukti_path = null;
if (!empty($_FILES['bukti']['name'])) {
    // Fungsi upload_process sekarang sudah aman (ada alert popup jika gagal)
    $bukti_path = upload_process($_FILES['bukti'], 'bukti_tf');
}

if (!$bukti_path) {
    // Jika upload_process gagal, dia akan alert & back. 
    // Tapi untuk keamanan ganda, jika sampai sini bukti masih kosong:
    pesan_error("tagihan_saya.php", "Wajib upload bukti pembayaran!");
}

// 7. Simpan ke Database
$simpan = $db->tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti_path, $metode);

if ($simpan) {
    // Redirect sukses dengan pesan query string agar bisa ditangkap di UI
    header('Location: tagihan_saya.php?status=sukses');
    exit;
} else {
    pesan_error("tagihan_saya.php", "Terjadi kesalahan database saat menyimpan pembayaran.");
}
?>
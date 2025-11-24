<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';   // Session start otomatis di sini
require 'inc/upload.php';

// 1. Cek Login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

// 2. Ambil Token CSRF dengan aman
$token = $_POST['csrf'] ?? ''; 

// 3. Validasi Request & Token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Akses Ditolak: Harus melalui form upload.');
}

if (!csrf_check($token)) {
    die('Akses Ditolak: Token keamanan tidak valid (CSRF Error). Silakan refresh halaman dan coba lagi.');
}

// 4. Inisialisasi Objek Database
$db = new Database();

// 5. Ambil Data Form
$id_tagihan = intval($_POST['id_tagihan']);
$jumlah     = intval($_POST['jumlah']);
$metode     = $_POST['metode'] ?? 'TRANSFER';

// 6. Proses Upload File
$bukti_path = null;
if (!empty($_FILES['bukti']['name'])) {
    // Pastikan fungsi upload_process ada dan berfungsi
    $bukti_path = upload_process($_FILES['bukti'], 'bukti_tf');
}

if (!$bukti_path) {
    die("Gagal upload bukti pembayaran. Pastikan file dipilih dan format sesuai (JPG/PNG).");
}

// 7. Simpan ke Database via Method Class
$simpan = $db->tambah_pembayaran_tagihan($id_tagihan, $jumlah, $bukti_path, $metode);

if ($simpan) {
    // Redirect sukses
    header('Location: tagihan_saya.php?status=sukses');
    exit;
} else {
    die("Gagal menyimpan data pembayaran ke database.");
}
?>
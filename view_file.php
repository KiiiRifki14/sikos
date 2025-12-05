<?php
session_start();
require 'inc/koneksi.php';
require 'inc/guard.php';

// 1. Cek Login (Wajib Login untuk lihat file)
if (!isset($_SESSION['id_pengguna'])) {
    http_response_code(403);
    die("Akses Ditolak: Silakan login terlebih dahulu.");
}

// 2. Validasi Parameter
$type = $_GET['type'] ?? '';
$file = basename($_GET['file'] ?? ''); // basename() mencegah directory traversal (hack ../../)

if (empty($type) || empty($file)) {
    die("Parameter tidak lengkap.");
}

// 3. Konfigurasi Folder
$folders = [
    'ktp'   => __DIR__ . '/assets/uploads/ktp/',
    'bukti' => __DIR__ . '/assets/uploads/bukti_tf/'
];

// Cek apakah tipe file valid
if (!array_key_exists($type, $folders)) {
    die("Tipe file tidak dikenal.");
}

$filepath = $folders[$type] . $file;

// 4. Validasi Keamanan Tambahan
// Khusus KTP, hanya Admin/Owner yang boleh lihat (kecuali dikembangkan lagi nanti)
if ($type == 'ktp' && !is_admin() && !is_owner()) {
    http_response_code(403);
    die("Akses Ditolak: Dokumen rahasia.");
}

// 5. Tampilkan File
if (file_exists($filepath)) {
    // Deteksi tipe konten (image/jpeg, image/png, dll)
    $mime = mime_content_type($filepath);
    
    // Set Header agar browser tahu ini gambar
    header("Content-Type: $mime");
    header("Content-Length: " . filesize($filepath));
    
    // Baca dan kirim file ke browser
    readfile($filepath);
    exit;
} else {
    http_response_code(404);
    echo "File tidak ditemukan di server.";
}
?>
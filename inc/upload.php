<?php
// inc/upload.php

function upload_process($file, $folder) {
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];
    
    // 1. Cek apakah ada error upload dari server (misal file korup atau kosong)
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Return null agar bisa dihandle file pemanggil, atau tampilkan error langsung
        echo "<script>alert('Terjadi kesalahan saat mengupload file. Kode Error: " . $file['error'] . "'); window.history.back();</script>";
        exit; 
    }

    // 2. Cek Tipe MIME (Keamanan)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    // finfo_close($finfo); <-- BAGIAN INI DIHAPUS KARENA DEPRECATED

    if (!isset($allowed[$mime])) {
        // Tampilkan Pesan Error Ramah
        echo "<script>alert('Format file tidak didukung! Mohon hanya upload file JPG, PNG, atau WEBP.'); window.history.back();</script>";
        exit;
    }

    // 3. Cek Ukuran File (Max 2MB)
    if ($file['size'] > 2097152) { 
        // Tampilkan Pesan Error Ramah
        echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB.'); window.history.back();</script>";
        exit;
    }

    // 4. Proses Simpan
    $ext   = $allowed[$mime];
    $fname = bin2hex(random_bytes(12)) . '.' . $ext; // Nama file acak agar aman
    $dest  = __DIR__ . "/../assets/uploads/$folder/$fname";
    
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $fname;
    } else {
        echo "<script>alert('Gagal menyimpan file ke server. Silakan coba lagi.'); window.history.back();</script>";
        exit;
    }
}
?>
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
    $mime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
    } else if (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } else {
        // Fallback: Check extension if PHP extensions are missing (Less Secure but functional)
        $ext_check = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime_map = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'webp' => 'image/webp'
        ];
        $mime = $mime_map[$ext_check] ?? 'application/octet-stream';
    }

    if (!isset($allowed[$mime])) {
        echo "<script>alert('Format file tidak didukung! Deteksi: $mime'); window.history.back();</script>";
        exit;
    }

    // 3. Cek Ukuran File (Max 2MB)
    if ($file['size'] > 2097152) { 
        // Tampilkan Pesan Error Ramah
        echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB.'); window.history.back();</script>";
        exit;
    }

    // 4. Proses Kompresi & Simpan (Cegah Overload Storage)
    $ext   = $allowed[$mime];
    $fname = bin2hex(random_bytes(12)) . '.' . $ext; 
    $dest  = __DIR__ . "/../assets/uploads/$folder/$fname";
    
    // Fitur Kompresi Gambar (GD Library)
    $valid_images = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (in_array($mime, $valid_images)) {
        // Ambil info dimensi
        list($width, $height) = getimagesize($file['tmp_name']);
        
        // Setting Max Width (misal 1000px sudah cukup untuk web)
        $max_width = 1000; 

        // Load Gambar ke Memory
        $image = null;
        if ($mime == 'image/jpeg') $image = imagecreatefromjpeg($file['tmp_name']);
        elseif ($mime == 'image/png') $image = imagecreatefrompng($file['tmp_name']);
        elseif ($mime == 'image/webp') $image = imagecreatefromwebp($file['tmp_name']);

        if ($image) {
            // Cek apakah perlu resize?
            if ($width > $max_width) {
                // Hitung tinggi proporsional
                $ratio = $max_width / $width;
                $new_width = $max_width;
                $new_height = $height * $ratio;

                // Buat canvas baru
                $new_image = imagecreatetruecolor($new_width, $new_height);

                // Handle Transparency (PNG/WEBP)
                if ($mime == 'image/png' || $mime == 'image/webp') {
                    imagealphablending($new_image, false);
                    imagesavealpha($new_image, true);
                    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
                }

                // Copy & Resize
                imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                $image = $new_image; // Timpa image lama dengan yg baru
            }

            // Simpan ke File Destination (Compress Quality: 80%)
            $result = false;
            if ($mime == 'image/jpeg') $result = imagejpeg($image, $dest, 80); // 80% Quality
            elseif ($mime == 'image/png') $result = imagepng($image, $dest, 8); // Compression level 8 (0-9)
            elseif ($mime == 'image/webp') $result = imagewebp($image, $dest, 80); // 80% Quality

            // Bersihkan Memory
            imagedestroy($image);

            if ($result) return $fname;
        }
    }

    // Fallback: Jika bukan gambar atau gagal kompres, pakai cara biasa
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $fname;
    } else {
        echo "<script>alert('Gagal menyimpan file. Folder destination mungkin permission denied.'); window.history.back();</script>";
        exit;
    }
}
?>
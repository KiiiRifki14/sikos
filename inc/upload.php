<?php
function upload_process($file, $folder) {
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) {
        die('Format file tidak didukung. Hanya JPG, PNG, atau WEBP!');
    }
    if ($file['size'] > 2097152) die('Ukuran file max 2MB');
    $ext = $allowed[$mime];
    $fname = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = __DIR__ . "/../assets/uploads/$folder/$fname";
    move_uploaded_file($file['tmp_name'], $dest);
    return $fname;
}
?>
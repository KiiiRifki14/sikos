<?php
function upload_process($fileinfo, $typefolder) {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileinfo['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) {
        die('Format file tidak didukung');
    }
    if ($fileinfo['size'] > 2097152) { // 2MB
        die('File terlalu besar');
    }
    $ext = $allowed[$mime];
    $fname = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = __DIR__ . '/../assets/uploads/' . $typefolder . '/' . $fname;
    move_uploaded_file($fileinfo['tmp_name'], $dest);
    return $fname;
}
?>
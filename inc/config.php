<?php
// FILE KONFIGURASI DATABASE SIKOS
// Simpan file ini di folder utama (root project).

// 1. ATUR ZONA WAKTU (WIB - Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

// 2. KONFIGURASI DATABASE
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Ganti sesuai username database di XAMPP/Hosting
define('DB_PASS', '');          // Ganti sesuai password database
define('DB_NAME', 'sikos');     // Nama database
?>
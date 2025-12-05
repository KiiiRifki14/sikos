<?php
// Pastikan session aktif agar tidak error saat cek $_SESSION
if (session_status() === PHP_SESSION_NONE) session_start();

// Fungsi Cek Admin (Kita buat permisif: Owner juga dianggap Admin agar bisa akses fitur Admin)
function is_admin() {
    if (!isset($_SESSION['peran'])) return false;
    return ($_SESSION['peran'] == 'ADMIN' || $_SESSION['peran'] == 'OWNER');
}

// Fungsi Cek Owner (Spesifik hanya Owner)
function is_owner() {
    if (!isset($_SESSION['peran'])) return false;
    return ($_SESSION['peran'] == 'OWNER');
}

// Fungsi Cek Penghuni
function is_penghuni() {
    if (!isset($_SESSION['peran'])) return false;
    return ($_SESSION['peran'] == 'PENGHUNI');
}
?>
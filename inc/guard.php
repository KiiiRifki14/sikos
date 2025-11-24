<?php
// Fungsi untuk mengecek apakah user adalah Admin/Owner (Super User)
function is_admin() {
    if (!isset($_SESSION['peran'])) return false;
    // Admin atau Owner dianggap sama-sama punya akses penuh
    return ($_SESSION['peran'] == 'ADMIN' || $_SESSION['peran'] == 'OWNER');
}

// Fungsi untuk pengecekan Penghuni
function is_penghuni() {
    return (isset($_SESSION['peran']) && $_SESSION['peran'] == 'PENGHUNI');
}
?>
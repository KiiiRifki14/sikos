<?php
// Helper/utility functions for SIKOS

function is_admin() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'ADMIN';
}

function is_owner() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'OWNER';
}

// Bisa tambahkan utils lain di sini, misal format rupiah, tanggal, dll.
?>
<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/utils.php';
// Panggil Object Database
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('Invalid Request');
}

// Panggil Method login dari Class
$user = $db->login($_POST['email'], $_POST['password']);

if ($user) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['id_pengguna'] = $user['id_pengguna'];
    $_SESSION['peran'] = $user['peran'];
    
    // --- LOG LOGIN (BARU) ---
    $db->catat_log($user['id_pengguna'], 'LOGIN', "User berhasil login ke sistem.");
    // ------------------------

    // Jika Admin atau Owner, masuk ke Admin Panel
    if ($user['peran'] == 'ADMIN' || $user['peran'] == 'OWNER') {
        header('Location: admin/index.php');
    } 
    // Jika Penghuni, masuk ke Dashboard Penghuni
    else {
        header('Location: penghuni_dashboard.php');
    }
    exit;

} else {
    // Set pesan error ke session
    set_flash_message('error', 'Email atau password salah!');
    header('Location: login.php'); // Tidak perlu pakai ?error=auth lagi
    exit;
}
?>
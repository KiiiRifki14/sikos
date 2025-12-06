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
    
    // Catat Log
    $db->catat_log($user['id_pengguna'], 'LOGIN', "User berhasil login.");

    // --- LOGIKA REDIRECT PINTAR (FIX ALUR) ---
    if ($user['peran'] == 'ADMIN' || $user['peran'] == 'OWNER') {
        header('Location: admin/index.php');
    } else {
        // Cek apakah ada kamar yang mau dibooking sebelum login?
        if (isset($_SESSION['next_booking_kamar'])) {
            $id_kamar_tujuan = $_SESSION['next_booking_kamar'];
            // Hapus sesi agar tidak redirect terus menerus
            unset($_SESSION['next_booking_kamar']); 
            // Lempar balik ke halaman booking
            header("Location: booking.php?id_kamar=" . $id_kamar_tujuan);
        } else {
            // Jika login biasa, masuk dashboard
            header('Location: penghuni_dashboard.php');
        }
    }
    exit;

} else {
    set_flash_message('error', 'Email atau password salah!');
    header('Location: login.php');
    exit;
}
?>
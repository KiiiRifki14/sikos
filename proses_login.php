<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';

// Panggil Object Database
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('Invalid Request');
}

// Panggil Method login dari Class
$user = $db->login($_POST['email'], $_POST['password']);

if ($user) {
    session_regenerate_id(true);
    $_SESSION['id_pengguna'] = $user['id_pengguna'];
    $_SESSION['peran'] = $user['peran'];
    
    // --- LOGIKA PENYEDERHANAAN ---
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
    header('Location: login.php?error=auth');
}
?>
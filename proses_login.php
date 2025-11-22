<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';

// 1. HAPUS atau Komen baris session_start() di sini 
// karena sudah dipanggil otomatis di dalam inc/csrf.php
// session_start(); 

// 2. Gunakan operator null coalescing (??) untuk mencegah warning jika 'csrf' tidak ada
$csrf_post = $_POST['csrf'] ?? '';

// 3. Cek Method dan Token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($csrf_post)) {
    // Jika diakses langsung tanpa form, user akan melihat ini
    die('Invalid Request: Akses harus melalui form login (klik tombol Masuk).');
}

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $mysqli->prepare("SELECT id_pengguna, password_hash, peran, status FROM pengguna WHERE email=?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    if ($row['status'] == 1 && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['id_pengguna'] = $row['id_pengguna'];
        $_SESSION['peran'] = $row['peran'];
        
        if ($row['peran'] == 'ADMIN') {
            header('Location: admin/index.php');
        } elseif ($row['peran'] == 'OWNER') {
            header('Location: owner_dashboard.php');
        } else {
            header('Location: penghuni_dashboard.php');
        }
        exit;
    }
}

header('Location: login.php?error=auth');
?>
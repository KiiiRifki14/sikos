<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) die('CSRF error!');
$email = $_POST['email'];
$newpass = $_POST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($newpass) < 8) {
    header('Location: forgot.php?info=Data tidak valid!');
    exit;
}

$stmt = $mysqli->prepare("SELECT id_pengguna FROM pengguna WHERE email=? AND status=1");
$stmt->bind_param('s', $email);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) {
    $passhash = password_hash($newpass, PASSWORD_DEFAULT);
    $upd = $mysqli->prepare("UPDATE pengguna SET password_hash=? WHERE id_pengguna=?");
    $upd->bind_param('si', $passhash, $row['id_pengguna']);
    $upd->execute();
    // langsung redirect ke login, info lewat GET
    header('Location: login.php?info=Password berhasil direset. Silakan login kembali!');
    exit;
} else {
    header('Location: forgot.php?info=Email tidak ditemukan!');
    exit;
}
?>
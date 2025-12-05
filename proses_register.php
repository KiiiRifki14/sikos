<?php
require '/inc/utils.php';
require 'inc/koneksi.php';
require 'inc/csrf.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('Invalid Request');
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Khusus email pakai filter bawaan PHP
$password = $_POST['password']; // Password JANGAN dibersihkan (biarkan apa adanya untuk di-hash)
$nama = bersihkan_input($_POST['nama']);
$no_hp = bersihkan_input($_POST['no_hp']);

// Validasi
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    header('Location: register.php?error=invalid');
    exit;
}

// Cek Duplikat
$stmt = $mysqli->prepare("SELECT id_pengguna FROM pengguna WHERE email=?");
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header('Location: register.php?error=duplikat');
    exit;
}

// Insert Data
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO pengguna (nama, email, no_hp, password_hash, peran, status) VALUES (?, ?, ?, ?, 'PENGHUNI', 1)");
$stmt->bind_param('ssss', $nama, $email, $no_hp, $hash);
$stmt->execute();

// Sukses -> Ke Login
header('Location: login.php?info=Registrasi sukses, silakan login!');
?>
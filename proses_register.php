<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('Invalid Request');
}

$email = trim($_POST['email']);
$password = $_POST['password'];
$nama = htmlspecialchars($_POST['nama']);
$no_hp = htmlspecialchars($_POST['no_hp']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    header('Location: register.php?error=invalid');
    exit;
}

$stmt = $mysqli->prepare("SELECT id_pengguna FROM pengguna WHERE email=?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header('Location: register.php?error=duplikat');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO pengguna (nama, email, no_hp, password_hash, peran, status) VALUES (?, ?, ?, ?, 'PENGHUNI', 1)");
$stmt->bind_param('ssss', $nama, $email, $no_hp, $hash);
$stmt->execute();
$stmt->close();

$_SESSION['email'] = $email;
header('Location: login.php?register=success');
?>
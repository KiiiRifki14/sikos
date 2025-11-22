<?php
require 'inc/koneksi.php';
require 'inc/csrf.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'])) {
    die('Invalid Request');
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
        header('Location: ' . ($row['peran']=='ADMIN' ? 'admin/index.php' : 'penghuni_dashboard.php'));
        exit;
    }
}
header('Location: login.php?error=auth');
?>
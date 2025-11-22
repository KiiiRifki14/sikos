<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (isset($_SESSION['id_pengguna'])) {
  // Redirect ke dashboard, atau ke booking jika ada
  if (isset($_SESSION['next_booking_kamar'])) {
    $idk = $_SESSION['next_booking_kamar'];
    unset($_SESSION['next_booking_kamar']);
    header("Location: booking.php?id_kamar=$idk");
    exit;
  }
  if ($_SESSION['peran']=='OWNER') header('Location: owner_dashboard.php');
  elseif ($_SESSION['peran']=='ADMIN') header('Location: admin/index.php');
  else header('Location: penghuni_dashboard.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - SIKOS Paadaasih</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Login Penghuni/Admin</h2>
  <?php
  if (!empty($_GET['error'])) echo '<div class="alert-error">Login gagal!</div>';
  if (!empty($_GET['info'])) echo '<div class="alert-success">'.htmlspecialchars($_GET['info']).'</div>';
  ?>
  <form method="POST" action="proses_login.php">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Masuk</button>
  </form>
  <a href="register.php">Daftar Penghuni Baru</a> |
  <a href="forgot.php">Lupa Password?</a>
</div>
</body>
</html>
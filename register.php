<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

?>
<!DOCTYPE html>
<html>
<head>
  <title>Register - SIKOS Paadaasih</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
  <h2>Registrasi Penghuni</h2>
  <?php if (!empty($_GET['error'])) {
    if ($_GET['error']=='duplikat') echo "<p style='color:red;'>Email sudah terdaftar!</p>";
    else echo "<p style='color:red;'>Data tidak valid!</p>";
  }?>
  <form method="POST" action="proses_register.php">
    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    Nama: <input type="text" name="nama" required><br>
    Email: <input type="email" name="email" required><br>
    No HP: <input type="text" name="no_hp" required><br>
    Password: <input type="password" name="password" minlength="8" required><br>
    <button type="submit">Daftar</button>
  </form>
  <p>Sudah punya akun? <a href="login.php">Login</a></p>
</div>
</body>
</html>
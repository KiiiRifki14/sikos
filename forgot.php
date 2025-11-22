<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password - SIKOS Paadaasih</title>
    <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>
    <?php if (!empty($_GET['info'])) echo '<p style="color:green;">'.htmlspecialchars($_GET['info']).'</p>'; ?>
    <form method="POST" action="forgot_proses.php">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        Email: <input type="email" name="email" required><br>
        Password Baru: <input type="password" name="password" minlength="8" required><br>
        <button type="submit">Reset</button>
    </form>
    <p><a href="login.php">Kembali ke Login</a></p>
</div>
</body>
</html>
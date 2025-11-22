<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

// Jika sudah login, tidak perlu reset password
if (isset($_SESSION['id_pengguna'])) {
    header('Location: penghuni_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Reset Password - SIKOS</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-page">
  <div class="login-container">
    
    <div class="login-left">
      <svg class="login-illustration" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
        <rect x="50" y="100" width="300" height="250" rx="10" fill="rgba(255,255,255,0.2)" />
        <rect x="50" y="100" width="300" height="60" rx="10" fill="rgba(255,255,255,0.3)" />
        
        <rect x="120" y="180" width="160" height="120" rx="10" fill="rgba(255,255,255,0.9)" />
        <path d="M150,180 L150,140 A50,50 0 0,1 250,140 L250,180" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="15" />
        <circle cx="200" cy="240" r="15" fill="#f59e0b" />
        <path d="M200,240 L200,270" stroke="#f59e0b" stroke-width="8" stroke-linecap="round" />

        <text x="200" y="330" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">RECOVERY</text>
      </svg>
      <h1>Lupa Password?</h1>
      <p>Jangan khawatir. Reset password Anda<br>dengan mudah dan aman di sini.</p>
    </div>

    <div class="login-right">
      <div class="login-header">
        <h2>Reset Password</h2>
        <p>Masukkan email dan password baru Anda</p>
      </div>

      <?php 
      if (!empty($_GET['info'])) {
          $msg = htmlspecialchars($_GET['info']);
          // Cek apakah pesannya error atau sukses (berdasarkan text)
          $alertType = (strpos($msg, 'gagal') !== false || strpos($msg, 'tidak') !== false) ? 'alert-red' : 'alert-green';
          echo '<div class="alert-box '.$alertType.'">'.$msg.'</div>';
      } 
      ?>

      <form method="POST" action="forgot_proses.php">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label class="form-label">Email Terdaftar</label>
          <input type="email" name="email" class="form-input" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" minlength="8" required>
        </div>

        <button type="submit" class="btn-login-full">Reset Password</button>
      </form>

      <div style="text-align:center; margin-top:20px; font-size:14px; color:#64748b;">
        Ingat password Anda? <a href="login.php" style="color:var(--primary); font-weight:600;">Kembali Login</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>